<?php
class description_walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes[] = 'item-' . $item->ID;

        $dept_class = ($depth > 0) ? 'menu-level-' . $depth : '';
        if (!empty($dept_class)) {
            $classes[] = $dept_class;
        }

        $item_has_children = in_array('menu-item-has-children', $classes);
        $current_item = intval($item->object_id) === get_the_ID();

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
        $class_names = 'class="' . esc_attr($class_names) . '"';

        $output .= $indent . '<li ' . $class_names . '>';

        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';

        if ((empty($item->url)) || ($item->url == "#") || $current_item) {
            $tagname = 'span';
            $attributes .= ' class="menu-item"';
        } else {
            $tagname = 'a';
            $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
            $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        }

        $icon = get_field('menu_icon', $item);
        $icon_html = '';

        if ($icon) {
            $icon_html = '<img src="' . esc_url($icon) . '" alt="' . esc_attr($item->title) . '" class="menu-icon" />';
        }

        $item_title = apply_filters('the_title', $item->title, $item->ID);
        $item_title = '<span class="menu-item__name">' . $item_title . '</span>';
        $item_output = (!!$args->before && trim($args->before)) ? $args->before : '';
        $item_output .= '<' . $tagname . ' ' . trim($attributes) . '>';
        $item_output .= $icon_html . $args->link_before .
            $item_title . $args->link_after;
        $item_output .= '</' . $tagname . '>';
        if ($item_has_children) {
            $item_output .= "\n\t"
                . '<div class="menu-item-arrow-has-children">';
            $item_output .= "\n\t" . '<div class="sub-menu-container-box">';
            $item_output .= "\n\t" . '<div class="sub-menu-container">';
            $item_output .= "\n\t<div class=\"sub-menu-parent-title\">
                <div class=\"menu-goto-parent\">Назад</div>
            </div>";
            $item_output .= "\n\t<div class='category-name'>" . $icon_html . $item_title . "</div>";
        }

        $item_output .= (!!$args->after && trim($args->after)) ? $args->after : '';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    public function end_lvl(&$output, $depth = 1, $args = null)
    {
        if (isset($args->item_spacing) && 'discard' === $args->item_spacing) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat($t, $depth);
        $output .= "$indent</ul>\n\t</div>{$n}";
    }
}
?>