<?php
$aquarium_id = isset( $args['aquarium_id'] ) ? $args['aquarium_id'] : 0;
$post        = get_post( $aquarium_id );
?>
<div <?php echo post_class( 'wp-block-group', $post ); ?>>
	<div class="wp-block-group__inner-container is  -layout-constrained wp-block-group-is-layout-constrained">
<?php
if ( has_post_thumbnail( $post ) ) {
	?>
<div class="wp-block-media-text is-stacked-on-mobile" style="grid-template-columns:25% auto">
	<figure class="wp-block-media-text__media">
		<?php echo get_the_post_thumbnail( $post, 'medium' ); ?>
	</figure>
	<div class="wp-block-media-text__content">
	<?php
}
?>
<h2 class="wp-block-heading"><a href="<?php echo get_permalink( $post ); ?>"><?php echo get_the_title(); ?></a></h2>
<dl>
	<dt><?php echo __( 'Started:', 'PLUGIN_NAME' ); ?></dt>
	<dd><?php echo get_post_meta( $post->ID, '_iw_date_started', true ); ?></dd>
	<dt><?php echo __( 'Water Volume in Tank:', 'PLUGIN_NAME' ); ?></dt>
	<dd><?php echo get_post_meta( $post->ID, '_iw_size_water_volume', true ); ?> <span><?php echo esc_html_x( 'L', 'liters abbreviation', 'PLUGIN_NAME' ); ?></span></dd>
</dl>
<?php
if ( has_post_thumbnail( $post ) ) {
	?>
</div></div>
	<?php
}
?>
	</div>
</div>
<?php
wp_reset_postdata();