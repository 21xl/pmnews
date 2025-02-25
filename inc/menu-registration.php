<?php
/* Add nav locations */
register_nav_menus([
    'header_menu' => __('Main Menu', 'pm_news'),
    'footer_menu_1' => __('Footer Menu 1', 'pm_news'),
    'footer_menu_2' => __('Footer Menu 2', 'pm_news'),
]);


/*  Remove <div> */
function remove_menu_wrapper($args)
{
    $args['container'] = false;
    return $args;
}
add_filter('wp_nav_menu_args', 'remove_menu_wrapper');
