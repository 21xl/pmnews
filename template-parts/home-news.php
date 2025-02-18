<?php
$category = get_sub_field('category');
$category_obj = get_category($category);

if ($category_obj) {
    $category_name = $category_obj->name;
    $category_link = get_category_link($category_obj->term_id);
}

$count = get_sub_field('count');
$ad = get_sub_field('ad');
$ad_banner = get_field('ad_category', 'options');
?>

<section class="grid-news">
    <div class="grid-news__wrapper wrapper">
        <?php if (!empty($category_name)): ?>
                <h2 class="grid-news__title">
                    <?php echo esc_html($category_name); ?>
                </h2>
        <?php endif; ?>

        <div class="grid-news__content">
            <?php
            $args = array(
                'cat' => $category,
                'posts_per_page' => $count,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            $category_posts = new WP_Query($args);

            if ($category_posts->have_posts()): ?>
                    <div
                        class="grid-news__list grid-news__list--<?php echo esc_attr($count); ?><?php echo $ad ? ' grid-news__list--ad' : ''; ?>">
                        <?php while ($category_posts->have_posts()):
                            $category_posts->the_post(); ?>
                                <?php get_template_part('template-parts/card'); ?>
                        <?php endwhile; ?>
                    </div>
            <?php else: ?>
                    <div class="grid-news__nothing">
                        <?php pll_e('There are no posts yet'); ?>
                    </div>
            <?php endif;
            wp_reset_postdata();
            ?>

            <?php if ($ad && $ad_banner):
                $ad_banner_link = isset($ad_banner['link']) ? esc_url($ad_banner['link']) : '';
                $ad_banner_img = isset($ad_banner['image']) ? esc_url($ad_banner['image']) : '';

                if ($ad_banner_link && $ad_banner_img): ?>
                            <a href="<?php echo esc_url($ad_banner_link); ?>" rel="nofollow noopener" target="_blank"
                                class="grid-news__ad">
                                <img src="<?php echo esc_url($ad_banner_img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                            </a>
                    <?php endif;
            endif; ?>
        </div>

        <?php if ($category_posts->have_posts() && !empty($category_link)): ?>
                <a href="<?php echo esc_url($category_link); ?>" class="link grid-news__link">
                    <?php pll_e('See more'); ?>
                </a>
        <?php endif; ?>
    </div>
</section>