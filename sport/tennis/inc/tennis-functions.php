<?php

// Определяем базовый URL с проверкой доступности
$base_url = defined('API_URL') ? API_URL : 'http://sport_back:3277';


function get_tennis_categories()
{
    global $base_url;
    $cache_key = 'tennis_categories_cache1';
    $cache_duration = HOUR_IN_SECONDS;

    $categories = get_transient($cache_key);
    if ($categories !== false) {
        return $categories;
    }

    $response = wp_remote_get("{$base_url}/api/tennis/categories", [
        'timeout' => 30,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $categories = json_decode($body, true);
        if (is_array($categories)) {
            set_transient($cache_key, $categories, $cache_duration);
            return $categories;
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP код: ' . wp_remote_retrieve_response_code($response);
        error_log("Ошибка при запросе категорий: {$error_message}");
    }

    return [];
}

function get_translated_name($names, $language = null)
{
    $current_language = $language ?: (function_exists('pll_current_language') ? pll_current_language() : 'en');
    $translation_key = "name_$current_language";

    if (!empty($names) && isset($names[$translation_key])) {
        return $names[$translation_key];
    }
    if (!empty($names) && isset($names['name_en'])) {
        return $names['name_en'];
    }
    return 'Unnamed';
}

function get_tennis_tournament_by_id($tournament_id)
{
    global $base_url;
    if (empty($tournament_id)) {
        return null;
    }

    $cache_key = "tennis_tournament_{$tournament_id}_cache";
    $cache_duration = HOUR_IN_SECONDS;

    $tournament = get_transient($cache_key);
    if ($tournament !== false) {
        return $tournament;
    }

    $response = wp_remote_get("{$base_url}/api/tennis/tournaments/{$tournament_id}", [
        'timeout' => 30,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $tournament = json_decode($body, true);
        if (is_array($tournament) && !empty($tournament['id'])) {
            set_transient($cache_key, $tournament, $cache_duration);
            return $tournament;
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP код: ' . wp_remote_retrieve_response_code($response);
        error_log("Ошибка при запросе турнира {$tournament_id}: {$error_message}");
    }

    return null;
}

function get_tennis_match_by_id($match_id)
{
    global $base_url;
    if (empty($match_id)) {
        return null;
    }

    $response = wp_remote_get("{$base_url}/api/tennis/match-details?match_id={$match_id}", [
        'timeout' => 30,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $match_data = json_decode($body, true);
        if (is_array($match_data) && !empty($match_data['match_id']) && isset($match_data['data'])) {
            return $match_data['data'];
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP код: ' . wp_remote_retrieve_response_code($response);
        error_log("Ошибка при запросе матча {$match_id}: {$error_message}");
    }

    return null;
}

function get_tennis_future_tournaments()
{
    global $base_url;
    $cache_key = 'tennis_future_tournaments_cache';
    $cache_duration = HOUR_IN_SECONDS;

    $tournaments = get_transient($cache_key);
    if ($tournaments !== false) {
        return $tournaments;
    }

    $response = wp_remote_get("{$base_url}/api/tennis/cur-tour", [
        'timeout' => 30,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $tournaments = json_decode($body, true);
        if (is_array($tournaments)) {
            set_transient($cache_key, $tournaments, $cache_duration);
            return $tournaments;
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP код: ' . wp_remote_retrieve_response_code($response);
        error_log("Ошибка при запросе будущих турниров: {$error_message}");
    }

    return [];
}

function get_tennis_team_info($team_id)
{
    global $base_url;
    $cache_key = "tennis_team_cache_$team_id";
    $cache_duration = HOUR_IN_SECONDS;

    $team = get_transient($cache_key);
    if ($team !== false) {
        return $team;
    }

    $response = wp_remote_get("{$base_url}/api/tennis/player-info?teamId={$team_id}", [
        'timeout' => 30,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $team = json_decode($body, true);
        if (is_array($team)) {
            set_transient($cache_key, $team, $cache_duration);
            return $team;
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP код: ' . wp_remote_retrieve_response_code($response);
        error_log("Ошибка при запросе данных о команде {$team_id}: {$error_message}");
    }

    return [];
}