<?php
get_header();
pageBanner(array(
    'title' => 'All Guardians',
    'subtitle' => 'Meet our Guardians',
    'photo' => get_theme_file_uri('/images/archive-banner.png.webp'),
));
?>

<div class="container container--narrow page-section">
    <?php get_template_part('template-parts/content', 'pagination'); ?>
    <div class="guardian-archive">
        <?php
        while (have_posts()) {
            the_post();
            $bannerSubtitle = get_field('page_banner_subtitle');
        ?>
            <div>

                <div class="guardian-archive__card">
                    <?php if ($bannerSubtitle) { ?>
                        <h3 class="guardian-archive__subtitle c-orange"><?php echo $bannerSubtitle; ?></h3>
                    <?php } ?>
                    <div class="guardian-archive__image-container">
                        <a href="<?php the_permalink(); ?>">
                            <?php echo wp_get_attachment_image(get_post_thumbnail_id(), 'medium_large', false, array(
                                'class' => 'guardian-archive__image',
                                'alt' => get_the_title()
                            )); ?>
                        </a>
                        <h2 class="guardian-archive__title">
                            <?php the_title(); ?>
                        </h2>
                    </div>


                </div>
            </div>
        <?php
        }
        ?>

    </div>

</div>

<?php
get_footer();

?>