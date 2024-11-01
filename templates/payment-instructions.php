<?php
/**
 * Template PIX payment instructions.
 *
 * @package virtuaria.
 */

defined( 'ABSPATH' ) || exit;

?>
<h3 class="validate-warning" style="color: green;">Pague com PIX. O código de pagamento tem validade de <?php echo esc_html( $validate ); ?>.</h3>

<strong style="display: block; margin-top: 10px;">
	Escanei este código para pagar
</strong>
<ol class="scan-instructions">
	<li>Acesse seu internet Banking ou app de pagamentos</li>
	<li>Escolha pagar via PIX</li>
	<li>Use o seguinte QR Code:</li>
</ol>
<img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=<?php echo esc_html( $qr_code ); ?>" alt="Qr code" />
<div class="code-area">
	<span class="code-text">
		Ou cole o seguinte código QR para fazer o pagamento ( escolha a opção Pix Copia e Cola no seu Internet Banking ).
	</span>
	<a id="pix-code" href="#">
		<span class="pix"><?php echo esc_html( $qr_code ); ?></span>
		<span class="copy">Copiar</span>
	</a>
	<button class="copy-pix">Copiar código</button>
	<div class="pix-copied" style="color:green;"></div>
</div>
<style>
	.code-area {
		margin-top: 20px;
	}
</style>
<?php
if ( $order ) {
	?>
	<style>
		.copy-pix,
		#pix-code .copy {
			display: none;
		}
		.validate-warning {
			font-size: 18px;
		}
	</style>
	<?php
} else {
	?>
	<style>
		.validate-warning {
			font-size: 16px;
		}
	</style>
	<?php
}
