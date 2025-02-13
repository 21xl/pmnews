<div class="date-picker">
    <div class="date-picker__wrapper">
        <button class="date-picker__button date-picker__button--prev">
            <img src="<?php echo get_template_directory_uri() . '/sport/src/img/date-picker-prev.svg' ?>" alt="Prev">
        </button>

        <span class="date-picker__display" data-value="">
            <div class="date-picker__icon">
                <img src="<?php echo get_template_directory_uri() . '/sport/src/img/date-picker-icon.svg' ?>"
                    alt="Choose a date">
            </div>

            <span class="date-picker__date"></span>
        </span>

        <button class="date-picker__button date-picker__button--next">
            <img src="<?php echo get_template_directory_uri() . '/sport/src/img/date-picker-next.svg' ?>" alt="Next">
        </button>

        <ul class="date-picker__list">
            <!-- Динамически генерируемые даты -->
        </ul>
    </div>
</div>