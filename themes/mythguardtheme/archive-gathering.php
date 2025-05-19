<?php

get_header();
pageBanner(array(
    'title' => 'All Gatherings',
    'subtitle' => 'See what is going on in our world',
    'photo' => get_theme_file_uri('/images/gatherings-banner.jpg.webp'),

));
?>



<div class="container container--narrow page-section">
    <?php get_template_part('template-parts/content', 'pagination', ['variant' => 'blue-grey']); ?>
    <?php
    while (have_posts()) {
        the_post();
        get_template_part('template-parts/content', 'gathering', ['has_border' => true]);
    }
    ?>

    <hr class="section-break">
    <p>Looking to see into the past? <a href="<?php echo site_url('/past-gatherings'); ?>">Take a trip to our past gatherings page</a></p>
</div>




<?php
get_footer();

?>