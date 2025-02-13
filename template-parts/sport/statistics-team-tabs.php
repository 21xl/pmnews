<?php
global $wpdb;
$post_id = get_the_ID();
$team_id = get_post_meta($post_id, '_team_id', true);
$tags = get_terms(['taxonomy' => 'post_tag', 'hide_empty' => true]);
$news_link = '';

if (!empty($tags)) {
    foreach ($tags as $tag) {
        $tag_id = get_field('competition_id', 'post_tag_' . $tag->term_id);

        if ($team_id && $tag_id && $team_id == $tag_id) {
            $tag_link = get_term_link($tag->term_id, 'post_tag');
            if (!is_wp_error($tag_link)) {
                $news_link = trailingslashit($tag_link);
                break;
            }
        }
    }
}

if (!$news_link) {
    $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => true]);
    if (!empty($categories)) {
        foreach ($categories as $category) {
            $cat_id = get_field('competition_id', 'category_' . $category->term_id);

            if ($team_id && $cat_id && $team_id == $cat_id) {
                $category_link = get_term_link($category->term_id, 'category');
                if (!is_wp_error($category_link)) {
                    $news_link = trailingslashit($category_link);
                    break;
                }
            }
        }
    }
}

$current_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', trim($current_uri, '/'));
$last_segment = end($uri_parts);

$tabs_slugs = array('sheduled', 'live', 'results', 'squad', 'news');
$active_tab_index = array_search($last_segment, $tabs_slugs);

if ($active_tab_index !== false) {
    array_pop($uri_parts);
} else {
    $active_tab_index = 0;
}

$base_url = trailingslashit('/' . implode('/', $uri_parts));

$tabs = array(
    $base_url => 'Расписание',
    $base_url . 'live' => 'Live',
    $base_url . 'results' => 'Результаты',
    $base_url . 'squad' => 'Состав',
);

// Добавляем "Новости" только если нашли ссылку
if ($news_link) {
    $tabs[$news_link] = 'Новости';
}

$index = 0;
?>

<div class="statistics-category__links links" <?php echo $active_tab_index !== 0 ? 'data-tab="' . esc_attr($last_segment) . '"' : 'data-tab="sheduled"'; ?>>
    <div class="links__list">
        <?php foreach ($tabs as $url => $label): ?>
            <?php if ($index === $active_tab_index): ?>
                <span class="links__item active">
                    <?php echo pll_e($label); ?>
                </span>
            <?php else: ?>
                <a href="<?php echo ($url === $news_link) ? esc_url($url) : esc_url(home_url($url)) . ($index === 0 ? '' : '/'); ?>"
                    class="links__item">
                    <span><?php echo pll_e($label); ?></span>
                </a>
            <?php endif; ?>
            <?php $index++; ?>
        <?php endforeach; ?>
    </div>
</div>