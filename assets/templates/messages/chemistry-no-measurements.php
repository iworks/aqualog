<?php
/**
 * Chemistry no measurements message template.
 *
 * @package Aqualog
 * @subpackage Templates
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="notice notice-warning inline">
	<p>
		<?php esc_html_e( 'No measurements found. Please add your first water chemistry measurements to track your aquarium parameters.', 'PLUGIN_NAME' ); ?>
	</p>
</div>