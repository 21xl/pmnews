<?php
function translate_human_time_diff($from, $to)
{
    $time_diff = human_time_diff($from, $to);

    $translations = array(
        'hour' => array(pll__('час'), pll__('часа'), pll__('часов')),
        'hours' => array(pll__('час'), pll__('часа'), pll__('часов')),
        'min' => array(pll__('минута'), pll__('минуты'), pll__('минут')),
        'mins' => array(pll__('минута'), pll__('минуты'), pll__('минут')),
        'day' => array(pll__('день'), pll__('дня'), pll__('дней')),
        'days' => array(pll__('день'), pll__('дня'), pll__('дней')),
        'week' => array(pll__('неделя'), pll__('недели'), pll__('недель')),
        'weeks' => array(pll__('неделя'), pll__('недели'), pll__('недель')),
        'month' => array(pll__('месяц'), pll__('месяца'), pll__('месяцев')),
        'months' => array(pll__('месяц'), pll__('месяца'), pll__('месяцев')),
        'second' => array(pll__('секунда'), pll__('секунды'), pll__('секунд')),
        'seconds' => array(pll__('секунда'), pll__('секунды'), pll__('секунд')),
    );

    foreach ($translations as $english => $translation) {
        if (preg_match('/(\d+)\s+' . preg_quote($english, '/') . '\b/', $time_diff, $matches)) {
            $number = (int) $matches[1];
            $correct_form = get_correct_form($number, $translation);
            $time_diff = preg_replace('/\b' . preg_quote($english, '/') . '\b/', $correct_form, $time_diff);
        }
    }

    return $time_diff;
}

function get_correct_form($number, $forms)
{
    $n = $number % 100;
    if ($n >= 11 && $n <= 19) {
        return $forms[2];
    }
    $n = $number % 10;
    if ($n == 1) {
        return $forms[0];
    }
    if ($n >= 2 && $n <= 4) {
        return $forms[1];
    }
    return $forms[2];
}
