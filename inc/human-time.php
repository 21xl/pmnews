<?php
function translate_human_time_diff($from, $to)
{
    $time_diff = human_time_diff($from, $to);

    $translations = array(
        'hour' => array(pll__('hour'), pll__('hour'), pll__('hour')),
        'hours' => array(pll__('hours'), pll__('hours'), pll__('hours')),
        'min' => array(pll__('min'), pll__('min'), pll__('min')),
        'mins' => array(pll__('mins'), pll__('mins'), pll__('mins')),
        'day' => array(pll__('day'), pll__('day'), pll__('day')),
        'days' => array(pll__('days'), pll__('days'), pll__('days')),
        'week' => array(pll__('week'), pll__('week'), pll__('week')),
        'weeks' => array(pll__('weeks'), pll__('weeks'), pll__('weeks')),
        'month' => array(pll__('month'), pll__('month'), pll__('month')),
        'months' => array(pll__('months'), pll__('months'), pll__('months')),
        'second' => array(pll__('second'), pll__('second'), pll__('second')),
        'seconds' => array(pll__('seconds'), pll__('seconds'), pll__('seconds')),
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
