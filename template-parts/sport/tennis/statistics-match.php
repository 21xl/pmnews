<?php
// Проверяем, переданы ли данные матча
if (!isset($args['match']) || empty($args['match'])) {
    echo '<p>Match data not available.</p>';
    return;
}

$match = $args['match'];

// Извлекаем ключевые данные
$match_id = $match['id'];
$status_id = (string) ($match['status_id'] ?? '0'); // Приводим к строке, как в Handlebars
$home_team_name = get_translated_name($match['homeTeam']['names']);
$away_team_name = get_translated_name($match['awayTeam']['names']);
$tournament_name = $match['tournament']['name'] ?? 'Unknown Tournament';
$match_time = $match['match_time'] ?? 0;

// Определяем класс и статус матча
$class = '';
switch ($status_id) {
    case '0': // Hidden
        $class = 'hidden';
        break;
    case '1': // Not Started
        $class = 'not-started';
        break;
    case '3': // In Progress
    case '51': // First Set
    case '52': // Second Set
    case '53': // Third Set
    case '54': // Fourth Set
    case '55': // Fifth Set
        $class = 'live';
        break;
    case '100': // Ended
        $class = 'ended';
        break;
    case '20': // Walkover
    case '21': // Retired
    case '22': // Walkover1
    case '23': // Walkover2
    case '24': // Retired1
    case '25': // Retired2
    case '26': // Defaulted1
    case '27': // Defaulted2
        $class = 'ended'; // Эти случаи считаем завершенными
        break;
    case '14': // Postponed
    case '15': // Delayed
    case '16': // Canceled
    case '17': // Interrupted
    case '18': // Suspension
    case '19': // Cut in half
    case '99': // To be determined
        $class = 'not-started'; // Эти статусы считаем "не начавшимися" или отложенными
        break;
    default:
        $class = 'hidden'; // Неизвестный статус
        break;
}

// Проверяем наличие статистики и таймлайна
$has_statistics = !empty($match['stats']);
$has_timeline = !empty($match['timeline']);
?>

<section class="match <?php echo esc_attr($class); ?>" id="match-<?php echo esc_attr($match_id); ?>"
    data-matchid="<?php echo esc_attr($match_id); ?>">

    <?php
    // Хлебные крошки
    // get_template_part('template-parts/sport/tennis/statistics-breadcrumbs');
    ?>

    <?php
    // Табло с результатами
    get_template_part('template-parts/sport/tennis/statistics-scoreboard', null, [
        'match_data' => $match
    ]);
    ?>

    <div class="match__tabs tabs">
        <div class="tabs__list">
            <?php if (in_array((string) $status_id, ['3', '51', '52', '53', '54', '55', '100'])): ?>
                <div class="tabs__item active" data-status="review">
                    <span><?php pll_e('Review'); ?></span>
                </div>
            <?php endif; ?>

            <?php if (in_array((string) $status_id, ['3', '51', '52', '53', '54', '55', '100']) && $has_statistics): ?>
                <div class="tabs__item" data-status="statistics">
                    <span><?php pll_e('Statistics'); ?></span>
                </div>
            <?php endif; ?>

            <div class="tabs__item" data-status="h2h">
                <span><?php pll_e('H2H'); ?></span>
            </div>

            <div class="tabs__item" data-status="standings">
                <span><?php pll_e('Tournament Grid'); ?></span>
            </div>
        </div>
    </div>

    <div class="match__tabs-content tabs__content">
        <?php if (in_array($status_id, ['3', '51', '52', '53', '54', '55', '100'])): ?>
            <div class="tabs__content-item" data-status="review">
                <?php
                get_template_part('template-parts/sport/tennis/statistics-match-review', null, [
                    'timeline' => $match['timeline'],
                    'home_team_name' => $home_team_name,
                    'away_team_name' => $away_team_name
                ]);
                ?>
            </div>
        <?php endif; ?>

        <?php if (in_array($status_id, ['3', '51', '52', '53', '54', '55', '100']) && $has_statistics): ?>
            <div class="tabs__content-item" data-status="statistics">
                <?php
                get_template_part('template-parts/sport/tennis/statistics-match-statistics', null, [
                    'stats' => $match['stats'],
                    'home_team_name' => $home_team_name,
                    'away_team_name' => $away_team_name
                ]);
                ?>
            </div>
        <?php endif; ?>

        <div class="tabs__content-item" data-status="h2h">
            <?php
            get_template_part('template-parts/sport/tennis/statistics-match-h2h', null, [
                'home_team_id' => $match['home_team_id'],
                'away_team_id' => $match['away_team_id']
            ]);
            ?>
        </div>

        <div class="tabs__content-item" data-status="standings">
            <?php
            get_template_part('template-parts/sport/tennis/statistics-match-standings', null, [
                'tournament_id' => $match['tournament_id']
            ]);
            ?>
        </div>

        <?php get_template_part('template-parts/sport/statistics-loader'); ?>
    </div>
</section>