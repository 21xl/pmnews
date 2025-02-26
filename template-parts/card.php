<div class="card">
    <a href="<?php the_permalink(); ?>" class="card__link">
        <?php if (get_the_post_thumbnail_url()): ?>
            <div class="card__img">
                <?php
                $img_id = get_post_thumbnail_id();
                $img_url = get_the_post_thumbnail_url(null, 'medium_large');
                $img_srcset = wp_get_attachment_image_srcset($img_id, 'full');
                $img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: get_the_title();
                ?>
                <img src="<?php echo esc_url($img_url); ?>" srcset="<?php echo esc_attr($img_srcset); ?>"
                    sizes="(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 33vw" alt="<?php echo esc_attr($img_alt); ?>"
                    loading="lazy">
            </div>
        <?php else: ?>
            <div class="card__img card__img--error">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/placeholder-card.webp'); ?>"
                    alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
            </div>
        <?php endif; ?>

        <div class="card__content">
            <div class="card__head">
                <div class="card__date">
                    <?php echo esc_html(get_the_date('d.m.Y')); ?>
                </div>

                <span class="card__separator">•</span>

                <span class="card__time">
                    <?php echo esc_html(get_reading_time()); ?>
                </span>
            </div>

            <span class="card__title">
                <?php echo esc_html(get_the_title()); ?>
            </span>

            <div class="card__desc">
                <p><?php echo esc_html(get_the_excerpt()); ?></p>
            </div>
        </div>
    </a>

    <?php
    $post_tags = get_the_tags();
    if ($post_tags):
        shuffle($post_tags);
        $random_tags = array_slice($post_tags, 0, 3);
        ?>
        <div class="tags">
            <?php foreach ($random_tags as $tag): ?>
                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tags__item">
                    <?php echo esc_html($tag->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>