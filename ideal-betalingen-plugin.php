<?php
/*
Plugin Name: iDEAL betalingen plugin
Plugin URI: https://www.finalwebsites.nl/handleiding-ideal-betalingen-plugin/
Description: Plugin voor het aanmaken van eenvoudige formulieren voor de betaling van enkele diensten of producten.
Author: Olaf Lederer
Version: 1.0.1
Author URI: https://www.finalwebsites.nl/
Text Domain: fws-ideal-betaalpaginas
Domain Path: /languages/
License: GPL v3

iDEAL betalingen plugin
Copyright (C) 2017, Olaf Lederer - https://www.finalwebsites.nl/contact/

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

include_once WP_PLUGIN_DIR.'/ideal-betalingen-plugin/vendor/Mollie/API/Autoloader.php';

define('FWS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

include_once FWS_PLUGIN_PATH.'include/shortcode-bedankt.php';
include_once FWS_PLUGIN_PATH.'include/shortcode-forms.php';
include_once FWS_PLUGIN_PATH.'include/options.php';

if ( is_admin() ) {
	register_deactivation_hook(__FILE__, 'fwsibp_deactivate');
}
add_action( 'plugins_loaded', 'FWSibp_load_textdomain' );

function FWSibp_load_textdomain() {
	load_plugin_textdomain( 'fws-ideal-betaalpaginas', false, FWS_PLUGIN_PATH . '/languages/' );
}

function load_my_scripts() {
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'fws_payment_form') ) {

		wp_enqueue_style( 'betalingen', plugins_url( 'css/style.css', __FILE__ ), array(), null );
		$is_divi = get_option('FWSIBP-is-divi');
		if ($is_divi == 1) {
			wp_enqueue_style( 'divi-formcode', plugins_url( 'css/divi.css', __FILE__ ), array('betalingen'), null );
		}
		wp_enqueue_script( 'bootstrap-validator', plugins_url( 'js/validator.min.js', __FILE__ ), null, null, true);
		wp_localize_script( 'bootstrap-validator', 'ajax_object_bp',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);
	}
}
add_action('wp_enqueue_scripts', 'load_my_scripts');

add_action( 'init', 'fws_create_orders_posttype' );
function fws_create_orders_posttype() {
    $args = array(
      'public' => false,
      'show_ui' => true,
      'menu_icon' => 'dashicons-cart',
      'rewrite' => false,
      'label'  => 'iDEAL betalingen',
      'supports' => array( 'title', 'custom-fields')
    );
    register_post_type( 'betalingen', $args );
}

add_filter('query_vars', 'fws_add_my_vars');
function fws_add_my_vars($public_query_vars) {
    $public_query_vars[] = 'iborderstring';
    return $public_query_vars;
}



function fws_bedankt_redirect() {
  global $wpdb;
	$secret = get_option('FWSIBP-secret');
	$apikey = get_option('FWSIBP-mollie-apiKey');
	$bedank_id = get_option('FWSIBP-bedankt-post-id');

	$iborderstring = get_query_var( 'iborderstring', '' );
	//$iborderstring = $_GET['iborderstring'];
	if (is_home()) {
  	if (preg_match('/^[a-f0-9]{32}$/', $iborderstring, $matches)) {
  		$order_id = $wpdb->get_var(
  			$wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE MD5(CONCAT('%s', ID)) = '%s'", $secret, $matches[0])
  		);

  		$pay_id = get_post_meta($order_id, 'Transactie_ID', true);
  		$mollie = new Mollie_API_Client;
  		$mollie->setApiKey($apikey);
  		$payment  = $mollie->payments->get($pay_id);
  		//print_r($payment);
			// de standaard bedankpagina
			if ($bedankt_url = get_permalink($bedank_id)) {
  			$bedankt_url .= '/?iborderstring='.$matches[0];
  		}
  		if ($payment->isPaid() == TRUE)	{
				// Externe URL overschrijft de standaard
  		  if ($bedankpagina = get_post_meta($order_id, 'bedankpagina', true)) {
  		    $bedankt_url = $bedankpagina;
  		  }
  		} 
			// nog steeds geen URL? dan maar de Homepage
  		if ($bedankt_url == '') {
  		  $bedankt_url = home_url('/');
  		}
  		wp_redirect( $bedankt_url );
  		exit();
    }
	}
}
add_action( 'template_redirect', 'fws_bedankt_redirect' );

function process_fws_betaalform() {
	global $wpdb;
	$msg = '';
	$status = 'error';
	// De verplichte velden voor een betaling
	$velden = array('Bedrag', 'Issuer', 'Product', 'Emailadres', 'Voornaam', 'Achternaam', 'btw');
	$extra_velden = array('Bedrijfsnaam', 'Telefoon', 'Adres', 'PC_Plaats', 'bedankpagina', 'send_email');

	foreach ($extra_velden as $ev) {
		if ($_POST[$ev] != '') {
		  if ($ev == 'bedankpagina') {
			  $valid_velden[$ev] = filter_var(trim($_POST[$ev]), FILTER_SANITIZE_URL);
		  } else {
			  $valid_velden[$ev] = filter_var(trim($_POST[$ev]), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
		  }
		}
	}
	$valid = true;
	foreach ($velden as $veld) {
		if ($_POST[$veld] == '') {
			$msg = __('Tenminste een van de verplichte velden is leeg.', 'fws-ideal-betaalpaginas');
			$valid = false;
		} else {
			if ($veld == 'Issuer') {
				preg_match('/^ideal_[A-Z0-9]{8}$/', $_POST['Issuer'], $matches);
				$valid_velden['Issuer'] = $matches[0];
			} elseif ($veld == 'Emailadres') {
				$valid_velden['Emailadres'] = filter_var(trim($_POST['Emailadres']), FILTER_SANITIZE_EMAIL);
			} else {
				$valid_velden[$veld] = filter_var(trim($_POST[$veld]), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
			}
		}
	}

	if ($valid) {
		if (!wp_verify_nonce($_POST['_fws_nonce'], $_POST['action'])) {
			$msg = __('Verificatie fout, probeer het opnieuw.', 'fws-ideal-betaalpaginas');
		} else {
			if ($order_id = create_order_record($valid_velden)) {
				// okay, ik heb een order ID...
				if ($molliepay = get_mollie_payment($valid_velden['Bedrag'], $valid_velden['Product'], $order_id, $valid_velden['Issuer'])) {
					add_post_meta($order_id, 'Transactie_ID', $molliepay['id'], true);
					add_post_meta($order_id, 'Transactie_status', 'pending', true);
					$msg = $molliepay['url'];
					$status = 'success';
				} else {
					$msg = __('Momenteel kan er geen iDEAL betaling worden aangemaakt, probeer het (later) opnieuw', 'fws-ideal-betaalpaginas');
				}
			} else {
				$msg = __('Uw betaling kan niet worden opgeslagen, probeer het opnieuw', 'fws-ideal-betaalpaginas');
			}
		}
	}
	$resp = array('status' => $status, 'msg' => $msg);
	wp_send_json($resp);
}
add_action('wp_ajax_fws_betaalform', 'process_fws_betaalform');
add_action('wp_ajax_nopriv_fws_betaalform', 'process_fws_betaalform');

function create_order_record($velden) {
	$titel = $velden['Product'].' - ';
	if (!empty($velden['Bedrijfsnaam'])) {
		$titel .= $velden['Bedrijfsnaam'];
	} else {
		$titel .= $velden['Voornaam'].' '.$velden['Achternaam'];
	}
	if ($order_id = wp_insert_post( array(
		'post_title' => $titel,
		'post_status' => 'publish',
		'post_date' => current_time('mysql'),
		'post_type' => 'betalingen'
	), true )) {
		add_post_meta($order_id, 'Product', $velden['Product'], true);
		add_post_meta($order_id, 'Bedrag', $velden['Bedrag'], true);
		add_post_meta($order_id, 'btw', $velden['btw'], true);
		add_post_meta($order_id, 'Emailadres', $velden['Emailadres'], true);
		add_post_meta($order_id, 'Voornaam', $velden['Voornaam'], true);
		add_post_meta($order_id, 'Achternaam', $velden['Achternaam'], true);

		if (!empty($bedankpagina)) add_post_meta($order_id, 'bedankpagina', $velden['bedankpagina'], true);
		if (!empty($send_email)) add_post_meta($order_id, 'send_email', $velden['send_email'], true);

		if (!empty($Bedrijfsnaam)) add_post_meta($order_id, 'Bedrijfsnaam', $velden['Bedrijfsnaam'], true);
		if (!empty($Adres)) add_post_meta($order_id, 'Adres', $velden['Adres'], true);
		if (!empty($PC_Plaats)) add_post_meta($order_id, 'PC_Plaats', $velden['PC_Plaats'], true);
		if (!empty($Telefoon)) add_post_meta($order_id, 'Telefoon', $velden['Telefoon'], true);
		
		return $order_id;
	} else {
		return false;
	}
}

function get_mollie_payment($bedrag, $descr, $order_id, $issuer) {
	try {
		$secret = get_option('FWSIBP-secret');
		$apikey = get_option('FWSIBP-mollie-apiKey');
		$mollie = new Mollie_API_Client;
		$mollie->setApiKey($apikey);
		$opties = array(
			"amount"       => floatval($bedrag),
			"method"       => Mollie_API_Object_Method::IDEAL,
			"description"  => $descr,
			"redirectUrl"  => home_url('/?iborderstring='.md5($secret.$order_id)),
			"webhookUrl"  =>  plugin_dir_url( __FILE__ ).'include/verify-payment-mollie.php',
			"metadata"     => array(
				"order_id" => $order_id
			),
			"issuer"       => $issuer
		);
		//print_r($opties);
		$payment = $mollie->payments->create($opties);
		//print_r($payment);
		$molliepayment['id'] = $payment->id;
		$molliepayment['url'] = $payment->getPaymentUrl();
		return $molliepayment;
	} catch (Mollie_API_Exception $e) {
		$msg = __('Fout:', 'fws-ideal-betaalpaginas') . $e->getMessage();
		return false;
	}
	
}

function create_options_banken_select($current = '') {
	$apikey = get_option('FWSIBP-mollie-apiKey');
	$mollie = new Mollie_API_Client;
    $mollie->setApiKey($apikey);
    $issuers = $mollie->issuers->all();
	$html = '';
	foreach ($issuers as $issuer) {
		if ($issuer->method == Mollie_API_Object_Method::IDEAL) {
			$html .= '
				<option value="'.$issuer->id.'"';
			if ($issuer->id == $current) $html .= ' selected="selected"';
			$html .= '>'.$issuer->name.'</option>';
		}
	}
	return $html;
}


function create_html_template($msg, $template = 'emailtemplate.html') {
	$html = wp_remote_request( plugins_url( 'include/'.$template, __FILE__ ));

	$html = str_replace('##BODYCONTENT##', $msg, $html['body']);
	$html = str_replace('##NAME##', get_option('admin_email'), $html);
	$html = str_replace('##COMPANY##', get_option('blogname'), $html);
	return $html;
}
function fws_set_html_content_type() {
	return 'text/html';
}


function add_this_script_footer(){
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'fws_payment_form') ) {
?>
<script>
jQuery(function($) {
	$('#betaalform').validator().on('submit', function (e) {
		$('#submit-btn').prop('disabled',true);
		$('#msg-bar').removeClass('alert alert-warning').html('');
		if (e.isDefaultPrevented()) {
			return false;
		} else {
			e.preventDefault();
			$.ajax({
    			type: 'POST',
    			url: ajax_object_bp.ajax_url,
    			data: $('form#betaalform').serialize(),
    			dataType: 'json',
    			success: function(data) {
    				if (data.status == 'success') {
						if (data.msg != '') {
							window.location.replace(data.msg);
						}
    				} else {
    				    $('#msg-bar').addClass('alert alert-warning').html(data.msg);
    				    $('#submit-btn').prop('disabled', false);
    				    window.scrollTo(0, 120);
    				}
    			},
    			error: function() {
    				$('#msg-bar').addClass('alert alert-warning').html('<?php __('Er is een fout ontstaan.', 'fws-ideal-betaalpaginas'); ?>');
    				$('#submit-btn').prop('disabled', false);
    				window.scrollTo(0, 120);
    			}
    		});
		}
	});
});
</script>
<?php
	}
}
add_action('wp_footer', 'add_this_script_footer', 20);


function fwsibp_deactivate() {
	// nothing to do
}
