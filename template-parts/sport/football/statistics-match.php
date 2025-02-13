<?php
global $wpdb;
$match_id = get_query_var('match_id');
$match_query = $wpdb->prepare("
    SELECT 
        m.*, 
        ht.id AS home_team_id, 
        ht.logo AS home_team_logo, 
        ht.slug AS home_team_slug,
        IF(ht.name_ru IS NULL OR ht.name_ru = '', ht.name, ht.name_ru) AS home_team_name,
        at.id AS away_team_id, 
        at.logo AS away_team_logo, 
        at.slug AS away_team_slug,
        IF(at.name_ru IS NULL OR at.name_ru = '', at.name, at.name_ru) AS away_team_name
    FROM 
        wp_sport_matches_shedule AS m
    LEFT JOIN 
        wp_soccer_teams AS ht ON ht.id = m.home_team_id
    LEFT JOIN 
        wp_soccer_teams AS at ON at.id = m.away_team_id
    WHERE 
        m.id = %s
", $match_id);

$match = $wpdb->get_row($match_query);
$lineup_query = $wpdb->prepare("
            SELECT coach_id, home_lineup, away_lineup 
            FROM wp_football_lineup 
            WHERE match_id = %s
        ", $match_id);
$lineup = $wpdb->get_row($lineup_query);

$odds_query = $wpdb->prepare("
            SELECT * 
            FROM wp_football_odds 
            WHERE match_id = %s
        ", $match_id);
$odds = $wpdb->get_row($odds_query);

$status = 1;
if ($match->status_id !== 1) {
    $status = (int) $match->status_id;
}

$statistics_query = $wpdb->prepare("
    SELECT * 
    FROM wp_football_team_stats_half 
    WHERE match_id = %s
", $match_id);

$statistics = $wpdb->get_results($statistics_query);

// Определяем, есть ли статистика
$has_statistics = !empty($statistics);

$class = '';
switch ($status) {
    case 1:
        $class = 'not-started'; // Матч не начался
        break;
    case 2:
        $class = 'live'; // Матч идет (в прямом эфире)
        break;
    case 3:
        $class = 'pause'; // Матч идет (в прямом эфире)
        break;
    case 4:
        $class = 'live'; // Матч идет (в прямом эфире)
        break;
    case 5:
        $class = 'live'; // Матч идет (в прямом эфире)
        break;
    case 6:
        $class = 'live'; // Матч идет (в прямом эфире)
        break;
    case 7:
        $class = 'live'; // Матч идет (в прямом эфире)
        break;
    case 8:
        $class = 'ended'; // Матч завершен
        break;
    default:
        $class = ''; // Неизвестный статус
        break;
}
?>

<section class="match <?php echo $class; ?>" id="match-<?php echo $match_id ?>" data-matchid="<?php echo $match_id ?>">

    <?php get_template_part('template-parts/sport/statistics-breadcrumbs'); ?>

    <?php get_template_part('template-parts/sport/football/statistics-scoreboard', null, ['match_data' => $match]); ?>

    <div class="match__tabs tabs">
        <div class="tabs__list">
            <?php if ($status !== 1 && !empty($match->incidents)): ?>
                <div class="tabs__item active" data-status="review">
                    <span><?php pll_e('Обзор') ?></span>
                </div>
            <?php endif ?>

            <?php if ($status !== 8 && isset($odds)): ?>
                <div class="tabs__item" data-status="odds">
                    <span><?php pll_e('Коэфициенты') ?></span>
                </div>
            <?php endif ?>

            <?php if ($status !== 1 && $has_statistics): ?>
                <div class="tabs__item" data-status="statistics">
                    <span><?php pll_e('Статистика') ?></span>
                </div>
            <?php endif; ?>

            <div class="tabs__item" data-status="h2h">
                <span><?php pll_e('H2H') ?></span>
            </div>

            <div class="tabs__item" data-status="standings">
                <span><?php pll_e('Турнирная таблица') ?></span>
            </div>

            <?php if ($lineup): ?>
                <div class="tabs__item" data-status="squad">
                    <span><?php pll_e('Состав') ?></span>
                </div>
            <?php endif ?>
        </div>
    </div>

    <div class="match__tabs-content tabs__content">
        <?php if (isset($match) && (int) $match->status_id !== 1 && !empty($match->incidents)): ?>
            <div class="tabs__content-item" data-status="review">
                <?php get_template_part('template-parts/sport/football/statistics-match-review'); ?>
            </div>
        <?php endif ?>

        <?php if (isset($odds)): ?>
            <div class="tabs__content-item" data-status="odds">
                <?php get_template_part('template-parts/sport/football/statistics-match-odds'); ?>
            </div>
        <?php endif ?>

        <?php if ($status !== 1 && $has_statistics): ?>
            <div class="tabs__content-item" data-status="statistics">
                <?php get_template_part('template-parts/sport/football/statistics-match-statistics'); ?>
            </div>
        <?php endif; ?>

        <div class="tabs__content-item" data-status="h2h">
            <?php get_template_part('template-parts/sport/football/statistics-match-h2h'); ?>
        </div>

        <div class="tabs__content-item" data-status="standings">
            <?php get_template_part('template-parts/sport/football/statistics-match-standings'); ?>
        </div>

        <div class="tabs__content-item" data-status="squad">
            <?php get_template_part('template-parts/sport/football/statistics-match-composition'); ?>
        </div>

        <?php get_template_part('template-parts/sport/statistics-loader') ?>
    </div>
</section>