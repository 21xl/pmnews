<?php
$bookmaker = get_field('bookmaker', 'option');
$match_id = get_query_var('match_id');
$match_data = $args['match_data'];

$format1 = date('d/m H:i', (int) $match_data->match_time);
// $format1 = (int) $match_data->match_time;   
$format1_1 = date('H:i', (int) $match_data->match_time);
$status = (int) $match_data->status_id;

$dt = new DateTime();
$dt->setTimestamp((int) $match_data->match_time);
$format2 = $dt->format('d') . ' ' . mb_strtolower($dt->format('F'), 'UTF-8');
$months = [
    'January' => 'january',
    'February' => 'february',
    'March' => 'march',
    'April' => 'april',
    'May' => 'may',
    'June' => 'june',
    'July' => 'july',
    'August' => 'august',
    'September' => 'september',
    'October' => 'october',
    'November' => 'november',
    'December' => 'december',
];

$month = $months[$dt->format('F')];
$format2 = $dt->format('d') . ' ' . $month;

$scores_home = json_decode($match_data->home_scores, true);
$scores_away = json_decode($match_data->away_scores, true);
?>

<div class="scoreboard">
    <div class="scoreboard__wrapper"
        style="background-image: url(<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/scoreboard.png') ?>);">

        <div class="scoreboard__main">
            <div class="scoreboard__block">
                <a href="/statistics/teams/<?= $match_data->home_team_slug ?>/">
                    <div class="scoreboard__logo">
                        <img src="<?php echo (!empty($match_data->home_team_logo) ? $match_data->home_team_logo : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg') ?>"
                            alt="<?php echo $match_data->home_team_name ?>">
                    </div>
                </a>

                <div class="scoreboard__score">
                    <?php if ($status == 8): ?>
                        <div class="scoreboard__old-time" data-time="<?php echo (int) $match_data->match_time ?>">
                            <?php echo $format1; ?>
                        </div>
                    <?php endif ?>

                    <div class="scoreboard__minute" data-time="<?php echo (int) $match_data->match_time ?>">
                        <span>
                            <?php
                            $currentTimestamp = time(); // Текущее время в Unix timestamp
                            $kickoffTimestamp = (int) $match_data->kickoff_timestamp;

                            switch ($status) {
                                case 1:
                                    echo $format2; // Время матча
                                    break;
                                case 8:
                                    echo 'Завершен'; // Завершен
                                    break;
                                case 3:
                                    echo 'Перерыв'; // Завершен
                                    break;
                                case 2: // Первый тайм
                                    $matchMinutes = floor(($currentTimestamp - $kickoffTimestamp) / 60) + 1;
                                    if ($matchMinutes > 45) {
                                        $matchMinutes = '45+' . ($matchMinutes - 45);
                                    }
                                    echo $matchMinutes;
                                    break;
                                case 4: // Второй тайм
                                    $matchMinutes = floor(($currentTimestamp - $kickoffTimestamp) / 60) + 46;
                                    if ($matchMinutes > 90) {
                                        $matchMinutes = '90+' . ($matchMinutes - 90);
                                    }
                                    echo $matchMinutes;
                                    break;
                                case 5: // Овертайм
                                    $matchMinutes = floor(($currentTimestamp - $kickoffTimestamp) / 60) + 45;
                                    $matchMinutes = '90+' . ($matchMinutes - 90);
                                    echo $matchMinutes;
                                    break;
                                default:
                                    echo $format2; // Дата матча
                                    break;
                            }
                            ?>
                    </div>

                    <div class="scoreboard__goals <?php echo $status === 1 ? 'shedul' : ''; ?>"
                        data-time="<?php echo (int) $match_data->match_time ?>">
                        <?php if (is_array($scores_home) && isset($scores_home[0]) && (int) $match_data->status_id !== 1): ?>
                            <span>
                                <?php echo $scores_home[0] ?>:<?php echo $scores_away[0] ?>
                            </span>
                        <?php else: ?>
                            <span>
                                <?php echo $format1_1; ?>
                            </span>
                        <?php endif ?>
                    </div>
                </div>
                <a href="/statistics/teams/<?= $match_data->away_team_slug ?>/">
                    <div class="scoreboard__logo">
                        <img src="<?php echo (!empty($match_data->away_team_logo) ? $match_data->away_team_logo : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg') ?>"
                            alt="<?php echo $match_data->away_team_name ?>">
                    </div>
                </a>
            </div>

            <div class="scoreboard__names">
                <a href="/statistics/teams/<?= $match_data->home_team_slug ?>/" class="scoreboard__name">
                    <span><?php echo $match_data->home_team_name ?></span>
                </a>

                <span class="scoreboard__vs">VS</span>

                <a href="/statistics/teams/<?= $match_data->away_team_slug ?>/" class="scoreboard__name">
                    <span><?php echo $match_data->away_team_name ?></span>
                </a>
            </div>
        </div>

        <?php if ($bookmaker): ?>
            <a href="<?php echo esc_url($bookmaker['link']) ?>" rel="nofollow noopener" target="_blank"
                class="scoreboard__partner">

                <span><?php pll_e('Watch online'); ?></span>

                <div class="scoreboard__partner-img">
                    <img src="<?php echo esc_url($bookmaker['logo']['url']) ?>" alt="<?php pll_e('Watch online'); ?>">
                </div>
            </a>
        <?php endif; ?>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const timeElements = document.querySelectorAll(".scoreboard__old-time");
        const minutes = document.querySelectorAll(".shedul")[0];

        timeElements.forEach((element) => {
            const utcTimestamp = element.getAttribute("data-time");
            if (utcTimestamp) {
                // Преобразуем таймстамп в объект Date
                const utcTimeMs = parseInt(utcTimestamp, 10) * 1000;
                const date = new Date(utcTimeMs);

                // Преобразуем в локальное время и форматируем
                const formattedTime = `${date.getDate().toString().padStart(2, "0")}.${(
                    date.getMonth() + 1
                )
                    .toString()
                    .padStart(2, "0")} ${date.toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                    })}`;

                // Устанавливаем форматированное время
                element.textContent = formattedTime;
            }
        });

        if (minutes) {
            const utcTimestamp = minutes.getAttribute("data-time");
            if (!utcTimestamp) return;

            const utcTimeMs = parseInt(utcTimestamp, 10) * 1000;
            const date = new Date(utcTimeMs);
            const formattedTime = date.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
            });

            let span = minutes.querySelector("span");
            if (!span) {
                span = document.createElement("span");
                minutes.appendChild(span);
            }
            span.textContent = formattedTime;
        }

    });
</script>