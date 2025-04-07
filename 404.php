<?php
get_template_part('head');
?>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<main>
		<section class="error-page">
			<div class="wrapper">
				<a href="<?php echo esc_url(get_home_url()); ?>" class="error-page__logo">
					<img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/logo.svg'); ?>"
						alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
				</a>

				<div class="error-page__404">
					<img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/404_text.webp'); ?>"
						alt="404 Error">
				</div>

				<h1 class="error-page__title">
					<?php pll_e('Page Not Found'); ?>
				</h1>

				<p class="error-page__text">
					<?php pll_e('Unfortunately, something went wrong, and the page could not be found. Please, go to the main page.'); ?>
				</p>

				<a href="<?php echo esc_url(get_home_url()); ?>" class="button error-page__button">
					<?php pll_e('Main page'); ?>
				</a>
			</div>
		</section>
	</main>

	<?php
	get_footer();
	?>