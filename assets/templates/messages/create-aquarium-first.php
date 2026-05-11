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
<div class="aquarium-log-create-first-notice">
	<div class="aquarium-log-card">
		<p><?php esc_html_e( 'To start tracking water chemistry, you need to create your first aquarium.', 'iworks-aquarium-log' ); ?></p>
		<div class="aquarium-log-actions-grid">
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aquarium-log-action-card">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Create Your First Aquarium', 'iworks-aquarium-log' ); ?>
			</a>
		</div>
	</div>
</div>
