<?php get_template_part('head'); ?>

<div class="page">
	<?php get_template_part('template-parts/aside') ?>

	<div class="content">
		<?php get_header('white'); ?>

		<main>
			<section class="default">
				<div class="default__wrapper wrapper">
					<h1>
						<?php echo esc_html(get_the_title()) ?>
					</h1>

					<?php if (get_the_post_thumbnail_url()): ?>
						<div class="default__img">
							<img src="<?php echo esc_attr(get_the_post_thumbnail_url()) ?>"
								alt="<?php echo esc_html(get_the_title()) ?>">
						</div>
					<?php endif; ?>

					<?php if (get_the_content()): ?>
						<div class="default__content editor-content">
							<?php the_content() ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</main>
	</div>
</div>

<?php get_footer(); ?>