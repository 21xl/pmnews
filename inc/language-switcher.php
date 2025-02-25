<?php
function custom_language_switcher_shortcode()
{
    ob_start();
    if (function_exists('pll_the_languages')) {
        $args = array(
            'dropdown' => 0,
            'show_flags' => 0,
            'echo' => 0,
            'raw' => 1
        );
        $languages = pll_the_languages($args);

        if (!empty($languages)): ?>
            <ul class="language-switcher">
                <?php foreach ($languages as $language):
                    $class = $language['current_lang'] ? 'language-switcher__item language-switcher__item--current' : 'language-switcher__item'; ?>
                    <li class="<?php echo esc_attr($class); ?>">
                        <a href="<?php echo esc_url($language['url']); ?>">
                            <?php echo esc_html($language['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif;
    }
    return ob_get_clean();
}

add_shortcode('language_switcher', 'custom_language_switcher_shortcode');