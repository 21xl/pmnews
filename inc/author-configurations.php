<?php
// Убираем блоки аватара, биографии ("Обо мне") и "Сайт" в профиле пользователя, оставляем ACF поля
function remove_user_profile_fields($buffer)
{
    // Убираем блок аватара
    $buffer = preg_replace('/<tr class="user-profile-picture">.*?<\/tr>/is', '', $buffer);

    // Убираем блок "Сайт"
    $buffer = preg_replace('/<tr class="user-url-wrap">.*?<\/tr>/is', '', $buffer);

    return $buffer;
}

function start_buffer_profile()
{
    ob_start("remove_user_profile_fields");
}

function end_buffer_profile()
{
    ob_end_flush();
}

add_action('admin_head', 'start_buffer_profile');
add_action('admin_footer', 'end_buffer_profile');

// Убираем стандартные поля, оставляя ACF поля
function remove_default_contact_methods($contactmethods)
{
    unset($contactmethods['url']);  // Удаляем поле 'Сайт'
    return $contactmethods;
}
add_filter('user_contactmethods', 'remove_default_contact_methods');


