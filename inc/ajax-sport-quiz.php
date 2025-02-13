<?php

function submit_quiz_vote()
{
    error_log('Полученные данные: ' . print_r($_POST, true));
    if (!isset($_POST['updatedStats']) || !isset($_POST['quiz_id'])) {
        error_log('Ошибка: нет выбора или ID квиза');
        wp_send_json_error(['message' => 'Ошибка: нет выбора или ID квиза']);
    }

    $quiz_id = sanitize_text_field($_POST['quiz_id']);
    $updatedStats = json_decode(stripslashes($_POST['updatedStats']), true);

    update_option("sport_quiz_results_{$quiz_id}", $updatedStats);
    $newStats = get_option("sport_quiz_results_{$quiz_id}");

    wp_send_json_success([
        'data' => $newStats
    ]);
}

add_action('wp_ajax_quiz_ajax', 'submit_quiz_vote');
add_action('wp_ajax_nopriv_quiz_ajax', 'submit_quiz_vote');

function get_quiz_stats()
{
    if (!isset($_POST['quiz_id'])) {
        wp_send_json_error(['message' => 'Ошибка: нет ID квиза']);
    }

    $quiz_id = sanitize_text_field($_POST['quiz_id']);
    $reset = false;
    $elements = intval($_POST['elements']);
    $option_name = "sport_quiz_results_{$quiz_id}";
    $votes = get_option($option_name);

    // if ($votes['version']) {
    //     $table_version = $votes['version'];
    // }

    if ($votes === false || !is_array($votes)) {
        $votes = [];
    }


    if (count($votes) != $elements) {
        delete_option("sport_quiz_results_{$quiz_id}");
        $votes = [];
        $reset = true;
        // $table_version += 1;
        for ($i = 0; $i < $elements; $i++) {
            if (!isset($votes[$i])) {
                $votes[$i] = ['count' => 0, 'percentage' => '0.00'];
            }
        }
        // $votes['version'] = $table_version;
    }

    $total_votes = array_sum(array_column($votes, 'count'));

    if ($total_votes > 0) {
        foreach ($votes as $key => $vote) {
            $votes[$key]['percentage'] = round(($vote['count'] / $total_votes) * 100, 2);
        }
    }

    update_option($option_name, $votes);
    wp_send_json_success([
        'data' => $votes,
        'reset' => $reset,
    ]);
}


add_action('wp_ajax_get_quiz_stats', 'get_quiz_stats');
add_action('wp_ajax_nopriv_get_quiz_stats', 'get_quiz_stats');
