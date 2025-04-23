<?php
global $wpdb;
$post_id = get_the_ID();
$competition_id = get_post_meta($post_id, '_competition_id', true);
$tags = get_terms(['taxonomy' => 'post_tag', 'hide_empty' => true]);
$news_link = '';

if (!empty($tags)) {
    foreach ($tags as $tag) {
        $tag_id = get_field('competition_id', 'post_tag_' . $tag->term_id);

        if ($competition_id && $tag_id && $competition_id == $tag_id) {
            $tag_link = get_term_link($tag->term_id, 'post_tag');
            if (!is_wp_error($tag_link)) {
                $news_link = trailingslashit($tag_link);
                break;
            }
        }
    }
}

$categories = get_terms(['taxonomy' => 'category', 'hide_empty' => true]);
if (!$news_link && !empty($categories)) {
    foreach ($categories as $category) {
        $cat_id = get_field('competition_id', 'category_' . $category->term_id);

        if ($competition_id && $cat_id && $competition_id == $cat_id) {
            $category_link = get_term_link($category->term_id, 'category');
            if (!is_wp_error($category_link)) {
                $news_link = trailingslashit($category_link);
                break;
            }
        }
    }
}

$current_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', trim($current_uri, '/'));
$last_segment = end($uri_parts);
$tabs_slugs = array('results', 'live', 'standings', 'news');
if (in_array($last_segment, $tabs_slugs)) {
    array_pop($uri_parts);
}
$base_url = trailingslashit('/' . implode('/', $uri_parts));
$active_tab_index = isset($args['active_tab_index']) ? (int) $args['active_tab_index'] : -1;

$tabs = array(
    $base_url => 'Schedule',
    $base_url . 'live/' => 'Live',
    $base_url . 'results/' => 'Results',
    $base_url . 'standings/' => 'Tournament table',
);

if ($news_link) {
    $tabs[$news_link] = 'News';
}
?>

<div class="statistics-category__links links">
    <div class="links__list">
        <?php $index = 0; ?>
        <?php foreach ($tabs as $url => $label): ?>
            <?php if ($index === $active_tab_index): ?>
                <span class="links__item active">
                    <?php echo pll_e($label); ?>
                </span>
            <?php else: ?>
                <a href="<?php echo esc_url($url); ?>" class="links__item">
                    <span><?php echo pll_e($label); ?></span>
                </a>
            <?php endif; ?>
            <?php $index++; ?>
        <?php endforeach; ?>
    </div>
</div>