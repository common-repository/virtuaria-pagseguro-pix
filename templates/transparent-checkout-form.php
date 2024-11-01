<?php
/**
 * Template checkout PIX.
 *
 * @package virtuaria.
 */

defined( 'ABSPATH' ) || exit;
?>

<fieldset id="pagseguro-pix-payment-form" >
	<button id="pagseguro-pix-button">
		GERAR CÓDIGO PIX
	</button>
	<img src="<?php echo esc_url( PAGSEGURO_PIX_URL ) . 'public/images/loading.gif'; ?>" alt="Loading" class="loading hidden">
</fieldset>
<style>
	#content #pagseguro-pix-payment-form .loading {
		max-width: 30px;
	}
	#content #pagseguro-pix-payment-form .loading.hidden {
		display: none;
	}
</style>
