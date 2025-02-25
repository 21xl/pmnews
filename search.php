<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package pm_news
 */

get_template_part('head');

$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
if (function_exists('pll_current_language')) {
	$current_lang = pll_current_language();
} else {
	$current_lang = 'en';
}

$banner = get_field('ad_single', 'option');
?>

<div class="page">
	<?php get_template_part('template-parts/aside'); ?>

	<div class="content">
		<?php get_header('white'); ?>

		<main class="result-flex">
			<section class="resluting-search">
				<?php get_template_part('template-parts/breadcrumbs'); ?>

				<div class="wrapper">
					<?php if ($search_query): ?>
						<h1 class="resluting-search__title">
							<?php pll_e('Search results'); ?>
							<?php
							$total_count = 0;

							$category_results = get_terms(array(
								'taxonomy' => 'category',
								'search' => $search_query,
								'hide_empty' => true,
							));
							$total_count += count($category_results);

							$tag_results = get_terms(array(
								'taxonomy' => 'post_tag',
								'search' => $search_query,
								'hide_empty' => true,
							));
							$total_count += count($tag_results);

							$args = array(
								'post_type' => 'page',
								's' => $search_query,
								'posts_per_page' => -1,
							);
							$search_results_pages = new WP_Query($args);
							$total_count += $search_results_pages->found_posts;
							wp_reset_postdata();

							$user_args = array(
								'search' => '*' . esc_attr($search_query) . '*',
								'search_columns' => array('display_name'),
							);
							$user_results = get_users($user_args);
							$total_count += count($user_results);

							$args = array(
								'post_type' => 'post',
								'post_status' => 'publish',
								'posts_per_page' => -1,
								's' => $search_query,
							);
							$search_results_posts = new WP_Query($args);
							$total_count += $search_results_posts->found_posts;
							wp_reset_postdata();

							echo '<span class="total-results">(' . $total_count . ')</span>';

							if ($search_query) {
								echo ' &mdash; <span class="search-query">"' . esc_html($search_query) . '"</span>';
							}
							?>
						</h1>
					<?php endif; ?>

					<div id="resluting-search__wrapper">
						<?php
						if ($search_query) {
							// Поиск по категориям
							$category_results = get_terms(array(
								'taxonomy' => 'category',
								'search' => $search_query,
								'hide_empty' => true,
							));

							if (!empty($category_results)) {
								echo '<p class="resluting-search__subtitle">' . pll__('Categories') . ' (' . count($category_results) . ')</p>
                                <div class="resluting-search__categories">';

								foreach ($category_results as $category) {
									echo '<a href="' . esc_url(get_term_link($category)) . '" class="resluting-search__category">' . esc_html($category->name) . '</a>';
								}
								echo '</div>';
							}

							// Поиск по тегам
							$tag_results = get_terms(array(
								'taxonomy' => 'post_tag',
								'search' => $search_query,
								'hide_empty' => true,
							));

							if (!empty($tag_results)) {
								echo '<p class="resluting-search__subtitle">' . pll__('Tags') . ' (' . count($tag_results) . ')</p>
                                <ul class="resluting-search__tags">';
								foreach ($tag_results as $tag) {
									echo '<li><a href="' . get_term_link($tag) . '" class="tags__item">' . esc_html($tag->name) . '</a></li>';
								}
								echo '</ul>';
							}

							// Поиск страниц
							$args = array(
								'post_type' => 'page',
								's' => $search_query,
								'posts_per_page' => -1,
							);

							$search_results_pages = new WP_Query($args);

							if ($search_results_pages->have_posts()) {
								$page_count = $search_results_pages->found_posts;
								echo '<p class="resluting-search__subtitle">' . pll__('Pages') . ' (' . $page_count . ')</p>
                                <div class="resluting-search__pages">';

								while ($search_results_pages->have_posts()) {
									$search_results_pages->the_post();
									echo '<a href="' . get_permalink() . '" class="resluting-search__page">' . get_the_title() . '</a> ';
								}
								echo '</div>';
								wp_reset_postdata();
							}

							// Поиск авторов
							$user_args = array(
								'search' => '*' . esc_attr($search_query) . '*',
								'search_columns' => array('display_name'),
							);
							$user_results = get_users($user_args);

							if (!empty($user_results)) {
								echo '<p class="resluting-search__subtitle">' . pll__('Authors') . ' (' . count($user_results) . ')</p>
                                <div class="resluting-search__people">';
								foreach ($user_results as $user) {
									$author_avatar = get_field('author_avatar', 'user_' . $user->ID);
									$author_position = get_field('author_position', 'user_' . $user->ID);

									if ($author_avatar) {
										$avatar_html = '<img src="' . esc_url($author_avatar) . '" alt="' . esc_attr($user->display_name) . '" />';
									} else {
										$avatar_html = '<img src="' . esc_url(get_template_directory_uri() . '/src/img/placeholder-daily.webp') . '" alt="' . esc_attr($user->display_name) . '" loading="lazy" />';
									}

									echo '<div class="resluting-search__people--human">';
									echo '<a href="' . get_author_posts_url($user->ID) . '" class="resluting-search__people--wrapper">';
									echo $avatar_html;
									echo '<div class="author-info">';
									echo '<p>' . esc_html($user->display_name) . '</p>';
									echo '<p class="user-role">' . esc_html($author_position ? $author_position : 'Author') . '</p>';
									echo '</div>';
									echo '</a>';
									echo '</div>';
								}

								echo '</div>';
							}

							// Поиск постов
							$args = array(
								'post_type' => 'post',
								'post_status' => 'publish',
								'posts_per_page' => -1,
								's' => $search_query,
							);

							$search_results_posts = new WP_Query($args);

							if ($search_results_posts->have_posts()) {
								echo '<p class="resluting-search__subtitle">' . pll__('News') . ' (' . $search_results_posts->found_posts . ')</p>';
								echo '<ul class="resluting-search__news">';

								$post_count = 0;
								while ($search_results_posts->have_posts()) {
									$search_results_posts->the_post();

									$post_tags = get_the_tags();
									$no_tags_class = !$post_tags ? ' no-tags' : '';

									echo '<li class="resluting-search__post' . $no_tags_class . '" style="' . ($post_count >= 12 ? 'display: none;' : '') . '">';

									// Теги
									if ($post_tags) {
										echo '<div class="resluting-search__post--tags">';
										foreach ($post_tags as $tag) {
											echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="tags__item">' . esc_html($tag->name) . '</a> ';
										}
										echo '</div>';
									}

									echo '<a href="' . get_permalink() . '" class="resluting-search__post--link">';
									echo '<div class="card__head">';
									echo '<div class="card__date">' . esc_html(get_the_date()) . '</div>';
									echo '<span class="card__separator">•</span>';
									echo '<span class="card__time">' . esc_html(get_reading_time()) . '</span>';
									echo '</div>';
									echo '<div class="resluting-search__post--name">' . get_the_title() . '</div>';
									echo '</a>';
									echo '</li>';

									$post_count++;
								}

								echo '</ul>';

								if ($post_count > 12) {
									echo '<button id="show-more-posts" style="display: flex; margin: 0 auto;">See more</button>';
								}

								wp_reset_postdata();
							}
						} else {
							echo '<p>' . pll_e('Please enter your search term.') . '</p>';
						}
						?>
					</div>
				</div>
			</section>

			<aside class="search-aside">
				<div class="search-aside__wrapper">
					<?php
					if ($banner):
						$banner_img = esc_url($banner['image']);
						$banner_link = esc_url($banner['link']);
						?>
						<a href="<?php echo $banner_link; ?>" class="single-banner"
							style="background:url('<?php echo $banner_img; ?>')" rel="nofollow noopener" target="_blank">
						</a>
					<?php endif; ?>
				</div>
			</aside>
		</main>
	</div>
</div>

<?php
get_footer(); ?>

<script defer>
	document.addEventListener('DOMContentLoaded', function () {
		const showMoreButton = document.getElementById('show-more-posts');
		if (showMoreButton) {
			showMoreButton.addEventListener('click', function () {
				const hiddenPosts = document.querySelectorAll('.resluting-search__post[style*="display: none"]');
				hiddenPosts.forEach((post, index) => {
					if (index < 12) {
						post.style.display = 'block';
					}
				});

				if (document.querySelectorAll('.resluting-search__post[style*="display: none"]').length === 0) {
					showMoreButton.style.display = 'none';
				}
			});
		}
	});
</script>