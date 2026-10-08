<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$block_content = '<div ' . get_block_wrapper_attributes() . '>';
$block_content .= apply_filters( 'iworks/aqualog/aquarium/the_content', '', $attributes['postId'] );
$block_content .= '</div>';

echo wp_kses_post( $block_content );

