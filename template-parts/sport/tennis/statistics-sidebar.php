<?php
function get_localized_field($data, $field_base)
{
    $current_language = pll_current_language();

    $field_key = "{$field_base}_{$current_language}";

    if (is_object($data) && !empty($data->$field_key)) {
        return $data->$field_key;
    } elseif (is_array($data) && !empty($data[$field_key])) {
        return $data[$field_key];
    }

    if (is_object($data) && !empty($data->$field_base)) {
        return $data->$field_base;
    } elseif (is_array($data) && !empty($data[$field_base])) {
        return $data[$field_base];
    }

    return '';
}

$page_data = $args['page_data'];
$related_competitions_formatted = [];
$current_language = function_exists('pll_current_language') ? pll_current_language() : 'en';
$localized_name_field = 'name_' . $current_language;
$location_object = null;
$post_id = get_the_ID();
$categories = get_tennis_categories();
$tournaments = get_tennis_future_tournaments();
?>

<aside class="statistics-sidebar statistics-sidebar__tennis">
    <div class="statistics-sidebar__wrapper">
        <div class="statistics-sidebar__mobile-control">

            <span><?php pll_e('Select league'); ?></span>

            <div class="statistics-sidebar__close">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#filter0_b_5935_290449)">
                        <rect width="40" height="40" rx="20" fill="black" fill-opacity="0.3" />
                        <path d="M16 16L24 24" stroke="white" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M24 16L16 24" stroke="white" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </g>
                    <defs>
                        <filter id="filter0_b_5935_290449" x="-7" y="-7" width="54" height="54"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feGaussianBlur in="BackgroundImageFix" stdDeviation="3.5" />
                            <feComposite in2="SourceAlpha" operator="in" result="effect1_backgroundBlur_5935_290449" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_backgroundBlur_5935_290449"
                                result="shape" />
                        </filter>
                    </defs>
                </svg>

            </div>
        </div>

        <?php if ($location_object && !empty($related_competitions_formatted)): ?>
            <div class="statistics-sidebar__operate">
                <div class="statistics-sidebar__container">
                    <div class="statistics-sidebar__block">
                        <span class="statistics-sidebar__title">
                            <div class="statistics-sidebar__title-img">
                                <img src="<?php echo $location_object->logo; ?>"
                                    alt="<?php echo $location_object->name; ?>" />
                            </div>

                            <?php echo $location_object->name; ?>
                        </span>

                        <ul class="statistics-sidebar__list">
                            <?php foreach ($related_competitions_formatted as $index => $comp): ?>
                                <li class="statistics-sidebar__item countries__item <?= $index >= 10 ? 'hidden' : ''; ?>"
                                    data-id="<?= esc_attr($comp->id); ?>">
                                    <div class="statistics-sidebar__item-wrapper">
                                        <div class="statistics-sidebar__item-block">
                                            <a class="statistics-sidebar__item-name"
                                                href="/statistics/football/<?= $location_object->slug; ?>/<?= $comp->slug; ?>/">
                                                <?= esc_html($comp->name); ?>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if (count($related_competitions_formatted) >= 11): ?>
                        <button class="statistics-sidebar__toggle">
                            <span><?php pll_e('Show more'); ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="statistics-sidebar__container">
            <div class="statistics-sidebar__block statistics-sidebar__block--pinned">
                <span class="statistics-sidebar__title statistics-sidebar__title--pinned">
                    <?php pll_e('Pinned Leagues'); ?>
                </span>

                <ul id="pinned" class="statistics-sidebar__list">
                    <li class="sceleton_sb_pined statistics-sidebar__item statistics-sidebar__item--pinned">
                        <div class="statistics-sidebar__item-loader statistics-sidebar__item-loader--pinned">
                            <?php for ($i = 0; $i < 10; $i++): ?>
                                <div class="statistics-sidebar__item-skeleton skeleton"></div>
                            <?php endfor; ?>
                        </div>
                    </li>

                    <li class="empty_sb_pined hidden statistics-sidebar__item statistics-sidebar__item--pinned">
                        <div class="statistics-sidebar__item-wrapper statistics-sidebar__item-wrapper--error">
                            <div class="statistics-sidebar__item-error">
                                <?php
                                printf(
                                    pll__('To create a list of pinned leagues, click on the %s next to the league you are interested in'),
                                    '<span>' . pll__('icon') . '</span>'
                                );
                                ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="statistics-sidebar__container">
            <div class="statistics-sidebar__block statistics-sidebar__block--team">
                <span class="statistics-sidebar__title statistics-sidebar__title--team">
                    <?php pll_e('My players'); ?>
                </span>

                <ul id="tennis-fav-team" class="statistics-sidebar__list">
                    <li class="statistics-sidebar__item statistics-sidebar__item--pinned">
                        <div class="statistics-sidebar__item-wrapper statistics-sidebar__item-wrapper--error">
                            <div class="statistics-sidebar__item-error">
                                <?php pll_e('To create a list of favorite players, click on') ?>
                                <div class="fav-icon-msg"></div>
                                <?php pll_e('near the match of interest') ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="statistics-sidebar__container">
            <div class="statistics-sidebar__block statistics-sidebar__block--rating">
                <span class="statistics-sidebar__title statistics-sidebar__title--rating">
                    <?php pll_e('Rating'); ?>
                </span>

                <ul class="statistics-sidebar__list">
                    <?php
                    // Запрос постов типа 'tennis_rating'
                    $rating_query = new WP_Query([
                        'post_type' => 'tennis_rating',
                        'posts_per_page' => -1, // Все посты
                        'post_status' => 'publish',
                        'orderby' => 'post_id',
                        'order' => 'ASC',
                    ]);

                    if ($rating_query->have_posts()) {
                        while ($rating_query->have_posts()) {
                            $rating_query->the_post();
                            $post_id = get_the_ID();
                            $type = get_post_meta($post_id, '_type', true);
                            $seo_title = get_post_meta($post_id, '_seo_title', true);
                            $title = $seo_title ?: get_the_title();
                            $permalink = get_permalink();
                            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'thumbnail') ?: '/wp-content/themes/pm-news/sport/src/img/tennis-player-placeholder.svg'; // Запасное изображение
                            ?>
                            <li class="statistics-sidebar__item statistics-sidebar__item--calendar"
                                data-leagid="<?php echo esc_attr($type); ?>">
                                <div class="statistics-sidebar__item-wrapper">
                                    <div class="statistics-sidebar__item-block">
                                        <div class="statistics-sidebar__item-img">
                                            <img src="<?php echo esc_url($thumbnail_url); ?>"
                                                alt="<?php echo esc_attr($title); ?>" />
                                        </div>

                                        <a class="statistics-sidebar__item-name" href="<?php echo esc_url($permalink); ?>">
                                            <?php echo esc_html($title); ?>
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <?php
                        }
                        wp_reset_postdata();
                    } else {
                        ?>
                        <li class="sceleton_sb_pined statistics-sidebar__item statistics-sidebar__item--pinned">
                            <div class="statistics-sidebar__item-loader statistics-sidebar__item-loader--pinned">
                                <?php for ($i = 0; $i < 10; $i++): ?>
                                    <div class="statistics-sidebar__item-skeleton skeleton"></div>
                                <?php endfor; ?>
                            </div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="statistics-sidebar__container">
            <div class="statistics-sidebar__block statistics-sidebar__block--tournaments">
                <span class="statistics-sidebar__title">
                    <?php pll_e('Current tournaments'); ?>
                </span>

                <ul id="tennis-tournaments" class="statistics-sidebar__list">
                    <?php foreach ($tournaments as $index => $comp): ?>
                        <li
                            class="statistics-sidebar__item statistics-sidebar__item--tournaments <?= $index >= 10 ? 'hidden toggle-hidden' : ''; ?>">
                            <div class="statistics-sidebar__item-wrapper">
                                <div class="statistics-sidebar__item-block">
                                    <div class="statistics-sidebar__item-img">
                                        <img src="<?php echo esc_url($comp['tournament']['logo']); ?>"
                                            alt="<?php echo esc_attr(get_translated_name($comp['tournament']['names'])); ?>" />
                                    </div>

                                    <a class="statistics-sidebar__item-name"
                                        href="/statistics/tennis/<?= $comp['tournament']['ct_slug'] ?>/<?= $comp['tournament']['slug'] ?>/">
                                        <?php echo esc_html(get_translated_name($comp['tournament']['names'])); ?>
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (count($tournaments) > 10): ?>
                    <button class="statistics-sidebar__toggle" id="tournaments-toggle">
                        <span><?php pll_e('Show more') ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="statistics-sidebar__container">
            <?php if ($categories): ?>
                <div class="statistics-sidebar__block statistics-sidebar__block--tournaments">
                    <span class="statistics-sidebar__title">
                        Categories
                    </span>

                    <ul class="statistics-sidebar__list">
                        <?php foreach ($categories as $index => $category): ?>
                            <li class="statistics-sidebar__item statistics-sidebar__item--has-children countries__item <?= $index >= 20 ? 'hidden' : ''; ?>"
                                data-id="<?= esc_attr($category['id']); ?>" data-type="<?= esc_attr($category['type']); ?>">

                                <div class="statistics-sidebar__item-wrapper">
                                    <span class="statistics-sidebar__item-name">
                                        <?= esc_html(get_translated_name($category['names']) . ' ' . get_translated_name($category['variations'])) ?>
                                    </span>
                                </div>

                                <div class="statistics-sidebar__item-loader">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <div class="statistics-sidebar__item-skeleton skeleton"></div>
                                    <?php endfor; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (count($categories) > 20): ?>
                <button id="toggle-button" class="statistics-sidebar__toggle">
                    <span><?php pll_e('Show more'); ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</aside>