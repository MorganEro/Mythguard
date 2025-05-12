<div id="<?php echo esc_attr($id); ?>" class="custom-modal" aria-hidden="true">
    <div class="custom-modal__overlay" data-modal-close></div>
    <div class="custom-modal__content <?php echo isset($args['type']) ? 'custom-modal__content--' . esc_attr($args['type']) : ''; ?>" role="dialog" aria-modal="true">
        <i data-modal-close class="fa-solid fa-square-xmark custom-modal__close" aria-hidden="true"></i>
        <?php if (isset($args['content'])) echo $args['content']; ?>
    </div>
</div>