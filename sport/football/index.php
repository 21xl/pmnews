<?php
/**
 * Football Custom Post Type.
 */

require_once __DIR__ . '/inc/post-type-football.php';

/**
 * API.
 */
require_once __DIR__ . '/api/get_competitions_by_country.php';
require_once __DIR__ . '/api/get_competitions_by_id.php';
require_once __DIR__ . '/api/get_matches_by_date.php';
require_once __DIR__ . '/api/matches_by_competitionid.php';
require_once __DIR__ . '/api/get_standings_data.php';
require_once __DIR__ . '/api/get_matches_by_location.php';
require_once __DIR__ . '/api/get_matche_data_incidents.php';
require_once __DIR__ . '/api/get_competitions_by_team.php';
require_once __DIR__ . '/api/get_favorite_matches.php';


/**
 * AJAX.
 */
require_once __DIR__ . '/ajax/update-football-posts.php';
require_once __DIR__ . '/ajax/update-football-team-posts.php';

