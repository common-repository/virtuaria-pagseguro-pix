<?php
/**
 * Template dependency missing.
 *
 * @package Virtuaria/Payments.
 */

defined( 'ABSPATH' ) || exit;

$is_installed = false;

if ( function_exists( 'get_plugins' ) ) {
	$all_plugins  = get_plugins();
	$is_installed = ! empty( $all_plugins[ $plugin_path ] );
}
?>
<div class="error">
	<p><span style="color:green">"Virtuaria PagSeguro Pix"</span> necessita da última versão do plugin <span style="color:green">"<?php echo esc_html( $plugin_name ); ?>"</span> para funcionar!</p>

	<?php
	if ( $is_installed && current_user_can( 'install_plugins' ) ) :
		?>
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_path . '&plugin_status=active' ), 'activate-plugin_' . $plugin_path ) ); ?>" class="button button-primary">
				Ativar <?php echo esc_html( $plugin_name ); ?>
			</a>
		</p>
		<?php
	else :
		$url = 'http://wordpress.org/plugins/' . $plugin_name;
		?>
		<p>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button button-primary">
				Instalar Plugin <?php echo esc_html( $plugin_name ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
