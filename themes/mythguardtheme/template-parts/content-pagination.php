<?php
/**
 * Template part for displaying pagination
 */
?>

<?php
$variant = isset($args['variant']) ? $args['variant'] : '';
$pagination_class = 'pagination' . ($variant ? ' pagination--' . $variant : '');
?>

<div class="pagination-wrapper">
    <nav class="<?php echo esc_attr($pagination_class); ?>">

        <?php
        echo paginate_links([
            'prev_text' => '<i class="fa-solid fa-caret-left"></i> PREV',
            'next_text' => 'NEXT <i class="fa-solid fa-caret-right"></i>',
            'before_page_number' => '<span>',
            'after_page_number'  => '</span>',
        ]);
        ?>
    </nav>
</div>