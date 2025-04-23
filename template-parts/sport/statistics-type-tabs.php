<div class="statistics__links links">
    <div class="links__list">
        <!-- Футбол -->
        <a href="<?php echo esc_url(home_url('/statistics/')); ?>" class="links__item <?php echo (strpos($_SERVER['REQUEST_URI'], '/statistics/') === 0
               && strpos($_SERVER['REQUEST_URI'], '/statistics/favourites/') === false
               && strpos($_SERVER['REQUEST_URI'], '/statistics/tennis/') === false ? 'active' : ''); ?>">
            <div class="links__img">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/tab-football.png'); ?>"
                    alt="Футбол">
            </div>
            <span>Football</span>
        </a>

        <!-- Теннис -->
        <a href="<?php echo esc_url(home_url('/statistics/tennis/')); ?>"
            class="links__item <?php echo (strpos($_SERVER['REQUEST_URI'], '/statistics/tennis/') === 0 ? 'active' : ''); ?>">
            <div class="links__img">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/tab-tennis.png'); ?>"
                    alt="Tennis">
            </div>
            <span>Tennis</span>
        </a>

        <!-- Избранное -->
        <a href="<?php echo esc_url(home_url('/statistics/favourites/')); ?>"
            class="links__item <?php echo (strpos($_SERVER['REQUEST_URI'], '/statistics/favourites/') === 0 ? 'active' : ''); ?>">
            <div class="links__img">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/tab-fav.png'); ?>"
                    alt="Favorites">
            </div>
            <span>Featured</span>
        </a>
    </div>
</div>