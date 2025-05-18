<div class="tooltip__content">
    <?php if (has_post_thumbnail()) : ?>
        <div class="location-tooltip__thumbnail">
            <img src="<?php echo get_the_post_thumbnail_url(null, 'thumbnail'); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
        </div>
    <?php endif; ?>
    <div class="location-tooltip__content">
        <strong><?php the_title(); ?></strong>
        <?php echo get_field('address'); ?>
    </div>
</div>
