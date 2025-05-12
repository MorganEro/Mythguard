<?php

get_header();
pageBanner(array(
    'title' => 'Search Results',
    'subtitle' => 'You searched for &ldquo;' . esc_html(get_search_query()) . '&rdquo;'
))
?>



<div class="container container--narrow page-section">
    <?php

    if (empty(get_search_query())) {
    ?>
        <h2 class="headline headline--small-plus">Please enter a search term.</h2>
    <?php
    } elseif (have_posts()) {
        while (have_posts()) {
            the_post();

            get_template_part('template-parts/content', get_post_type());
        }
        echo paginate_links();
    } else {
    ?>
        <h2 class="headline headline--small-plus">No results found</h2>
    <?php
    }

    ?>
    <hr class="section-break">
    <?php
    get_search_form();
    ?>
</div>




<?php
get_footer();

?>