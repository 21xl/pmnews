<?php
global $wpdb;
$post_id = get_the_ID();
$competition_id = get_post_meta($post_id, '_tournament_id', true);

// Получаем посты tennis_calendar
$calendar_query = new WP_Query([
    'post_type' => 'tennis_calendar',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
]);

$tabs = [];
if ($calendar_query->have_posts()) {
    while ($calendar_query->have_posts()) {
        $calendar_query->the_post();
        $calendar_id = get_the_ID();
        $seo_title = get_post_meta($calendar_id, '_seo_title', true) ?: get_the_title();
        $permalink = get_permalink();
        $tabs[$permalink] = $seo_title;
    }
    wp_reset_postdata();
}

// Определяем активную вкладку по последнему сегменту URL
$current_uri = $_SERVER['REQUEST_URI'];
$current_uri_parts = explode('/', trim($current_uri, '/'));
$current_last_segment = end($current_uri_parts);

$active_tab_index = -1;
$index = 0;
foreach ($tabs as $url => $label) {
    $url_parts = explode('/', trim($url, '/'));
    $tab_last_segment = end($url_parts);

    if ($current_last_segment === $tab_last_segment) {
        $active_tab_index = $index;
        break;
    }
    $index++;
}

// Значения для data-tab
$tabs_data = array_values(array_map('sanitize_title', array_values($tabs)));
$active_tab_value = ($active_tab_index >= 0 && isset($tabs_data[$active_tab_index])) ? $tabs_data[$active_tab_index] : sanitize_title(array_values($tabs)[0] ?? 'calendar');

// Сохраняем ID активного календаря
$active_calendar_id = ($active_tab_index >= 0 && isset($calendar_query->posts[$active_tab_index])) ? $calendar_query->posts[$active_tab_index]->ID : '';
?>

<div class="statistics-category__links links" data-tab="<?php echo esc_attr($active_tab_value); ?>">
    <div class="links__list">
        <?php $index = 0; ?>
        <?php foreach ($tabs as $url => $label): ?>
            <?php
            $calendar_id = $calendar_query->posts[$index]->ID;
            $is_active = ($index === $active_tab_index) ? 'active' : '';
            ?>
            <a href="<?php echo esc_url($url); ?>" class="links__item <?php echo $is_active; ?>"
                data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <span><?php echo esc_html($label); ?></span>
            </a>
            <?php $index++; ?>
        <?php endforeach; ?>
    </div>
</div>