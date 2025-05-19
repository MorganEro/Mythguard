<?php
get_header();
pageBanner(array(
    'title' => 'Our Locations',
    'subtitle' => 'Find a MythGuard location near you.',
    'photo' => get_theme_file_uri('/images/archive-locations.jpg.webp'),

));
?>

<div class="container container--narrow page-section">

    <p class="headline headline--tiny-reg">MythGuard locations are expertly camouflaged to blend into the fabric of everyday life—appearing as parks, storefronts, transit hubs, or forgotten alleyways. Invisible to the uninitiated, they reveal themselves only to those who have been granted the Sight.</p>

    <!-- Map Container -->
    <div class="acf-map">
        <?php
        while (have_posts()) {
            the_post();
            $coordinates = get_field('coordinates');
            if ($coordinates) {
        ?>
                <div class="marker"
                    data-lat="<?php echo $coordinates['latitude']; ?>"
                    data-lng="<?php echo $coordinates['longitude']; ?>"
                    data-type="<?php echo get_field('location_type'); ?>">
                    <?php get_template_part('template-parts/content', 'tooltip'); ?>
                    <?php get_template_part('template-parts/content', 'map-popup'); ?>
                </div>
        <?php
            }
        }
        ?>
    </div>
    <!-- Locations List -->
    <ul class="link-list min-list">
        <?php
        while (have_posts()) {
            the_post();
        ?>
            <li class="generic-content">
                <h5>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h5>
                <p><?php echo get_field('address'); ?></p>
            </li>
        <?php }
        rewind_posts(); // Reset the post query for the map
        ?>
    </ul>
</div>


<?php get_footer(); ?>