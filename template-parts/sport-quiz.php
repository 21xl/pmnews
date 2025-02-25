<?php
$quiz_switcher = get_field('quiz_switcher', 'options');

$quiz_title = get_field('quiz_title', 'options');
$quiz_bg = get_field('quiz_bg', 'options');

$image_option = get_field('quiz_image_option', 'options');
$image_1 = get_field('quiz_image_1', 'options');
$image_2 = get_field('quiz_image_2', 'options');

$tournament = get_field('quiz_tournament', 'options');
$datetime = get_field('quiz_datetime', 'options');

$quiz_ad = get_field('quiz_ad', 'options');
$cta_text = isset($quiz_ad['quiz_cta_text']) ? $quiz_ad['quiz_cta_text'] : '';
$ref_link = isset($quiz_ad['quiz_ref_link']) ? $quiz_ad['quiz_ref_link'] : '';
$logo = isset($quiz_ad['quiz_logo']) ? $quiz_ad['quiz_logo'] : '';

$quiz_id = get_the_ID();

?>

<?php if ($quiz_switcher === 'true' || $quiz_switcher === true): ?>
    <div class="sport-quiz" id="quiz-wrapper">
        <div class="sport-quiz__container" <?php echo $quiz_bg ? "style='background:url(" . esc_url($quiz_bg) . ")'" : ''; ?>>
            <div class="sport-quiz__left">
                <h2 class="sport-quiz__title"><?php echo esc_html($quiz_title); ?></h2>
                <div class="sport-quiz__left--inner">
                    <div class="sport-quiz__icons">
                        <?php if ($image_option === 'show_image_1' || $image_option === 'show_both'): ?>
                            <?php if ($image_1): ?>
                                <div class="sport-quiz__icon">
                                    <img src="<?php echo esc_url($image_1); ?>" alt="<?php echo esc_attr(basename($image_1)); ?>">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($image_option === 'show_image_2' || $image_option === 'show_both'): ?>
                            <?php if ($image_2): ?>
                                <div class="sport-quiz__icon">
                                    <img src="<?php echo esc_url($image_2); ?>" alt="<?php echo esc_attr(basename($image_2)); ?>">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="sport-quiz__left--text">
                        <div class="sport-quiz__tournament"><?php echo esc_html($tournament); ?></div>
                        <p class="sport-quiz__time"><?php echo esc_html($datetime); ?></p>
                    </div>
                </div>
            </div>

            <?php if (have_rows('quiz_answers', 'options')): ?>
                <div class="sport-quiz__list" id="quiz-<?php echo esc_attr($quiz_id); ?>"
                    data-quiz-id="<?php echo esc_attr($quiz_id); ?>">
                    <?php $index = 0; ?>
                    <?php while (have_rows('quiz_answers', 'options')):
                        the_row(); ?>
                        <?php
                        $answer_quiz = get_sub_field('answer');
                        $answer_icon = get_sub_field('icon');
                        ?>
                        <div class="sport-quiz__item">
                            <input type="radio" name="sport-quiz__option" id="sport-quiz__radio-<?php echo esc_attr($index); ?>"
                                class="sport-quiz__option" data-choice="<?php echo esc_attr($index); ?>" />
                            <label for="sport-quiz__radio-<?php echo esc_attr($index); ?>" class="sport-quiz__answer--container">
                                <?php if ($answer_icon): ?>
                                    <img src="<?php echo esc_url($answer_icon); ?>"
                                        alt="<?php echo esc_attr(basename($answer_icon)); ?>" class="sport-quiz__answer--icon" />
                                <?php endif; ?>
                                <span class="sport-quiz__answer"><?php echo esc_html($answer_quiz); ?></span>
                            </label>
                            <span class="sport-quiz__stats"></span>
                        </div>
                        <?php $index++; ?>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

            <?php if ($cta_text && $ref_link): ?>
                <a href="<?php echo esc_url($ref_link); ?>" class="sport-quiz__cta">
                    <?php echo esc_html($cta_text); ?>
                    <?php if ($logo): ?>
                        <div class="sport-quiz__logo">
                            <img src="<?php echo esc_url($logo); ?>" alt="" />
                        </div>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>