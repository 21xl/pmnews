<?php
$content = get_the_content();
if (!empty($content)): ?>
    <section class="editor-content">
        <div class="editor-content__wrapper wrapper">
            <?php the_content() ?>
        </div>
    </section>
<?php endif; ?>