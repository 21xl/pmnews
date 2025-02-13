<?php
//REMOVE EMOJI STYLES
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

//REMOVE GUTENBERG BLOCK LIBRARY CSS FROM LOADING ON FRONTEND
function remove_wp_block_library_css()
{
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-block-style'); // REMOVE WOOCOMMERCE BLOCK CSS
    wp_dequeue_style('global-styles'); // REMOVE THEME.JSON
}
add_action('wp_enqueue_scripts', 'remove_wp_block_library_css', 100);