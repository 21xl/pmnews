<?php
global $wpdb;
$post_id = get_the_ID();
$team_id = get_post_meta($post_id, '_team_id', true);
$team_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM wp_soccer_teams WHERE id = %s",
    $team_id
));
$team_name = null;
$team_logo = null;
$statuses = [8];


if ($team_data) {
    if (!empty($team_data->country_logo) && $team_data->national) {
        $team_logo = $team_data->country_logo; // Логотип команды
    } else if (!empty($team_data->logo) && !$team_data->national) {
        $team_logo = $team_data->logo;
    } else {
        $team_logo = '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg';
    }
    $current_language = function_exists('pll_current_language') ? pll_current_language() : 'en';

    $localized_name_field = 'name_' . $current_language; // Формируем имя поля, например, name_ru или name_en

    if (!empty($team_data->$localized_name_field)) {
        $team_name = $team_data->$localized_name_field;
    } else {
        $team_name = $team_data->name;
    }

} else {
    error_log("Команда с ID {$team_id} не найдена.");

}

if ($team_id) {
    // Запрос к базе данных
    $matches = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT id, season_id, competition_id, home_team_id, away_team_id, status_id, match_time, home_scores, away_scores, round, kickoff_timestamp
            FROM wp_sport_matches_shedule
            WHERE (home_team_id = %s OR away_team_id = %s) AND status_id IN (" . implode(',', array_fill(0, count($statuses), '%d')) . ")
            ORDER BY match_time DESC
            LIMIT 5
            ",
            array_merge([$team_id, $team_id], $statuses)
        )
    );

    // Инициализация массива результатов
    $match_results = [];

    if (!empty($matches)) {
        foreach ($matches as $match) {
            // Декодируем результаты матчей
            $home_scores = json_decode($match->home_scores, true);
            $away_scores = json_decode($match->away_scores, true);

            // Проверяем, что результаты существуют
            if (isset($home_scores[0], $away_scores[0])) {
                $home_score = (int) $home_scores[0];
                $away_score = (int) $away_scores[0];

                // Определение результата для текущей команды
                if ($match->home_team_id == $team_id) {
                    // Команда играет дома
                    if ($home_score > $away_score) {
                        $match_results[] = 'win';
                    } elseif ($home_score < $away_score) {
                        $match_results[] = 'loss';
                    } else {
                        $match_results[] = 'draw';
                    }
                } elseif ($match->away_team_id == $team_id) {
                    // Команда играет в гостях
                    if ($away_score > $home_score) {
                        $match_results[] = 'win';
                    } elseif ($away_score < $home_score) {
                        $match_results[] = 'loss';
                    } else {
                        $match_results[] = 'draw';
                    }
                }
            }
        }
    } else {

    }
} else {

}

$content = get_the_content();

$page_type = trim(get_query_var('page_type'));
if ($team_data): ?>
    <section class="statistics-competition" data-team="<?php echo $team_id; ?>">
        <?php get_template_part('template-parts/sport/statistics-breadcrumbs'); ?>

        <div class="statistics-competition__head">
            <div class="statistics-competition__img">
                <img src="<?php echo esc_url($team_logo); ?>" alt="<?php echo esc_attr($team_name); ?>">
            </div>

            <div class="statistics-competition__content">
                <div class="statistics-competition__content-top">
                    <div class="statistics-competition__main">
                        <span class="statistics-competition__title">
                            <?php echo esc_html($team_name); ?>
                        </span>
                    </div>

                    <?php if (!empty($content)): ?>
                        <div class="statistics-competition__desc">
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="statistics-competition__last form-icons">
                    <?php if (!empty($match_results)): ?>
                        <div class="statistics-competition__last-text">Последние игры:</div>
                        <?php foreach ($match_results as $result): ?>
                            <span class="<?= esc_attr($result) ?>"></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="statistics-competition__matches">
            <div class="matches" data-tab="sheduled">

                <h1 class="statistics-competition__subtitle">
                    <?php pll_e('Match Schedule'); ?>
                </h1>

                <div class="matches__head">
                    <?php get_template_part('template-parts/sport/statistics-team-tabs', null, array('active_tab_index' => 0)); ?>
                </div>

                <div class="matches__main">
                    <?php if ($page_type === 'squad'): ?>
                        <div class="team-squad">
                            <div class="match__composition">
                                <div class="match__composition-block">
                                    <div class="match__composition-main">
                                        <div class="match__composition-wrapper">
                                            <div class="table composition">
                                                <div class="row head">
                                                    <div class="cell team-name">
                                                        <span class="skeleton"></span>
                                                    </div>

                                                    <div class="cell nationality">
                                                        <span class="skeleton"></span>
                                                    </div>

                                                    <div class="cell age">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="cell player">
                                                        <div class="player__img skeleton"></div>

                                                        <div class="player__block">
                                                            <div class="player__head skeleton"></div>

                                                            <span class="player__position skeleton"></span>
                                                        </div>
                                                    </div>

                                                    <div class="cell">
                                                        <div class="flag skeleton"></div>
                                                    </div>

                                                    <div class="cell gray">
                                                        <span class="skeleton"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="matches__ligue">
                            <div class="matches__ligue-head">
                                <div class="matches__ligue-block">
                                    <div class="matches__ligue-fav skeleton"></div>
                                    <div class="matches__ligue-title skeleton"></div>
                                </div>

                                <div class="matches__ligue-control skeleton"></div>
                            </div>

                            <div class="matches__ligue-content">
                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="matches__ligue">
                            <div class="matches__ligue-head">
                                <div class="matches__ligue-block">
                                    <div class="matches__ligue-fav skeleton"></div>
                                    <div class="matches__ligue-title skeleton"></div>
                                </div>

                                <div class="matches__ligue-control skeleton"></div>
                            </div>

                            <div class="matches__ligue-content">
                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="matches__ligue">
                            <div class="matches__ligue-head">
                                <div class="matches__ligue-block">
                                    <div class="matches__ligue-fav skeleton"></div>
                                    <div class="matches__ligue-title skeleton"></div>
                                </div>

                                <div class="matches__ligue-control skeleton"></div>
                            </div>

                            <div class="matches__ligue-content">
                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="matches__ligue">
                            <div class="matches__ligue-head">
                                <div class="matches__ligue-block">
                                    <div class="matches__ligue-fav skeleton"></div>
                                    <div class="matches__ligue-title skeleton"></div>
                                </div>

                                <div class="matches__ligue-control skeleton"></div>
                            </div>

                            <div class="matches__ligue-content">
                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="matches__ligue">
                            <div class="matches__ligue-head">
                                <div class="matches__ligue-block">
                                    <div class="matches__ligue-fav skeleton"></div>
                                    <div class="matches__ligue-title skeleton"></div>
                                </div>

                                <div class="matches__ligue-control skeleton"></div>
                            </div>

                            <div class="matches__ligue-content">
                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="matches__item">
                                    <div class="matches__item-block">
                                        <div class="matches__item-fav skeleton"></div>

                                        <div class="matches__item-time skeleton"></div>

                                        <div class="matches__item-rivals">
                                            <div class="matches__item-team skeleton"></div>

                                            <div class="matches__item-team skeleton"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>

    <div class="matches__error">
        <span><?php pll_e('Matches not found') ?></span>
    </div>

<?php endif; ?>