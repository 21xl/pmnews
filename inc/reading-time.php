<?php
function get_reading_time()
{
    $post = get_post(get_the_ID());
    $post_content = $post->post_content;
    $clean_content = wp_strip_all_tags($post_content);
    $clean_content = trim(preg_replace('/\s+/', ' ', $clean_content));

    preg_match_all('/\p{L}+/u', $clean_content, $matches);
    $word_count = count($matches[0]);

    $reading_speed = 200;
    $reading_time = ceil($word_count / $reading_speed);

    if ($reading_time < 1) {
        $reading_time = 1;
    }

    if ($reading_time == 1) {
        $time_text = "1 " . pll__('мин');
    } else {
        $time_text = $reading_time . " " . pll__('мин');
    }

    return $time_text;
}