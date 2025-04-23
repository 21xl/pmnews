<div class="statistics__breadcrumbs breadcrumbs">
    <div class="breadcrumbs__wrapper wrapper">
        <nav class="breadcrumbs">
            <a href="<?php echo home_url(); ?>" class="breadcrumbs__main">
                <span>
                    <?php pll_e("Home") ?>
                </span>
            </a>

            <?php if (is_singular() || get_query_var('custom_page') === 'match'): ?>
                <?php
                global $post, $wpdb;
                $post_type = get_post_type($post->ID);

                // Определяем базовый URL для статистики или матчей
                $base_url = (in_array($post_type, ['tennis', 'tennis_team']))
                    ? home_url('/statistics/')
                    : home_url('/statistics/' . $post_type . '/');

                // Для динамических матчей
                if (get_query_var('custom_page') === 'match') {
                    $match_id = get_query_var('match_id');
                    $base_url = home_url('/statistics/');

                    // Запрос в таблицу матчей для получения данных о матче
                    $match = $wpdb->get_row(
                        $wpdb->prepare("SELECT * FROM wp_sport_matches_shedule WHERE id = %s", $match_id)
                    );

                    // Если матч найден
                    if ($match) {
                        // Получаем данные о соревновании
                        $competition = $wpdb->get_row(
                            $wpdb->prepare("SELECT * FROM wp_sport_competitions WHERE id = %s", $match->competition_id)
                        );

                        // Проверяем наличие country_id и category_id
                        if (!empty($competition->country_id) && $competition->country_id !== 'null') {
                            $country = $wpdb->get_row(
                                $wpdb->prepare("SELECT * FROM wp_sport_country_data WHERE id = %s", $competition->country_id)
                            );
                            $location_name = $country ? $country->name_ru : $competition->name_ru;
                            $location_slug = $country ? $country->slug : $competition->slug;
                        } else {
                            $category = $wpdb->get_row(
                                $wpdb->prepare("SELECT * FROM wp_sport_category_data WHERE id = %s", $competition->category_id)
                            );
                            $location_name = $category ? $category->name_ru : $competition->name;
                            $location_slug = $category ? $category->slug : $competition->slug;
                        }

                        $base_url = home_url('/statistics/');
                    }
                }
                ?>

                <a href="<?php echo esc_url($base_url); ?>" class="breadcrumbs__section">
                    <span>
                        <?php echo (in_array($post_type, ['tennis', 'tennis_team'])) ? 'Футбол' : ucfirst($post_type); ?>
                    </span>
                </a>

                <?php
                $ancestors = get_post_ancestors($post->ID);
                $ancestors = array_reverse($ancestors);
                ?>

                <?php
                // Показываем родителей только если это не одиночный пост tennis
                if (!empty($ancestors) && get_query_var('custom_page') !== 'match' && !($post_type === 'tennis' && is_singular())):
                    ?>
                    <?php foreach ($ancestors as $ancestor_id): ?>
                        <a href="<?php echo get_permalink($ancestor_id); ?>" class="breadcrumbs__parent">
                            <span>
                                <?php echo get_the_title($ancestor_id); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (get_query_var('custom_page') === 'match'): ?>
                    <a href="<?php echo esc_url(home_url('/statistics/tennis/' . $location_slug . '/')); ?>"
                        class="breadcrumbs__section">
                        <span>
                            <?php echo $location_name; ?>
                        </span>
                    </a>

                    <a href="<?php echo esc_url(home_url('/statistics/tennis/' . $location_slug . '/' . $competition->slug . '/')); ?>"
                        class="breadcrumbs__section">
                        <span>
                            <?php echo $competition->name_ru ? $competition->name_ru : $competition->name; ?>
                        </span>
                    </a>

                    <span class="breadcrumbs__current">
                        <?php pll_e('match') ?>
                    </span>
                <?php else: ?>
                    <span class="breadcrumbs__current">
                        <?php echo get_the_title(); ?>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>
</div>