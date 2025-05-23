<?php
global $wpdb;
$post_id = get_the_ID();
$competition_id = get_post_meta($post_id, '_competition_id', true);
$competition_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM wp_sport_competitions WHERE id = %s",
    $competition_id
));
$content = get_the_content();

$team_name = null;
$team_logo = null;
$team_slug = null;

$seo_title = get_post_meta($post_id, '_seo_title', true);
$title = '';

if ($competition_data) {
    if (!empty($seo_title)) {
        $title = $seo_title;
    } else {
        $title = $competition_data->name;
    }
}

if ($competition_data && !is_null($competition_data->title_holder)) {
    $title_holder = json_decode($competition_data->title_holder, true);

    if (is_array($title_holder) && !empty($title_holder) && isset($title_holder[0])) {
        $team_id = $title_holder[0]; // Берем team_id из массива

        $team_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM wp_soccer_teams WHERE id = %s",
            $team_id
        ));

        if ($team_data) {
            if (!empty($team_data->logo)) {
                $team_logo = $team_data->logo; // Логотип команды
            } else {
                error_log("Логотип команды отсутствует.");
                $team_logo = '/wp-content/themes/pm-news/sport/src/img/player.svg'; // Укажите путь к логотипу по умолчанию, если он отсутствует
            }
            $current_language = function_exists('pll_current_language') ? pll_current_language() : 'en'; // По умолчанию 'en' если Polylang не активен



            $localized_name_field = 'name_' . $current_language; // Формируем имя поля, например, name_ru или name_en

            if (!empty($team_data->$localized_name_field)) {
                $team_name = $team_data->$localized_name_field;
            } else {
                $team_name = $team_data->name;
            }

            $team_slug = $team_data->slug;

        } else {
            error_log("Команда с ID {$team_id} не найдена.");

        }
    } else {
        error_log("Некорректные данные в поле title_holder: либо пустой массив, либо отсутствует team_id.");
    }
} else {
    error_log("Поле title_holder пустое, равно null или соревнование не найдено.");
}

if ($competition_data): ?>
    <section class="statistics-competition" data-competition="<?php echo $competition_id; ?>">
        <?php get_template_part('template-parts/sport/statistics-breadcrumbs'); ?>

        <div class="statistics-competition__head">
            <div class="statistics-competition__img">
                <img src="<?php echo esc_url($competition_data->logo); ?>"
                    alt="<?php echo esc_attr($competition_data->name); ?>">
            </div>

            <div class="statistics-competition__content">
                <div class="statistics-competition__content-top">
                    <div class="statistics-competition__main">
                        <span class="statistics-competition__title">
                            <?php echo esc_html($title); ?>
                        </span>

                        <?php get_template_part('template-parts/sport/pin', null, array('competition_id' => $competition_id)); ?>
                    </div>

                    <?php if (!empty($content)): ?>
                        <div class="statistics-competition__desc">
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($team_logo && $team_name): ?>
                    <div class="statistics-competition__winner">
                        <span class="statistics-competition__winner-text"><?php pll_e('Winner of the tournament'); ?>:</span>

                        <div class="statistics-competition__winner-img">
                            <img src="<?= esc_url($team_logo); ?>" alt="<?php echo $team_name ?>">
                        </div>

                        <a href="/statistics/teams/<?= $team_slug ?>/"><span
                                class="statistics-competition__winner-title"><?php echo $team_name ?></span></a>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="statistics-competition__matches">
            <div class="matches">

                <h1 class="statistics-competition__subtitle">
                    <?php pll_e('Tournament table'); ?> -
                    <?php echo esc_html($title); ?>
                </h1>

                <div class="matches__head">
                    <?php get_template_part('template-parts/sport/statistics-category-tabs', null, array('active_tab_index' => 3)); ?>
                </div>

                <div class="matches__main">
                    <?php get_template_part('template-parts/sport/statistics-table') ?>
                </div>
            </div>
        </div>
    </section>
    <?php
else:
    echo '<p>Country data not found.</p>';
endif;
?>