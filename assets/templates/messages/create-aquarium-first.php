<?php
/**
 * Create aquarium first message template.
 *
 * @package Aqualog
 * @subpackage Templates
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="aqualog-create-first-notice">
	<div class="aqualog-card">
		<p><?php esc_html_e( 'To start tracking water chemistry, you need to create your first aquarium.', 'aqualog' ); ?></p>
		<div class="aqualog-actions-grid">
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-action-card">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Create Your First Aquarium', 'aqualog' ); ?>
			</a>
		</div>
	</div>
</div>
