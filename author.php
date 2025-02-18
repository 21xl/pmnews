<?php
get_template_part('head');
$author_id = get_the_author_meta('ID');
$author_first_name = get_the_author_meta('first_name', $author_id);
$author_last_name = get_the_author_meta('last_name', $author_id);
$author_position = get_field('author_position', 'user_' . $author_id);
$author_avatar = get_field('author_avatar', 'user_' . $author_id);
$author_rating = get_field('author_rating', 'user_' . $author_id);
$post_count = count_user_posts($author_id);
$author_bio = get_the_author_meta('description', $author_id);
$comment_count = get_comments(array(
    'user_id' => $author_id,
    'count' => true
));
$ad_banner = get_field('ad_category', 'options');
?>

<div class="page">
    <?php get_template_part('template-parts/aside') ?>

    <div class="content">
        <?php get_header('white'); ?>

        <main>
            <?php get_template_part('template-parts/breadcrumbs') ?>

            <section class="author__wrapper wrapper">
                <div class="author__main">
                    <?php if ($author_avatar): ?>
                        <div class="author__main-img">
                            <img src="<?php echo esc_url($author_avatar); ?>"
                                alt="<?php echo esc_html($author_first_name . ' ' . $author_last_name); ?>" loading="lazy">
                        </div>
                    <?php else: ?>
                        <div class="author__main-img author__main-img--error">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/avatar.svg') ?>"
                                alt="<?php echo esc_html($author_first_name . ' ' . $author_last_name); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="author__main-content">
                        <span class="author__main-name">
                            <?php echo esc_html($author_first_name . ' ' . $author_last_name); ?>
                        </span>

                        <?php if ($author_position): ?>
                            <span class="author__main-position">
                                <?php echo esc_html($author_position); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="author__about">
                    <?php if ($author_bio): ?>
                        <div class="author__about-wrapper">
                            <div class="author__about-item">
                                <span><?php pll_e('Об авторе/ специализация') ?></span>

                                <p><?php echo esc_html($author_bio); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="author__about-wrapper author__about-wrapper--grid">
                        <div class="author__about-item">
                            <span><?php pll_e('Количество статей') ?></span>

                            <p><?php echo esc_html($post_count); ?></p>
                        </div>

                        <div class="author__about-item">
                            <span><?php pll_e('Комментарии') ?></span>

                            <p><?php echo esc_html($comment_count); ?></p>
                        </div>

                        <div class="author__about-item">
                            <span><?php pll_e('Рейтинг') ?></span>

                            <div>
                                <?php echo esc_html($author_rating); ?>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/star.svg') ?>"
                                    alt="<?php pll_e('Рейтинг') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid-news">
                <div class="grid-news__wrapper wrapper">
                    <h2>
                        <?php pll_e('Все статьи автора'); ?>
                    </h2>

                    <div class="grid-news__content grid-news__content--all">
                        <?php
                        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                        $args = array(
                            'posts_per_page' => 13,
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'author' => $author_id,
                            'paged' => $paged,
                        );

                        $posts = new WP_Query($args);
                        $counter = 1;
                        $post_count = $posts->post_count;

                        if ($posts->have_posts()):
                            $list_class = ($post_count <= 5) ? 'archive-grid__list archive-grid__list--nocomplete' : 'archive-grid__list'; ?>
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
                                            <a href="<?php echo esc_url($ad_banner_link) ?>" rel="nofollow noopener" target="_blank"
                                                class="grid-news__ad">
                                                <img src="<?php echo esc_url($ad_banner_img) ?>"
                                                    alt="<?php echo esc_attr(get_the_title()); ?>">
                                            </a>
                                        <?php endif;
                                    endif; ?>
                                </div>
                            </div>

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

                            <?php if ($posts->max_num_pages >= 1): ?>
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

                        <?php else: ?>
                            <div class="grid-news__nothing">
                                <?php pll_e('There are no posts yet'); ?>
                            </div>
                        <?php endif;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<?php get_footer(); ?>