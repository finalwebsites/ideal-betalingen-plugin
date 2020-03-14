<?php

function fws_create_bedankt($atts) {
	global $wpdb;
	$atts = shortcode_atts(
		array(
			'showordersummary' => 'ja'
		),
		$atts
	);
	
	$iborderstring = get_query_var( 'iborderstring', '' );
	$content = '';
	// $iborderstring = $_GET['iborderstring'];
	if (preg_match('/^[a-f0-9]{32}$/', $iborderstring, $matches)) {
		$orderstring_checked = $matches[0];
		$secret = get_option('FWSIBP-secret');
		$sql = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE MD5(CONCAT(%s, ID)) = %s", $secret, $orderstring_checked );
		if ($order_row = $wpdb->get_row( $sql )) {
			$status = get_post_meta($order_row->ID, 'Transactie_status', true);
			$Emailadres = get_post_meta($order_row->ID, 'Emailadres', true);
			$Bedrag = get_post_meta($order_row->ID, 'Bedrag', true);
			$Product = get_post_meta($order_row->ID, 'Product', true);
			$btw = get_post_meta($order_row->ID, 'btw', true);
			
			if ($btw > 0) {
				$bedrag_label = sprintf(__('Bedrag incl. %s%% BTW', 'fws-ideal-betaalpaginas'), $btw);
			} else {
				$bedrag_label = __('Bedrag', 'fws-ideal-betaalpaginas');
			}
			
			if ($status == 'paid') {
				$content .= '
			<p>'.sprintf(__('Wij hebben de betaling voor je bestelling ontvangen en zullen het bestelde product of dienst zo snel mogelijk leveren.<br><br>Je ontvangt van ons een bevestiging via het opgegeven e-mailadres (%s).', 'fws-ideal-betaalpaginas'), $Emailadres).'</p>';
			} else {
				$content .= '
			<p>'.__('Je betaling is op dit moment nog niet verwerkt. Wanneer je de betaling hebt geannuleerd, is het helaas niet mogelijk om de betaling opnieuw te beginnen. Heb je wel betaald, dan ontvang je alsnog een bevestiging via e-mail.', 'fws-ideal-betaalpaginas').'</p>';
			}
			
			if ($atts['showordersummary'] == 'ja') $content .= '
			<h3>'.__('Je bestelling:', 'fws-ideal-betaalpaginas').'</h3>
			<p>
				'.__('Product of dienst:', 'fws-ideal-betaalpaginas').' <strong>'.$Product.'</strong><br>
				'.$bedrag_label.': <strong>&euro;'.number_format($Bedrag, 2, ',', '.').'</strong>
			</p>';
		}
	}
	return $content;
}
add_shortcode( 'bedankt_tekst', 'fws_create_bedankt' );
