<?php
$aquarium_id = isset($args['aquarium_id']) ? $args['aquarium_id'] : 0;
$post = get_post( $aquarium_id );
?>
<div <?php echo post_class('wp-block-group', $post); ?>>
    <div class="wp-block-group__inner-container is-layout-constrained wp-block-group-is-layout-constrained">
<?php
if ( has_post_thumbnail($post) ) {
    ?>
<div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img decoding="async" loading="lazy" src="https://wordpress.test/wp-content/uploads/2026/10/logo.svg" alt="" class="wp-image-39883 size-full"></figure><div class="wp-block-media-text__content">
    <?php
}
?>
<h2 class="wp-block-heading"><?php echo get_the_title(); ?></h2>



<p class="wp-block-paragraph">cośtam</p>
<?php
if ( has_post_thumbnail($post) ) {
?>
</div></div>
    <?php
}
?>
</div>
</div>
<?php
wp_reset_postdata();