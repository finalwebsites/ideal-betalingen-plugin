<?php

// Extra velden = "bedrijf,adres,telefoon"

function fws_create_payment_form($atts) {

	$atts = shortcode_atts(
		array(
			'form_titel' => __('Uw gegevens', 'fws-ideal-betaalpaginas'),
			'order_titel' => __('Uw bestelling', 'fws-ideal-betaalpaginas'),
			'extra_velden' => '',
			'product' => __('Voorbeeld product', 'fws-ideal-betaalpaginas'),
			'bedankpagina' => '',
			'prijs' => 10,
			'incl_btw' => false,
			'send_email' => 'ja',
			'btw' => 21
		),
		$atts
	);
	$secret = get_option('FWSIBP-secret');
	$apikey = get_option('FWSIBP-mollie-apiKey');
	if ($secret == '' || $apikey == '') {
		return __('De Mollie API key en/of de geheime code zijn niet ingevoerd.', 'fws-ideal-betaalpaginas');
	}
	$velden = array();
	if (trim($atts['extra_velden']) != '') {
		$velden = explode(',', str_replace(' ', '', $atts['extra_velden']));
	}
	if ($atts['btw'] > 0) {
		$totaal = ($atts['incl_btw']) ? $atts['prijs'] : $atts['prijs']*(100+$atts['btw'])/100;
	} else {
		$totaal = $atts['prijs'];
	}
	$html = '
			<div class="panel panel-default prijstabel">
				<div class="panel-heading"><h3>'.$atts['order_titel'].'</h3></div>
				<div class="panel-body">
					<p>
						<span class="pull-right">&euro; '.number_format($atts['prijs'], 2, ',', '.').'</span>
						<strong>'.$atts['product'].'</strong>
					</p>';
	if ($atts['incl_btw']) {
		$html .= '
					<p class="totaal">
						<span class="pull-right"><strong>&euro; '.number_format($totaal, 2, ',', '.').'</strong></span>
						<strong>'.sprintf(__('Totaal incl. %s%% BTW', 'fws-ideal-betaalpaginas'), $atts['btw']).'</strong>
					</p>';
	} elseif ($atts['btw'] == 0) {
		$html .= '
					<p class="totaal">
						<span class="pull-right"><strong>&euro; '.number_format($totaal, 2, ',', '.').'</strong></span>
						<strong>'.__('Totaal', 'fws-ideal-betaalpaginas').'</strong>
					</p>';

	} else {
		$html .= '
					<p>
						<span class="pull-right">&euro; '.number_format($atts['prijs']*$atts['btw']/100, 2, ',', '.').'</span>
						<strong>'.__('BTW', 'fws-ideal-betaalpaginas').' '.$atts['btw'].'%</strong>
					</p>
					<p class="totaal">
						<span class="pull-right"><strong>&euro; '.number_format($totaal, 2, ',', '.').'</strong></span>
						<strong>'.__('Totaal', 'fws-ideal-betaalpaginas').'</strong>
					</p>';
	}
	$html .= '
				</div>
			</div>
			<div id="msg-bar"></div>
			<h3>'.$atts['form_titel'].'</h3>
			<form id="betaalform" data-toggle="validator">';
	if (in_array('bedrijf', $velden)) $html .= '
				<div class="form-group">
					<label for="Bedrijfsnaam">'.__('Bedrijfsnaam', 'fws-ideal-betaalpaginas').'</label>
					<input type="text" class="form-control" id="Bedrijfsnaam" name="Bedrijfsnaam" placeholder="" required>
				</div>';
	if (in_array('adres', $velden)) $html .= '
				<div class="form-group">
					<label for="Adres">'.__('Straat en huisnummer', 'fws-ideal-betaalpaginas').'</label>
					<input type="text" class="form-control" id="Adres" name="Adres" placeholder="" required>
				</div>
				<div class="form-group">
					<label for="PC_Plaats">'.__('Postcode en plaats', 'fws-ideal-betaalpaginas').'</label>
					<input type="text" class="form-control" id="PC_Plaats" name="PC_Plaats" placeholder="" required>
				</div>';
	$html .= '
				<div class="form-group">
					<label for="contact">'.__('Contact', 'fws-ideal-betaalpaginas').'</label>
					<div class="row" id="contact">
						<div class="col-sm-6">
							<input type="text" class="form-control" id="Voornaam" name="Voornaam" placeholder="'.__('Voornaam', 'fws-ideal-betaalpaginas').'" required>
						</div>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="Achternaam" name="Achternaam" placeholder="'.__('Achternaam', 'fws-ideal-betaalpaginas').'" required>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="Emailadres">'.__('E-mailadres', 'fws-ideal-betaalpaginas').'</label>
					<input type="email" class="form-control" id="Emailadres" name="Emailadres" placeholder="" required>
				</div>';
	if (in_array('telefoon', $velden)) $html .= '
				<div class="form-group">
					<label for="Telefoon">'.__('Telefoon', 'fws-ideal-betaalpaginas').'</label>
					<input type="text" class="form-control" id="Telefoon" name="Telefoon" placeholder="" required>
				</div>';
	$html .= '
				<h3>'.__('Betaling via iDEAL', 'fws-ideal-betaalpaginas').'</h3>
				<div class="form-group">
					<label for="Bank">'.__('Kies uw bank', 'fws-ideal-betaalpaginas').'</label>
					<select id="bankenlijst" name="Issuer" class="form-control" required>
						<option value="">&#8212;</option>
						'.create_options_banken_select().'
					</select>
				</div>
				'.wp_nonce_field('fws_betaalform', '_fws_nonce', true, false).'
				<input type="hidden" name="action" value="fws_betaalform" />
				<input type="hidden" name="Bedrag" value="'.$totaal.'">
				<input type="hidden" name="btw" value="'.$atts['btw'].'">
				<input type="hidden" name="send_email" value="'.$atts['send_email'].'">
				<input type="hidden" name="Product" value="'.$atts['product'].'">
				<input type="hidden" name="bedankpagina" value="'.esc_url($atts['bedankpagina']).'">
				<button type="submit" id="submit-btn" class="btn btn-success">&euro; '.number_format($totaal, 2, ',', '.').' '.__('via iDEAL betalen', 'fws-ideal-betaalpaginas').'</button>
			</form>';
	return shortcode_unautop($html);
}
add_shortcode('fws_payment_form', 'fws_create_payment_form');
