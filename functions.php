<?php
/**
 * Base configuration.
 */
if (!defined('_S_VERSION')) {
	define('_S_VERSION', '1.0.0');
}

function pm_news_setup()
{
	load_theme_textdomain('pm_news', get_template_directory() . '/languages');

	add_theme_support('automatic-feed-links');


	add_theme_support('title-tag');

	add_theme_support('post-thumbnails');

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support('customize-selective-refresh-widgets');
}
add_action('after_setup_theme', 'pm_news_setup');

function pm_news_content_width()
{
	$GLOBALS['content_width'] = apply_filters('pm_news_content_width', 640);
}
add_action('after_setup_theme', 'pm_news_content_width', 0);


/**
 * Включаем авто-встраивание ссылок
 */
add_filter('embed_oembed_discover', '__return_true');

/**
 * HTTP Headers.
 */
require get_template_directory() . '/inc/http-headers.php';


/**
 * Remove empty <p> in editor.
 */
add_filter('wpcf7_autop_or_not', '__return_false');


/**
 * Enqueue scripts and styles.
 */
require get_template_directory() . '/inc/enque-scripts-and-styles.php';


/**
 * Remove inline WP styles.
 */
require get_template_directory() . '/inc/remove-wp-styles.php';


/**
 * Add custom logo in wp-admin.
 */
require get_template_directory() . '/inc/wp-admin-logo.php';


/**
 * Transliteration.
 */
require get_template_directory() . '/inc/transliteration.php';


/**
 * Register Menus.
 */
require get_template_directory() . '/inc/menu-registration.php';


/**
 * Polylang String Translations.
 */
require get_template_directory() . '/inc/translates-registration.php';


/**
 * Language switcher.
 */
require get_template_directory() . '/inc/language-switcher.php';


/**
 * Reading time for posts.
 */
require get_template_directory() . '/inc/reading-time.php';


/**
 * Remove screen reader text from pagination.
 */
require get_template_directory() . '/inc/remove-screen-reader-text.php';


/**
 * Menu Walker.
 */
require get_template_directory() . '/inc/menu-walker.php';


/**
 * Security configuration.
 */
require get_template_directory() . '/inc/security.php';


/**
 * Configuration for links.
 */
require get_template_directory() . '/inc/links.php';


/**
 * Recommended news.
 */
require get_template_directory() . '/inc/recommended-news.php';


/**
 * Add custom confirations on author.
 */
require get_template_directory() . '/inc/author-configurations.php';


/**
 * Translation for daily time
 */
require get_template_directory() . '/inc/human-time.php';


/**
 * Search Ajax.
 */
require get_template_directory() . '/inc/ajax-search.php';


/**
 * JWT-token auth expire time.
 */
require get_template_directory() . '/inc/jwt-time.php';


/**
 * SportQuiz Ajax.
 */
require get_template_directory() . '/inc/ajax-sport-quiz.php';


/**
 * Convert images to webp
 */
require get_template_directory() . '/inc/webp-converter.php';


/**
 * Image exif data.
 */
add_filter('wp_read_image_metadata', '__return_false');


/**
 * Sports API.
 */
require get_template_directory() . '/sport/translates-registration.php';
require get_template_directory() . '/sport/index.php';
require get_template_directory() . '/sport/football/index.php';
// require get_template_directory() . '/sport/tennis/index.php';

//Disable canonical from Rank Math
add_filter('rank_math/canonical_url', '__return_false');
