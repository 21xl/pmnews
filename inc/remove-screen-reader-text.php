<?php
function remove_screen_reader_text_from_pagination($template)
{
    $template = '
    <nav class="navigation %1$s" role="navigation">
        <div class="nav-links">%3$s</div>
    </nav>';
    return $template;
}
add_filter('navigation_markup_template', 'remove_screen_reader_text_from_pagination');