<?php


add_action('admin_menu', 'FWSIBP_plugin_menu');

function FWSIBP_plugin_menu() {
	add_submenu_page('edit.php?post_type=betalingen', 'iDEAL betaalpagina\'s instellingen', 'Instellingen', 'manage_options', 'FWSIBP-options', 'FWSIBP_options_page');
	add_action( 'admin_init', 'register_FWSIBP_setting' );
}




function register_FWSIBP_setting() {
	register_setting( 'FWSIBP_options', 'FWSIBP-is-divi' );
	register_setting( 'FWSIBP_options', 'FWSIBP-disable-bootstrap' );
	register_setting( 'FWSIBP_options', 'FWSIBP-bedankt-post-id' );
	register_setting( 'FWSIBP_options', 'FWSIBP-mollie-apiKey' );
	register_setting( 'FWSIBP_options', 'FWSIBP-secret' );
}

function FWSIBP_options_page() {

	echo '
	<div class="wrap">
		<h2>'.__( 'Instellingen voor iDEAL betaalpagina\'s', 'fws-ideal-betaalpaginas' ).'</h2>
		<p>'.sprintf ( __( 'Configureer hier de opties voor de plugin. Voor het wijzigen van de "standaard" teksten, kan de plugin <a href="%s" target="_blank">%s</a> worden gebruikt.', 'fws-ideal-betaalpaginas' ), esc_url( 'https://wordpress.org/plugins/loco-translate/' ), 'Loco Translate' ).'</p>';
	if (!get_option('FWSIBP-mollie-apiKey')) echo '
		<p>'.sprintf ( __( 'Voor het aanbod van een iDEAL betaling is een Mollie account noodzakelijk. U kunt gratis een account aanmaken via deze link <a href="%s">Mollie</a>.', 'fws-ideal-betaalpaginas' ), esc_url( 'https://www.mollie.com/nl/signup/211215' ) ).'</p>';

	echo '
		<form action="options.php" method="post">';
	settings_fields( 'FWSIBP_options' );

	echo '
			<h3>'.__( 'Configuratie', 'fws-ideal-betaalpaginas' ).'</h3>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">'.__( ' Mollie API Key: ', 'fws-ideal-betaalpaginas' ).'</th>
					<td>
						<input class="regular-text" type="text" placeholder="" value="'.esc_attr( get_option('FWSIBP-mollie-apiKey') ).'" name="FWSIBP-mollie-apiKey">
						<p class="description">'.__( 'Vul hier de Mollie API key in. Wilt uw eerst testen gebruik dan de API key die met "test_" begint. ', 'fws-ideal-betaalpaginas' ).'</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">'.__( ' ID bedankpagina: ', 'fws-ideal-betaalpaginas' ).'</th>
					<td>
						<input class="regular-text" type="text" placeholder="" value="'.esc_attr( get_option('FWSIBP-bedankt-post-id') ).'" name="FWSIBP-bedankt-post-id">
						<p class="description">'.__( 'De post ID van de standaard bedankpagina. Het is ook mogelijk om een ID voor succesvolle betalingen via de formulier shortcode op te geven.', 'fws-ideal-betaalpaginas' ).'</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">'.__( ' Geheime code: ', 'fws-ideal-betaalpaginas' ).'</th>
					<td>
						<input class="regular-text" type="text" placeholder="" value="'.esc_attr( get_option('FWSIBP-secret') ).'" name="FWSIBP-secret">
						<p class="description">'.__( 'De code of tekst die u hier opgeeft wordt gebruikt om een unieke order URL te genereren. Deze is noodzakelijk om via Mollie de betaalstatus te laten bijwerken.', 'fws-ideal-betaalpaginas' ).'</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">'.__( ' Divi CSS gebruiken: ', 'fws-ideal-betaalpaginas' ).'</th>
					<td>
						<label for="FWSIBP-is-divi">
						<input id="FWSIBP-is-divi" type="checkbox" value="1" name="FWSIBP-is-divi" '.checked( get_option('FWSIBP-is-divi'), 1, false ).'>
						'.__( 'Gebruikt u de Divi template? Vink dan deze optie aan.', 'fws-ideal-betaalpaginas' ).'
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">'.__( ' Bootstrap framework uitschakelen: ', 'fws-ideal-betaalpaginas' ).'</th>
					<td>
						<label for="FWSIBP-disable-bootstrap">
						<input id="FWSIBP-disable-bootstrap" type="checkbox" value="1" name="FWSIBP-disable-bootstrap" '.checked( get_option('FWSIBP-disable-bootstrap'), 1, false ).'>
						'.__( 'Gebruikt u al een WordPress template dat met Bootstrap 3 werkt? Dan kunt door het aanvinken van deze checkbox de Boostrap bestanden die door ons plugin worden gebruikt, uitschakelen.', 'fws-ideal-betaalpaginas' ).'
						</label>
					</td>
				</tr>
			</table>


			<p class="submit">
				<input class="button-primary" type="submit" value="'.__( 'Opslaan', 'fws-ideal-betaalpaginas' ).'">
			</p>
		</form>
		<h3>'.__( 'Hulp nodig?', 'fws-ideal-betaalpaginas' ).'</h3>
		<p>'.sprintf ( __( 'Raadpleeg dan de informatie op deze <a href="%s">pagina</a>.', 'fws-ideal-betaalpaginas' ), esc_url( '#' ) ).'</p>
	</div>';
}
