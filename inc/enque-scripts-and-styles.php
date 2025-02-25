<?php
function duration_scripts()
{
	// Styles
	wp_enqueue_style('reset-styles', get_template_directory_uri() . '/src/css/reset.css', array(), _S_VERSION, 'all');
	wp_enqueue_style('styles', get_stylesheet_uri(), array(), _S_VERSION, 'all');
	echo '<link rel="preload" href="' . get_template_directory_uri() . '/src/css/swiper-bundle.min.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
	echo '<noscript><link rel="stylesheet" href="' . get_template_directory_uri() . '/src/css/swiper-bundle.min.css"></noscript>';

	// Scripts
	wp_enqueue_script('main-scripts', get_template_directory_uri() . '/assets/main.js', array('jquery'), '1.0.0', true);

	// API Scripts
	wp_enqueue_script('api-scripts-football', get_template_directory_uri() . '/assets/api.js', array('jquery'), _S_VERSION, true);

	// Ajax
	wp_localize_script('api-scripts-football', 'ajax_object', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('ajax_nonce')
	));

	// SportQuiz Ajax
	wp_enqueue_script('quiz-script', get_template_directory_uri() . '/src/js/parts/sport-quiz.js', ['jquery'], null, true);
	wp_localize_script('quiz-script', 'quizAjax', ['ajax_url' => admin_url('admin-ajax.php')]);

	// Swiper Scripts
	wp_enqueue_script('swiper-scripts', get_template_directory_uri() . '/src/js/libraries/swiper-bundle.min.js', array('jquery'), _S_VERSION, true);
	wp_script_add_data('swiper-scripts', 'defer', true);

	// Comments
	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}

add_action('wp_enqueue_scripts', 'duration_scripts');