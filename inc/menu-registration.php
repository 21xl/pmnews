<?php
/* Add nav locations */
register_nav_menus([
    'header_menu' => __('Main Menu', 'sport_pulse'),
    'footer_menu_1' => __('Footer Menu 1', 'sport_pulse'),
    'footer_menu_2' => __('Footer Menu 2', 'sport_pulse'),
]);


/*  Remove <div> */
function remove_menu_wrapper($args)
{
    $args['container'] = false;
    return $args;
}
add_filter('wp_nav_menu_args', 'remove_menu_wrapper');
