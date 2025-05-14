<?php

get_header();
pageBanner(array(
    'title' => 'All Events',
    'subtitle' => 'See what is going on in our world',
    'photo' => get_theme_file_uri('/images/events-banner.jpg'),

));
?>



<div class="container container--narrow page-section">
    <?php get_template_part('template-parts/content', 'pagination', ['variant' => 'blue-grey']); ?>
    <?php
    while (have_posts()) {
        the_post();
        get_template_part('template-parts/content', 'event', ['has_border' => true]);
    }
    ?>

    <hr class="section-break">
    <p>Looking to see into the past? <a href="<?php echo site_url('/past-events'); ?>">Take a trip to our past events page</a></p>
</div>




<?php
get_footer();

?>