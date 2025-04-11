<?php
global $wpdb;

// Получение данных стран
$table_countries = $wpdb->prefix . 'sport_country_data';
$table_categories = $wpdb->prefix . 'sport_category_data';
$table_competitions = $wpdb->prefix . 'sport_competitions';

$countries = $wpdb->get_results("
    SELECT DISTINCT c.id, c.name_ru, c.name
    FROM $table_countries c
    JOIN $table_competitions comp ON c.id = comp.country_id
    WHERE comp.cur_season_id IS NOT NULL
    ORDER BY c.name ASC
");

// Получение данных категорий
$categories = $wpdb->get_results("
    SELECT DISTINCT cat.id, cat.name_ru, cat.name, cat.logo
    FROM $table_categories cat
    JOIN $table_competitions comp ON cat.id = comp.category_id
    WHERE comp.cur_season_id IS NOT NULL
    ORDER BY cat.name ASC
");

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
$current_language = function_exists('pll_current_language') ? pll_current_language() : 'en'; // По умолчанию 'en' если Polylang не активен
$localized_name_field = 'name_' . $current_language;
$location_object = null;
$post_id = get_the_ID();

if (isset($page_data)) {

    if ($page_data === 'competition') {
        // Получаем ID соревнования из метаданных поста
        $competition_id = get_post_meta($post_id, '_competition_id', true);

        // Проверяем наличие ID соревнования
        if ($competition_id) {
            // Получаем данные соревнования
            $competition_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM wp_sport_competitions WHERE id = %s",
                $competition_id
            ));

            // Если у соревнования есть поле country_id
            if (!empty($competition_data->country_id)) {
                // Получаем данные о стране
                $country_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM wp_sport_country_data WHERE id = %s",
                    $competition_data->country_id
                ));

                // Получаем все соревнования с таким же country_id
                $related_competitions = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM wp_sport_competitions WHERE country_id = %s",
                    $competition_data->country_id
                ));

                if (!empty($related_competitions)) {
                    foreach ($related_competitions as $related_competition) {
                        $competition_name = !empty($related_competition->$localized_name_field) ? $related_competition->$localized_name_field : $related_competition->name;

                        $related_competitions_formatted[] = (object) [
                            'id' => $related_competition->id,
                            'name' => $competition_name,
                            'slug' => $related_competition->slug,
                        ];
                    }
                }

                $location_object = (object) [
                    'id' => $country_data->id,
                    'name' => !empty($country_data->$localized_name_field) ? $country_data->$localized_name_field : $country_data->name,
                    'slug' => $country_data->slug,
                    'logo' => !empty($country_data->logo) ? $country_data->logo : null,
                ];
            }
            // Если у соревнования нет country_id, но есть category_id
            else {
                // Получаем данные о категории
                $category_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM wp_sport_category_data WHERE id = %s",
                    $competition_data->category_id
                ));

                // Получаем все соревнования с таким же category_id и пустым country_id
                $related_competitions = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM wp_sport_competitions WHERE category_id = %s AND (country_id IS NULL OR country_id = '') AND (cur_season_id IS NOT NULL OR cur_season_id != '')",
                    $competition_data->category_id
                ));

                if (!empty($related_competitions)) {
                    foreach ($related_competitions as $related_competition) {
                        $competition_name = !empty($related_competition->$localized_name_field) ? $related_competition->$localized_name_field : $related_competition->name;

                        $related_competitions_formatted[] = (object) [
                            'id' => $related_competition->id,
                            'name' => $competition_name,
                            'slug' => $related_competition->slug,
                        ];
                    }
                }

                $location_object = (object) [
                    'id' => $category_data->id,
                    'name' => !empty($category_data->$localized_name_field) ? $category_data->$localized_name_field : $category_data->name,
                    'slug' => $category_data->slug,
                    'logo' => !empty($category_data->logo) ? $category_data->logo : '/wp-content/themes/pm-news/sport/src/img/world.svg',
                ];
            }
        }
    } elseif ($page_data === 'country') {
        $country_id = get_post_meta($post_id, '_country_id', true);
        if ($country_id) {
            $country_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM wp_sport_country_data WHERE id = %s",
                $country_id
            ));

            // Получаем все соревнования с таким же country_id
            $related_competitions = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM wp_sport_competitions WHERE country_id = %s",
                $country_id
            ));

            if (!empty($related_competitions)) {
                foreach ($related_competitions as $related_competition) {
                    $competition_name = !empty($related_competition->$localized_name_field) ? $related_competition->$localized_name_field : $related_competition->name;

                    $related_competitions_formatted[] = (object) [
                        'id' => $related_competition->id,
                        'name' => $competition_name,
                        'slug' => $related_competition->slug,
                    ];
                }
            }

            $location_object = (object) [
                'id' => $country_data->id,
                'name' => !empty($country_data->$localized_name_field) ? $country_data->$localized_name_field : $country_data->name,
                'slug' => $country_data->slug,
                'logo' => !empty($country_data->logo) ? $country_data->logo : null,
            ];
        }
    } elseif ($page_data === 'category') {
        $category_id = get_post_meta($post_id, '_category_id', true);

        $category_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM wp_sport_category_data WHERE id = %s",
            $category_id
        ));

        // Получаем все соревнования с таким же category_id и пустым country_id
        $related_competitions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_sport_competitions WHERE category_id = %s AND (country_id IS NULL OR country_id = '') AND (cur_season_id IS NOT NULL OR cur_season_id != '')",
            $category_id
        ));

        if (!empty($related_competitions)) {
            foreach ($related_competitions as $related_competition) {
                $competition_name = !empty($related_competition->$localized_name_field) ? $related_competition->$localized_name_field : $related_competition->name;

                $related_competitions_formatted[] = (object) [
                    'id' => $related_competition->id,
                    'name' => $competition_name,
                    'slug' => $related_competition->slug,
                ];
            }
        }

        $location_object = (object) [
            'id' => $category_data->id,
            'name' => !empty($category_data->$localized_name_field) ? $category_data->$localized_name_field : $category_data->name,
            'slug' => $category_data->slug,
            'logo' => !empty($category_data->logo) ? $category_data->logo : '/wp-content/themes/pm-news/sport/src/img/world.svg',
        ];
    }
}
?>

<aside class="statistics-sidebar">
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
                <span class="statistics-sidebar__title">
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
                                To create a list of pinned leagues, click on <span>icon</span> near the league of
                                interest
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="statistics-sidebar__operate">
            <?php if ($countries): ?>
                <div class="statistics-sidebar__block statistics-sidebar__block--countries">
                    <span class="statistics-sidebar__title">
                        <?php pll_e('Countries'); ?>
                    </span>

                    <ul class="statistics-sidebar__list">
                        <?php foreach ($countries as $index => $country): ?>
                            <li class="statistics-sidebar__item statistics-sidebar__item--has-children countries__item <?= $index >= 20 ? 'hidden' : ''; ?>"
                                data-id="<?= esc_attr($country->id); ?>">

                                <div class="statistics-sidebar__item-wrapper">
                                    <span class="statistics-sidebar__item-name">
                                        <?= esc_html(get_localized_field($country, 'name')); ?>
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

            <?php if ($countries && $categories): ?>
                <div class="statistics-sidebar__container">
                    <div class="statistics-sidebar__block statistics-sidebar__block--other hidden">
                        <span class="statistics-sidebar__title">
                            <?php pll_e('Other competitions'); ?>
                        </span>

                        <ul class="statistics-sidebar__list">
                            <?php foreach ($categories as $index => $category): ?>
                                <li class="statistics-sidebar__item statistics-sidebar__item--has-children countries__item hidden"
                                    data-idcat="<?= esc_attr($category->id); ?>">

                                    <div class="statistics-sidebar__item-wrapper">
                                        <span class="statistics-sidebar__item-name">
                                            <?= esc_html(get_localized_field($category, 'name')); ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <button id="toggle-button" class="statistics-sidebar__toggle">
                        <span><?php pll_e('Show more'); ?></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</aside>