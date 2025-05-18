<div class="marker__content">
    <?php if (has_post_thumbnail()) : ?>
        <div class="marker__thumbnail">
            <img src="<?php echo get_the_post_thumbnail_url(null, 'thumbnail'); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
        </div>
    <?php endif; ?>
    <h3><?php the_title(); ?></h3>
    <p><?php echo get_field('address'); ?></p>
</div>
