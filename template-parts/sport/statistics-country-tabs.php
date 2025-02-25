<?php
$current_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', trim($current_uri, '/'));
$last_segment = end($uri_parts);
$tabs_slugs = array('results', 'live', 'standings', 'news');

if (in_array($last_segment, $tabs_slugs)) {
    array_pop($uri_parts);
}

$base_url = '/' . implode('/', $uri_parts);
$active_tab_index = isset($args['active_tab_index']) ? (int) $args['active_tab_index'] : -1;
$tabs = array(
    $base_url . '/' => 'Предстоящие',
    $base_url . '/results' => 'Результаты',
);
?>

<div class="statistics-category__links links">
    <div class="links__list">
        <?php $index = 0; ?>
        <?php foreach ($tabs as $url => $label): ?>
            <a href="<?php echo esc_url(home_url($url)); ?>" class="links__item">
                <span><?php echo pll_e($label); ?></span>
            </a>
            <?php $index++; ?>
        <?php endforeach; ?>
    </div>
</div>