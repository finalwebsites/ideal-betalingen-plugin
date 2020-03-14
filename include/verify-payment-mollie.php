<?php
require_once('../../../../wp-load.php');
include_once FWS_PLUGIN_PATH.'vendor/Mollie/API/Autoloader.php';

try {
	/*
	 * Check if this is a test request by Mollie
	 */
	if (!empty($_GET['testByMollie'])) 	{
		die('OK');
	}
	$msg = '';
	$apikey = get_option('FWSIBP-mollie-apiKey');
	$mollie = new Mollie_API_Client;
    $mollie->setApiKey($apikey);
	$payment  = $mollie->payments->get($_REQUEST['id']);
	//$msg .= serialize($payment);
	$oid = $payment->metadata->order_id;
	if ($order = get_post($oid)) {
		$Bedrag = get_post_meta($order->ID, 'Bedrag', true);
		$Product = get_post_meta($order->ID, 'Product', true);
		$Emailadres = get_post_meta($order->ID, 'Emailadres', true);
		$status = get_post_meta($order->ID, 'Transactie_status', true);
		$Voornaam = get_post_meta($order->ID, 'Voornaam', true);
		$send_email = get_post_meta($order_row->ID, 'send_email', true);
		if ($status == 'pending') {
			//$msg .= serialize($order);
			if ($payment->isPaid() == TRUE)	{
				update_post_meta($order->ID, 'Transactie_status', 'paid');
				add_post_meta($order->ID, 'Transactie_datum', current_time('mysql'), true);
        		if ($send_email == 'ja') {
  					$responderSubject = __('Bedankt voor je bestelling', 'fws-ideal-betaalpaginas');
  					$responderBody = sprintf( __('Beste %s,<br /><br />Wij hebben je betaling (%s) in goede orde ontvangen en zullen op korte termijn contact met je opnemen. <br />Voor vragen kan je ons natuurlijk bellen of mailen.', 'fws-ideal-betaalpaginas'), $Voornaam, $Product);
  					$responderBody = create_html_template($responderBody);

  					$responderHeader = array('From: '.get_option('blogname').' <'.get_option('admin_email').'>');
  					add_filter( 'wp_mail_content_type', 'fws_set_html_content_type' );
  					if ( !wp_mail($Emailadres, $responderSubject, $responderBody, $responderHeader) ) {
  						$msg .= 'Error sending mail.';
  					}
  					remove_filter( 'wp_mail_content_type', 'fws_set_html_content_type' );
        		}
			}
		}
	}
} catch (Mollie_API_Exception $e) {
	$msg .= "API call failed: " . htmlspecialchars($e->getMessage());
}

//file_put_contents(FWS_PLUGIN_PATH.'error.log', $msg);
