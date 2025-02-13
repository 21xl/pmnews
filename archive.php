<?php get_template_part('head'); ?>

<div class="page">
	<?php get_template_part('template-parts/aside') ?>

	<div class="content">
		<?php get_header('white'); ?>

		<main>
			<div class="container">

				<?php get_template_part('template-parts/breadcrumbs') ?>

				<?php get_template_part('template-parts/youtube-widget'); ?>

				<?php
				$ad_banner = get_field('ad_category', 'options'); ?>

				<section class="archive-grid">
					<div class="archive-grid__wrapper wrapper">
						<h1 class="archive-grid__title">
							<?php
							$archive_title = '';

							if (is_category()) {
								$archive_title = single_cat_title('', false);
							} elseif (is_tag()) {
								$archive_title = single_tag_title('', false);
							} elseif (is_author()) {
								$archive_title = get_the_author();
							} elseif (is_year()) {
								$archive_title = get_the_date('Y');
							} elseif (is_month()) {
								$archive_title = get_the_date('F Y');
							} elseif (is_day()) {
								$archive_title = get_the_date('F j, Y');
							} elseif (is_tax()) {
								$archive_title = single_term_title('', false);
							} elseif (is_post_type_archive()) {
								$archive_title = post_type_archive_title('', false);
							} else {
								$archive_title = get_the_archive_title();
							}

							echo esc_html($archive_title);
							?>
						</h1>

						<div class="archive-grid__content">
							<?php
							$tax_query = array();

							if (is_category()) {
								$tax_query[] = array(
									'taxonomy' => 'category',
									'field' => 'id',
									'terms' => get_queried_object_id(),
								);
							} elseif (is_tag()) {
								$tax_query[] = array(
									'taxonomy' => 'post_tag',
									'field' => 'id',
									'terms' => get_queried_object_id(),
								);
							} elseif (is_tax()) {
								$tax_query[] = array(
									'taxonomy' => get_queried_object()->taxonomy,
									'field' => 'id',
									'terms' => get_queried_object_id(),
								);
							}

							$args = array(
								'posts_per_page' => 13,
								'orderby' => 'date',
								'order' => 'DESC',
								'tax_query' => $tax_query
							);

							$posts = new WP_Query($args);
							$counter = 1;

							if ($posts->have_posts()): ?>
								<div class="archive-grid__list">
									<?php while ($posts->have_posts()):
										$posts->the_post();
										$counter++; ?>

										<?php get_template_part('template-parts/card'); ?>

										<?php if ($counter == 2): ?>
											<?php if ($ad_banner):
												$ad_banner_link = isset($ad_banner['link']) ? esc_url($ad_banner['link']) : '';
												$ad_banner_img = isset($ad_banner['image']) ? esc_url($ad_banner['image']) : '';

												if ($ad_banner_link && $ad_banner_img): ?>
													<a href="<?php echo esc_url($ad_banner_link) ?>" rel="nofollow noopener"
														target="_blank" class="archive-grid__ad">
														<img src="<?php echo esc_url($ad_banner_img) ?>"
															alt="<?php echo esc_attr(get_the_title()); ?>">
													</a>
												<?php endif;
											endif; ?>
										<?php endif; ?>

										<?php if ($counter == 5): ?>
											<?php get_template_part('template-parts/bet-widget'); ?>
										<?php endif; ?>

									<?php endwhile; ?>
								</div>
							<?php else: ?>
								<div class="archive-grid__nothing">
									<?php pll_e('Пока постов нет'); ?>
								</div>
							<?php endif;
							wp_reset_postdata();
							?>
						</div>
					</div>
				</section>
			</div>
		</main>
	</div>
</div>

<?php get_footer(); ?>