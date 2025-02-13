<?php
get_template_part('head');

global $post;
$author_id = $post->post_author;
$author_first_name = get_the_author_meta('first_name', $author_id);
$author_last_name = get_the_author_meta('last_name', $author_id);
$author_avatar = get_field('author_avatar', 'user_' . $author_id);
$author_position = get_field('author_position', 'user_' . $author_id);
$author_url = get_author_posts_url($author_id);
?>

<div class="progress-bar">
	<div class="progress-bar__inner"></div>
</div>

<div class="page">
	<?php get_template_part('template-parts/aside') ?>

	<div class="content">
		<?php get_header('white'); ?>

		<main class="single__main">
			<div class="single__container container">
				<section class="single__content">

					<?php get_template_part('template-parts/breadcrumbs') ?>

					<div class="single__wrapper wrapper">
						<div class="single__info">
							<div class="single__info-head">
								<div class="single__info-date">
									<?php echo esc_html(get_the_date()); ?>
								</div>

								<div class="single__info-time">
									<?php pll_e('Время для чтения:'); ?> <?php echo esc_html(get_reading_time()); ?>
								</div>
							</div>

							<div class="single__share single__share--desktop">
								<?php get_template_part('template-parts/share') ?>
							</div>

							<div class="single__info-numbers single__info-numbers--mobile">
								<div class="single__info-rate">
									<span>5.0</span>

									<img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/star.svg') ?>"
										alt="Рейтинг">
								</div>

								<div class="single__info-comments">
									<span>21</span>

									<img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/comments.svg') ?>"
										alt="Комментарии">
								</div>
							</div>
						</div>

						<h1 class="single__title">
							<?php echo esc_html(get_the_title()) ?>
						</h1>

						<div class="single__info">
							<a href="<?php echo esc_url($author_url); ?>" class="single__author">
								<?php if ($author_avatar): ?>
									<div class="single__author-img">
										<img src="<?php echo esc_url($author_avatar); ?>"
											alt="<?php echo esc_html($author_first_name . ' ' . $author_last_name); ?>">
									</div>
								<?php else: ?>
									<div class="single__author-img single__author-img--error">
										<img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/avatar.svg') ?>"
											alt="<?php echo esc_html($author_first_name . ' ' . $author_last_name); ?>">
									</div>
								<?php endif; ?>
								<div class="single__author-content">
									<span class="single__author-name">
										<?php echo esc_html($author_first_name . ' ' . $author_last_name); ?>
									</span>

									<?php if ($author_position): ?>
										<span class="single__author-position">
											<?php echo esc_html($author_position); ?>
										</span>
									<?php endif; ?>
								</div>
							</a>

							<div class="single__info-numbers single__info-numbers--desktop">
								<div class="single__info-rate">
									<span>5.0</span>

									<img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/star.svg') ?>"
										alt="Рейтинг">
								</div>

								<div class="single__info-comments">
									<span>21</span>

									<img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/comments.svg') ?>"
										alt="Комментарии">
								</div>
							</div>

							<div class="single__share single__share--mobile">
								<?php get_template_part('template-parts/share') ?>
							</div>
						</div>

						<?php if (get_the_post_thumbnail_url()): ?>
							<div class="single__img">
								<?php
								$img_id = get_post_thumbnail_id();
								$img_url = get_the_post_thumbnail_url(null, 'medium_large');
								$img_srcset = wp_get_attachment_image_srcset($img_id, 'full');
								$img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: get_the_title();
								?>
								<img src="<?php echo esc_url($img_url); ?>" srcset="<?php echo esc_attr($img_srcset); ?>"
									sizes="(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 33vw"
									alt="<?php echo esc_attr($img_alt); ?>" loading="lazy">

								<?php
								$post_tags = get_the_tags();
								if ($post_tags): ?>
									<div class=" tags">
										<?php foreach ($post_tags as $tag): ?>
											<a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tags__item">
												<?php echo esc_html($tag->name); ?>
											</a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</section>

				<?php get_template_part('template-parts/editor-content') ?>

				<?php get_template_part('template-parts/tg-banner') ?>
			</div>

			<?php get_template_part('template-parts/single-aside') ?>

		</main>
	</div>
</div>

<?php get_footer(); ?>