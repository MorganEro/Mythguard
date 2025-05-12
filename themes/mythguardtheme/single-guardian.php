<?php
get_header();

while (have_posts()) {
    the_post();
    pageBanner();
?>

    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('guardian') ?>"><i class="fa fa-home" aria-hidden="true"></i> Guardians Home </a> <span class="metabox__main"><?php the_title(); ?></span>
            </p>
        </div>
        
        <div class="generic-content">
            <div class="row group">
                <div class="one-half">
                    <div class="guardian-image">
                        <?php 
                            $thumbnail_id = get_post_thumbnail_id();
                            $full_image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
                        ?>
                        <div class="wp-block-image size-guardianPortrait" data-wp-interactive="core/image">
                            <a href="<?php echo esc_url($full_image_url); ?>" class="guardian-image-link">
                                <?php 
                                    the_post_thumbnail('guardianPortrait', array(
                                        'class' => 'wp-image-' . $thumbnail_id
                                    ));
                                ?>
                            </a>
                            <button 
                                type="button" 
                                aria-haspopup="dialog" 
                                aria-label="Zoom in"
                                class="lightbox-trigger"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 12 12">
                                    <path fill="#fff" d="M2 0a2 2 0 0 0-2 2v2h1.5V2a.5.5 0 0 1 .5-.5h2V0H2Zm2 10.5H2a.5.5 0 0 1-.5-.5V8H0v2a2 2 0 0 0 2 2h2v-1.5ZM8 12v-1.5h2a.5.5 0 0 0 .5-.5V8H12v2a2 2 0 0 1-2 2H8Zm2-12a2 2 0 0 1 2 2v2h-1.5V2a.5.5 0 0 0-.5-.5H8V0h2Z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="one-half">
                    <?php

                    $likeCount = new WP_Query(array(
                        'post_type' => 'like',
                        'meta_query' => array(
                            array(
                                'key' => 'liked_guardian_id',
                                'compare' => '=',
                                'value' => get_the_ID(),
                            ),
                        ),
                    ));
                    $existStatus = 'no';
                    $likeId = null;
                    if (is_user_logged_in()) {
                        $existQuery = new WP_Query(array(
                            'author' => get_current_user_id(),
                            'post_type' => 'like',
                            'meta_query' => array(
                                array(
                                    'key' => 'liked_guardian_id',
                                    'compare' => '=',
                                    'value' => get_the_ID(),
                                ),
                            ),
                        ));

                        if ($existQuery->found_posts) {
                            $existStatus = 'yes';
                            $likeId = $existQuery->posts[0]->ID;
                        }
                    }
                    ?>
                    <span class="like-box" 
                        data-guardian="<?php echo get_the_ID(); ?>" 
                        data-exists="<?php echo $existStatus; ?>" 
                        <?php if ($likeId) echo 'data-like="' . $likeId . '"'; ?>>
                        <i class="fa-solid fa-heart" aria-hidden="true"></i>
                        <i class="fa-regular fa-heart" aria-hidden="true"></i>
                        <span class="like-count"><?php echo $likeCount->found_posts; ?></span>
                    </span>
                    <?php the_content(); ?>
                </div>
            </div>
        </div>

        <?php
        $relatedPrograms = get_field('related_programs');

        if ($relatedPrograms) {


            echo '<hr class="section-break">';
            echo '<h2 class="headline headline--medium">Related Programs</h2>';
            echo '<ul class="link-list min-list">';
            foreach ($relatedPrograms as $program) {  ?>

                <li><a href="<?php echo get_the_permalink($program); ?>"><?php echo get_the_title($program); ?></a></li>

        <?php
            }
            echo '</ul>';
        }
        ?>
    </div>

<?php
}

?>

<?php
get_footer();
?>