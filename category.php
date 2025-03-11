<?php
get_template_part('head');
$category = get_queried_object();
$custom_title = get_field('custom_title', 'category_' . $category->term_id);
?>

<div class="page">
    <?php get_template_part('template-parts/aside') ?>

    <div class="content">
        <?php get_header('white'); ?>

        <main>
            <div class="container">

                <?php get_template_part('template-parts/breadcrumbs') ?>

                <?php get_template_part('template-parts/youtube-widget'); ?>

                <?php $ad_banner = get_field('ad_category', 'options'); ?>

                <section class="archive-grid">
                    <div class="archive-grid__wrapper wrapper">
                        <h1 class="archive-grid__title">
                            <?php
                            if ($custom_title):
                                echo esc_html($custom_title);
                            else:
                                single_cat_title();
                            endif;
                            ?>
                        </h1>

                        <div class="archive-grid__content">
                            <?php
                            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                            $category_id = get_queried_object_id();
                            $args = array(
                                'posts_per_page' => 25,
                                'orderby' => 'date',
                                'order' => 'DESC',
                                'paged' => $paged,
                                'cat' => $category_id,
                            );
                            $posts = new WP_Query($args);
                            $counter = 1;
                            $post_count = $posts->post_count;

                            if ($posts->have_posts()):
                                $list_class = ($post_count <= 5) ? 'archive-grid__list archive-grid__list--nocomplete' : 'archive-grid__list';
                                ?>
                                <div class="<?php echo $list_class; ?>">
                                    <div class="archive-grid__top">
                                        <div class="archive-grid__top-list">
                                            <?php while ($posts->have_posts()):
                                                $posts->the_post(); ?>
                                                <?php if ($counter <= 4): ?>
                                                    <?php get_template_part('template-parts/card'); ?>
                                                <?php endif; ?>
                                                <?php $counter++; ?>
                                            <?php endwhile; ?>
                                        </div>

                                        <?php if ($ad_banner):
                                            $ad_banner_link = isset($ad_banner['link']) ? esc_url($ad_banner['link']) : '';
                                            $ad_banner_img = isset($ad_banner['image']) ? esc_url($ad_banner['image']) : '';

                                            if ($ad_banner_link && $ad_banner_img): ?>
                                                <a href="<?php echo esc_url($ad_banner_link) ?>" rel="nofollow noopener"
                                                    target="_blank" class="grid-news__ad">
                                                    <img src="<?php echo esc_url($ad_banner_img) ?>"
                                                        alt="<?php echo esc_attr(get_the_title()); ?>">
                                                </a>
                                            <?php endif;
                                        endif; ?>
                                    </div>

                                    <?php get_template_part('template-parts/bet-widget'); ?>

                                    <div class="archive-grid__all">
                                        <?php
                                        $counter = 1;
                                        while ($posts->have_posts()):
                                            $posts->the_post(); ?>
                                            <?php if ($counter > 4): ?>
                                                <?php get_template_part('template-parts/card'); ?>
                                            <?php endif; ?>
                                            <?php $counter++; ?>
                                        <?php endwhile; ?>
                                    </div>

                                    <?php if ($posts->have_posts() && $posts->max_num_pages > 1): ?>
                                        <div class="archive-grid__pagination">
                                            <?php $prev_link = get_previous_posts_link('<span class="pagination-prev"><svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23.6504 13.9998L16.9504 20.6998L23.6504 27.3998" stroke="#071424" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>');
                                            $next_link = get_next_posts_link('<span class="pagination-next"><svg xmlns="http://www.w3.org/2000/svg" width="41" height="41" viewBox="0 0 41 41" fill="none"> <path d="M17.3496 26.998L24.0496 20.298L17.3496 13.598" stroke="#071424" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>');

                                            if (!$prev_link) {
                                                $prev_link = '<span class="pagination-prev pagination-disabled"><svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23.6504 13.9998L16.9504 20.6998L23.6504 27.3998" stroke="#071424" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
                                            }

                                            if (!$next_link) {
                                                $next_link = '<span class="pagination-next pagination-disabled"><svg xmlns="http://www.w3.org/2000/svg" width="41" height="41" viewBox="0 0 41 41" fill="none"> <path d="M17.3496 26.998L24.0496 20.298L17.3496 13.598" stroke="#071424" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
                                            }

                                            echo $prev_link;

                                            the_posts_pagination(array(
                                                'mid_size' => 1,
                                                'screen_reader_text' => '',
                                                'prev_text' => '',
                                                'next_text' => '',
                                            ));

                                            echo $next_link; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="archive-grid__nothing">
                                <?php pll_e('There are no posts yet'); ?>
                            </div>
                        <?php endif;
                            wp_reset_postdata(); ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

<?php get_footer(); ?>