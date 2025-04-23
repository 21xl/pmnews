<?php
$bookmaker = get_field('bookmaker', 'option');
$match_data = $args['match_data'] ?? null;

if (!$match_data) {
    echo '<p>Match data is not available.</p>';
    return;
}

// Извлекаем данные
$match_id = $match_data['id'];
$status_id = (string) ($match_data['status_id'] ?? '0');
$match_time = (int) ($match_data['match_time'] ?? 0);
$home_team_name = get_translated_name($match_data['homeTeam']['names']);
$away_team_name = get_translated_name($match_data['awayTeam']['names']);
$scores = $match_data['scores'] ?? ['ft' => [0, 0]];

// Определяем, парный ли матч, и получаем логотипы
$is_doubles = ($match_data['tournament']['type'] ?? 0) === 2 || !empty($match_data['homeTeam']['sub_ids']);
$home_logos = [];
$away_logos = [];
$default_logo = '/wp-content/themes/pm-news/sport/src/img/tennis-player-placeholder.svg';

if ($is_doubles && !empty($match_data['homeTeam']['subs'])) {
    $home_logos = array_map(function ($sub) use ($default_logo) {
        return [
            'logo' => $sub['logo'] ?? $default_logo,
            'slug' => $sub['slug'] ?? '',
            'id' => $sub['id'] ?? ''
        ];
    }, $match_data['homeTeam']['subs']);
} else {
    $home_logos[] = [
        'logo' => $match_data['homeTeam']['logo'] ?? $default_logo,
        'slug' => $match_data['homeTeam']['slug'] ?? '',
        'id' => $match_data['home_team_id'] ?? ''
    ];
}

if ($is_doubles && !empty($match_data['awayTeam']['subs'])) {
    $away_logos = array_map(function ($sub) use ($default_logo) {
        return [
            'logo' => $sub['logo'] ?? $default_logo,
            'slug' => $sub['slug'] ?? '',
            'id' => $sub['id'] ?? ''
        ];
    }, $match_data['awayTeam']['subs']);
} else {
    $away_logos[] = [
        'logo' => $match_data['awayTeam']['logo'] ?? $default_logo,
        'slug' => $match_data['awayTeam']['slug'] ?? '',
        'id' => $match_data['away_team_id'] ?? ''
    ];
}

// Форматирование времени (без изменений)
$dt = new DateTime();
$dt->setTimestamp($match_time);
$format1 = $dt->format('d/m H:i');
$format1_1 = $dt->format('H:i');
$months = [
    'January' => 'January',
    'February' => 'February',
    'March' => 'March',
    'April' => 'April',
    'May' => 'May',
    'June' => 'June',
    'July' => 'July',
    'August' => 'August',
    'September' => 'September',
    'October' => 'October',
    'November' => 'November',
    'December' => 'December',
];
$month = $months[$dt->format('F')];
$format2 = $dt->format('d') . ' ' . $month;

// Логика отображения времени или статуса (без изменений)
$currentTimestamp = time();
$isToday = $dt->format('Y-m-d') === date('Y-m-d', $currentTimestamp);
$display_time = '';
switch ($status_id) {
    case '0':
        $display_time = pll__('Hide');
        break;
    case '1':
        $display_time = $format2; // тут заменил  $display_time = $isToday ? $format1_1 : $format2 . ' ' . $format1_1;
        break;
    case '3':
        $display_time = pll__('Матч начался');
        break;
    case '51':
        $display_time = '1 ' . pll__('Set');
        break;
    case '52':
        $display_time = '2 ' . pll__('Set');
        break;
    case '53':
        $display_time = '3 ' . pll__('Set');
        break;
    case '54':
        $display_time = '4 ' . pll__('Set');
        break;
    case '55':
        $display_time = '5 ' . pll__('Set');
        break;
    case '100':
        $display_time = pll__('Ended');
        break;
    case '20':
        $display_time = pll__('Technical victory');
        break;
    case '21':
        $display_time = pll__('Refusel');
        break;
    case '14':
        $display_time = pll__('Delayed');
        break;
    case '15':
        $display_time = pll__('Detained');
        break;
    case '16':
        $display_time = pll__('Canceled');
        break;
    case '17':
        $display_time = pll__('Interrupted');
        break;
    case '18':
        $display_time = pll__('Suspended');
        break;
    case '19':
        $display_time = pll__('Interrupted');
        break;
    case '99':
        $display_time = '--:--';
        break;
    default:
        $display_time = $format2;
        break;
}
?>

<div class="scoreboard scoreboard--tennis">
    <div class="scoreboard__wrapper"
        style="background-image: url(<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/scoreboard-tennis.png'); ?>);">

        <div class="scoreboard__main">
            <div class="scoreboard__block">
                <?php if ($is_doubles): ?>
                    <div class="scoreboard__logo-list">
                        <?php foreach ($home_logos as $index => $player): ?>
                            <a
                                href="/statistics/tennis/player/<?php echo esc_attr($match_data['homeTeam']['subs'][$index]['slug'] ?? $player['slug']); ?>/<?php echo esc_attr($player['id']); ?>/">
                                <div class="scoreboard__logo-double">
                                    <img src="<?php echo esc_url($player['logo']); ?>"
                                        alt="<?php echo esc_attr(get_translated_name($match_data['homeTeam']['subs'][$index]['names'])); ?>">
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <a
                        href="/statistics/tennis/player/<?php echo esc_attr($home_logos[0]['slug']); ?>/<?php echo esc_attr($home_logos[0]['id']); ?>/">
                        <div class="scoreboard__logo-single">
                            <?php foreach ($home_logos as $player): ?>
                                <img src="<?php echo esc_url($player['logo']); ?>"
                                    alt="<?php echo esc_attr($home_team_name); ?>">
                            <?php endforeach; ?>
                        </div>
                    </a>
                <?php endif; ?>

                <div class="scoreboard__score">
                    <?php if ($status_id === '100'): ?>
                        <div class="scoreboard__old-time" data-time="<?php echo esc_attr($match_time); ?>">
                            <?php echo esc_html($format1); ?>
                        </div>
                    <?php endif; ?>

                    <div class="scoreboard__minute" data-time="<?php echo esc_attr($match_time); ?>">
                        <span><?php echo esc_html($display_time); ?></span>
                    </div>

                    <div class="scoreboard__goals <?php echo $status_id === '1' ? 'shedul' : ''; ?>"
                        data-time="<?php echo esc_attr($match_time); ?>">
                        <?php if ($status_id !== '1' && isset($scores['ft'][0])): ?>
                            <span><?php echo esc_html($scores['ft'][0] . ':' . $scores['ft'][1]); ?></span>
                        <?php else: ?>
                            <span><?php echo esc_html($format1_1); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($is_doubles): ?>
                    <div class="scoreboard__logo-list">
                        <?php foreach ($away_logos as $index => $player): ?>
                            <a
                                href="/statistics/tennis/player/<?php echo esc_attr($match_data['awayTeam']['subs'][$index]['slug'] ?? $player['slug']); ?>/<?php echo esc_attr($player['id']); ?>/">
                                <div class="scoreboard__logo-double">
                                    <img src="<?php echo esc_url($player['logo']); ?>"
                                        alt="<?php echo esc_attr(get_translated_name($match_data['awayTeam']['subs'][$index]['names'])); ?>">
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <a
                        href="/statistics/tennis/player/<?php echo esc_attr($away_logos[0]['slug']); ?>/<?php echo esc_attr($away_logos[0]['id']); ?>/">
                        <div class="scoreboard__logo-single">
                            <?php foreach ($away_logos as $player): ?>
                                <img src="<?php echo esc_url($player['logo']); ?>"
                                    alt="<?php echo esc_attr($away_team_name); ?>">
                            <?php endforeach; ?>
                        </div>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($is_doubles): ?>
                <div class="scoreboard__names">
                    <div class="scoreboard__name">
                        <a href="/statistics/tennis/player/<?php echo esc_attr($match_data['homeTeam']['subs'][0]['slug']); ?>/<?php echo esc_attr($match_data['homeTeam']['subs'][0]['id']); ?>/"
                            class="scoreboard__name">
                            <span><?php echo esc_html(get_translated_name($match_data['homeTeam']['subs'][0]['names'])); ?></span>
                        </a>

                        <div class="scoreboard__slash">/</div>

                        <a href="/statistics/tennis/player/<?php echo esc_attr($match_data['homeTeam']['subs'][1]['slug']); ?>/<?php echo esc_attr($match_data['homeTeam']['subs'][1]['id']); ?>/"
                            class="scoreboard__name">
                            <span><?php echo esc_html(get_translated_name($match_data['homeTeam']['subs'][1]['names'])); ?></span>
                        </a>
                    </div>

                    <span class="scoreboard__vs">VS</span>

                    <div class="scoreboard__name">
                        <a href="/statistics/tennis/player/<?php echo esc_attr($match_data['awayTeam']['subs'][0]['slug']); ?>/<?php echo esc_attr($match_data['awayTeam']['subs'][0]['id']); ?>/"
                            class="scoreboard__name">
                            <span><?php echo esc_html(get_translated_name($match_data['awayTeam']['subs'][0]['names'])); ?></span>
                        </a>

                        <div class="scoreboard__slash">/</div>

                        <a href="/statistics/tennis/player/<?php echo esc_attr($match_data['awayTeam']['subs'][1]['slug']); ?>/<?php echo esc_attr($match_data['awayTeam']['subs'][1]['id']); ?>/"
                            class="scoreboard__name">
                            <span><?php echo esc_html(get_translated_name($match_data['awayTeam']['subs'][1]['names'])); ?></span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="scoreboard__names">
                    <a href="/statistics/tennis/player/<?php echo esc_attr($home_logos[0]['slug']); ?>/<?php echo esc_attr($home_logos[0]['id']); ?>/"
                        class="scoreboard__name">
                        <span><?php echo esc_html($home_team_name); ?></span>
                    </a>

                    <span class="scoreboard__vs">VS</span>

                    <a href="/statistics/tennis/player/<?php echo esc_attr($away_logos[0]['slug']); ?>/<?php echo esc_attr($away_logos[0]['id']); ?>/"
                        class="scoreboard__name">
                        <span><?php echo esc_html($away_team_name); ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <div class="scoreboard__status">
                <div
                    class="scoreboard__ball<?php echo (isset($match_data['serving_side']) && $match_data['serving_side'] == 1) ? ' active' : ''; ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/tennis-ball-white.svg') ?>"
                        alt="Ball">
                </div>

                <div
                    class="scoreboard__ball<?php echo (isset($match_data['serving_side']) && $match_data['serving_side'] == 2) ? ' active' : ''; ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/tennis-ball-white.svg') ?>"
                        alt="Ball">
                </div>
            </div>

            <?php if ($bookmaker): ?>
                <a href="<?php echo esc_url($bookmaker['link']); ?>" rel="nofollow noopener" target="_blank"
                    class="scoreboard__partner">
                    <span><?php pll_e('Watch online'); ?></span>
                    <div class="scoreboard__partner-img">
                        <img src="<?php echo esc_url($bookmaker['logo']['url']); ?>" alt="<?php pll_e('Watch online'); ?>">
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const timeElements = document.querySelectorAll(".scoreboard__old-time");
        const minutes = document.querySelectorAll(".shedul")[0];

        timeElements.forEach((element) => {
            const utcTimestamp = element.getAttribute("data-time");
            if (utcTimestamp) {
                const utcTimeMs = parseInt(utcTimestamp, 10) * 1000;
                const date = new Date(utcTimeMs);
                const formattedTime = `${date.getDate().toString().padStart(2, "0")}.${(
                    date.getMonth() + 1
                ).toString().padStart(2, "0")} ${date.toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                })}`;
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