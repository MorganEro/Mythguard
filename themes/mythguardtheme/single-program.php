<?php
get_header();

while (have_posts()) {
    the_post();
    pageBanner();
?>

    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <div class="metabox__row">
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('program') ?>"><i class="fa fa-home" aria-hidden="true"></i> Programs Home </a> <span class="metabox__main"><?php the_title(); ?></span>
            </div>
        </div>
        <div class="generic-content">
            <?php the_content(); ?>
        </div>

        <?php
        $relatedGuardians = new WP_Query(array(
            'posts_per_page' => -1,
            'post_type' => 'guardian',
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'related_programs',
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"'
                )
            )

        ));

        if ($relatedGuardians->have_posts()) {

            echo '<hr class="section-break">';
            echo '<h2 class="headline headline--medium">' . get_the_title() . ' Guardians</h2>';


            echo '<ul class="guardian-cards">';
            while ($relatedGuardians->have_posts()) {
                $relatedGuardians->the_post(); ?>
                <?php get_template_part('template-parts/content', 'guardian'); ?>
        <?php
            }
            echo '</ul>';
        }


        wp_reset_postdata();

        $today = date('Y-m-d H:i:s');
        $homepageGatherings = new WP_Query(array(
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
                ),
                array(
                    'key' => 'related_programs',
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"'
                )
            )

        ));

        if ($homepageGatherings->have_posts()) {

            echo '<hr class="section-break">';
            echo '<h2 class="headline headline--medium">Upcoming ' . get_the_title() . ' Gatherings</h2>';

            while ($homepageGatherings->have_posts()) {
                $homepageGatherings->the_post();
                get_template_part('template-parts/content', 'gathering');
            }
        }
        ?>

    </div>

<?php
}

get_footer();
?>