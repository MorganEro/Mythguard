<?php
get_header();

while (have_posts()) {
    the_post();
    pageBanner();
?>

    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('location'); ?>">
                    <i class="fa fa-home" aria-hidden="true"></i> All Locations
                </a>
                <span class="metabox__main"><?php the_title(); ?></span>
            </p>
        </div>

        <div class="generic-content">
            <?php the_content(); ?>
        </div>
        <ul class="other-locations">
            
            <?php
            $args = array(
                'post_type' => 'location',
                'posts_per_page' => -1,
                'post__not_in' => array(get_the_ID()),
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
            ?>
                    <li class="other-locations__item">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </li>
            <?php
                }
            }
            wp_reset_postdata();
            ?>
        </ul>

        <!-- Map Container -->
        <div class="acf-map">
            <div class="marker"
                data-lat="<?php echo get_field('coordinates')['latitude']; ?>"
                data-lng="<?php echo get_field('coordinates')['longitude']; ?>"
                data-type="<?php echo get_field('location_type'); ?>">
                <?php get_template_part('template-parts/content', 'tooltip'); ?>
                <?php get_template_part('template-parts/content', 'map-popup'); ?>
            </div>
        </div>

        <?php
        $relatedGuardians = get_field('related_guardian');
        if ($relatedGuardians) {
            echo '<hr class="section-break">';
            echo '<h2 class="headline headline--small">Some guardians stationed at ' . get_the_title() . '</h2>';


            echo '<ul class="guardian-cards">';
            foreach ($relatedGuardians as $guardian) { ?>
                <li class="guardian-card__list-item">
                    <a class="guardian-card" href="<?php echo get_permalink($guardian); ?>">
                        <img class="guardian-card__image" src="<?php echo get_the_post_thumbnail_url($guardian, 'guardianLandscape') ?>">
                        <span class="guardian-card__name"><?php echo get_the_title($guardian); ?></span>
                    </a>
                </li>
        <?php }
            echo '</ul>';
        }

        ?>

    </div>

<?php
}

get_footer();
?>