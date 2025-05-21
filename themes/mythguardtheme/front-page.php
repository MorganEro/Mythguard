<?php get_header(); ?>

<div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo get_theme_file_uri('/images/front-page.png.webp') ?>)"></div>
    <div class="page-banner__content container t-center c-white">
        <h1 class="headline headline--large">Welcome to MythGuard!</h1>
        <h2 class="headline headline--medium">Your protection is our specialty.</h2>
        <h3 class="headline headline--small">Why not explore the <strong>program</strong> that fits your needs?</h3>
        <a href="<?php echo get_post_type_archive_link('program'); ?>" class="btn btn--large btn--blue">Find Your Guardian Program</a>
    </div>
</div>

<div class="full-width-split ">
    <div class="full-width-split__one">
        <div class="full-width-split__inner">
            <h2 class="headline headline--small-plus t-center">Upcoming Gatherings</h2>

            <?php
            $today = date('Y-m-d H:i:s');
            $homepageEvents = new WP_Query(array(
                'posts_per_page' => 2,
                'post_type' => 'gathering',
                'orderby' => 'meta_value',
                'meta_key' => 'event_date',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'event_date',
                        'compare' => '>=',
                        'value' => $today,
                        'type' => 'datetime'
                    )
                )

            ));

            while ($homepageEvents->have_posts()) {
                $homepageEvents->the_post();
                get_template_part('template-parts/content', 'gathering');
            }
            ?>


            <p class="t-center no-margin"><a href="<?php echo get_post_type_archive_link('gathering') ?>" class="btn btn--blue">View All Gatherings</a></p>
        </div>
    </div>
    <div class="full-width-split__two">
        <div class="full-width-split__inner">
            <h2 class="headline headline--small-plus t-center">From the Chronicles</h2>

            <?php
            $homepagePosts = new WP_Query(array(
                'posts_per_page' => 2,
            ));

            while ($homepagePosts->have_posts()) {
                $homepagePosts->the_post(); ?>
                <div class="gathering-summary">
                    <a class="gathering-summary__date gathering-summary__date--beige t-center" href="<?php the_permalink(); ?>">
                        <span class="gathering-summary__month"><?php the_time('M') ?></span>
                        <span class="gathering-summary__day"><?php the_time('d') ?></span>
                    </a>
                    <div class="gathering-summary__content">
                        <h5 class="gathering-summary__title headline headline--tiny"><a href="<?php the_permalink
                        (); ?>"><?php the_title(); ?></a></h5>
                        <div>
                        <p><?php if (has_excerpt()) {
                                echo get_the_excerpt();
                            } else {
                                echo wp_trim_words(get_the_content(), 18);
                            } ?> <a href="<?php the_permalink(); ?>" class="nu gray">Read more</a></p>
                        </div>
                    </div>
                </div>
            <?php }
            wp_reset_postdata(); ?>


            <p class="t-center no-margin"><a href="<?php echo site_url('/blog'); ?>" class="btn btn--yellow">View All Chronicles</a></p>
        </div>
    </div>
</div>

<div class="hero-slider splide">
    <div class="splide__track">
        <div class="splide__list">
            <?php 
            $heroSliderPosts = new WP_Query(array(
                'posts_per_page' => 3,
                'post_type' => 'post',
                'meta_query' => array(
                    array(
                        'key' => '_thumbnail_id',
                        'compare' => 'EXISTS'
                    )
                )
            ));

            while($heroSliderPosts->have_posts()) {
                $heroSliderPosts->the_post();
                $featuredImage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                ?>
                <div class="splide__slide hero-slider__slide" style="background-image: url(<?php echo $featuredImage[0] ?>)">
                    <div class="hero-slider__interior container">
                        <div class="hero-slider__overlay">
                            <h2 class="headline headline--medium t-center"><?php the_title(); ?></h2>
                            <p class="t-center"><?php 
                                if (has_excerpt()) {
                                    echo get_the_excerpt();
                                } else {
                                    echo wp_trim_words(get_the_content(), 18);
                                } 
                            ?></p>
                            <p class="t-center no-margin">
                                <a href="<?php the_permalink(); ?>" class="btn btn--blue">Read More</a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php }
            wp_reset_postdata();
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>