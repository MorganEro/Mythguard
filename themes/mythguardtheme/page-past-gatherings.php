<?php

get_header();
pageBanner(array(
    'title' => 'All Past Gatherings',
    'subtitle' => 'Travel to the past'
))
?>



<div class="container container--narrow page-section">
    <?php

    $today = date('Y-m-d H:i:s');
    $pastGatherings = new WP_Query(array(
        'posts_per_page' => -1, // Show all posts
        'paged' => get_query_var('paged', 1),
        'post_type' => 'gathering',
        'meta_key' => 'event_date',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => 'event_date',
                'compare' => '<',
                'value' => $today,
                'type' => 'datetime'
            )
        )
    ));

    if ($pastGatherings->have_posts()) {
        while ($pastGatherings->have_posts()) {
            $pastGatherings->the_post();
            get_template_part('template-parts/content', 'gathering', ['has_border' => true]);
        }
        get_template_part('template-parts/content', 'pagination', [
            'variant' => 'blue-grey',
            'max_num_pages' => $pastGatherings->max_num_pages,
            'current_page' => get_query_var('paged', 1)
        ]);
    } else {
        echo '<h2 class="t-center">There are no past gatherings yet. Check back later!</h2>';
    }
    ?>
</div>




<?php
get_footer();

?>