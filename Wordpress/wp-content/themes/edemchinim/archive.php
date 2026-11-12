<?php
get_header("new");
?>
<main id="primary" class="development-page landing">
    <section class="development-page__content landing-section" aria-labelledby="development-page-title">
        <h1 class="development-page__title landing-title" id="development-page-title">Страница в разработке</h1>
        <div class="development-page__text landing-text">
            <?php if (trim(get_the_content()) !== '') : ?>
                <?php the_content(); ?>
            <?php else : ?>
                <p>Мы готовим эту страницу. Пока вы можете перейти на главную.</p>
            <?php endif; ?>
        </div>
        <a class="development-page__cta landing-cta" href="<?php echo esc_url(home_url('/')); ?>">
            <span>На главную</span>
            <svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M5 16h21m-8-8 8 8-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="square"/></svg>
        </a>
    </section>
</main>
<?php get_footer("new"); ?>
