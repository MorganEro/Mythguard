<div class="gathering-summary <?php echo isset($args['has_border']) && $args['has_border'] ? 'gathering-summary--bordered' : ''; ?>">
    <a class="gathering-summary__date t-center" href="<?php the_permalink(); ?>">
        <span class="gathering-summary__month"><?php
                                            $eventDate = new DateTime(get_field('event_date'));
                                            echo $eventDate->format('M'); ?></span>
        <span class="gathering-summary__day">
            <?php echo $eventDate->format('d'); ?>
        </span>
    </a>
    <div class="gathering-summary__content">
        <h5 class="gathering-summary__title headline headline--tiny"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
        <p><?php if (has_excerpt()) {
                echo get_the_excerpt();
            } else {
                echo wp_trim_words(get_the_content(), 18);
            } ?> <a href="<?php the_permalink() ?>" class="nu gray">Learn more</a></p>
    </div>
</div>