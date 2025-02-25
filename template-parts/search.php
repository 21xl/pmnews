<div id="search-modal" class="modal-search">
    <div class="modal-search__content">

        <!-- Инпут для поиска -->
        <div class="modal-search__top">
            <div class="close-search" id="close-search">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/search-black.svg'); ?>"
                    alt="Поиск">
            </div>
            <input type="text" id="search-input" placeholder="<?php echo esc_attr(pll__('Search...')); ?>">
            <button id="clear-search" class="clear-search-button"></button>
            <span id="modal-search__close" class="modal-search__close"></span>
        </div>

        <div class="modal-search__mobile">

            <!-- Недавние запросы поиска -->
            <span class="modal-search__title"><?php echo esc_html(pll__('Recent queries')); ?></span>
            <ul id="recent-queries"></ul>

            <!-- Прилоадер -->
            <div class="modal-search__loading">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/search-load.svg'); ?>"
                    alt="<?php echo esc_attr($title); ?>" loading="lazy">
            </div>

            <!-- Результаты поиска -->
            <div class="modal-search__results">
                <div class="modal-search__results-inner">
                    <span class="search-results-title"><?php echo esc_html(pll__('Search results')); ?> (<span
                            id="search-count">0</span>)</span>
                    <ul id="search-results"></ul>
                </div>
                <div class="modal-search__absolute">
                    <span class="view-text"><?php echo esc_html(pll__('Showing first search results')); ?></span>
                    <div id="view-all-results-container"></div>
                </div>
            </div>

            <!-- Сообщение, если ничего не найдено -->
            <div class="modal-search__message">
                <div class="modal-search__message--icon">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/nothing.svg'); ?>"
                        alt="<?php echo esc_attr(pll__('Unfortunately, nothing was found for your request.')); ?>">
                </div>
                <span
                    class="modal-search__message--text"><?php echo esc_html(pll__('Unfortunately, nothing was found for your request.')); ?></span>
            </div>

            <!-- Рекомендации -->
            <div class="modal-search__recommendations">
                <?php
                $args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'ignore_sticky_posts' => true,
                );

                $recommendations_query = new WP_Query($args);

                if ($recommendations_query->have_posts()): ?>
                    <div id="recommendations-section" style="display: block;">
                        <span
                            class="search-results-title"><?php echo esc_html(pll__('You might be interested in:')); ?></span>
                        <div class="recommended-posts">
                            <ul>
                                <?php while ($recommendations_query->have_posts()):
                                    $recommendations_query->the_post(); ?>
                                    <?php
                                    $post_tags = get_the_tags();
                                    $is_empty = empty($post_tags);
                                    ?>
                                    <li class="<?php echo $is_empty ? 'recommended-posts__empty' : ''; ?>">
                                        <div class="recommended-posts__categories">
                                            <?php
                                            if (!$is_empty) {

                                                $limited_tags = array_slice($post_tags, 0, 3);
                                                foreach ($limited_tags as $tag) {
                                                    echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="recommended-posts__category">' . esc_html($tag->name) . '</a> ';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <a
                                            href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>

                        </div>
                    </div>
                <?php endif;
                wp_reset_postdata(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('search-input').addEventListener('input', function (e) {
        this.value = this.value.replace(/<[^>]*>/g, '');
        this.value = this.value.replace(/<script[^>]*>([\S\s]*?)<\/script>/g, '');
    });
</script>