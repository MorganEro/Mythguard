<!-- Single Chronicle (/blog) page -->
<?php
get_header();

while (have_posts()) {
    the_post();
    pageBanner();
?>

    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <div class="metabox__row">
                <a class="metabox__blog-home-link" href="<?php echo site_url('/blog'); ?>">
                    <i class="fa fa-home" aria-hidden="true"></i>
                    Chronicles Home
                </a>
                <span class="metabox__main">Posted by <?php the_author_posts_link(); ?> on <?php the_time('n/j/y'); ?> in <?php echo get_the_category_list(', '); ?>
                </span>
            </div>
        </div>
        <div class="generic-content">
            <?php the_content(); ?>
        </div>
    </div>

<?php
}

get_footer();
?>