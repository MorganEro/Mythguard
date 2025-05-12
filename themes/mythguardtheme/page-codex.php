<?php

if (!is_user_logged_in()) {
    wp_redirect(esc_url(site_url('/')));
    exit;
}

get_header();

while (have_posts()) {
    the_post();
    pageBanner();

    $id = 'create-codex-modal';
    ob_start();
    get_template_part('template-parts/content-codex');
    $content = ob_get_clean();
?>


    <div class="container container--narrow page-section">

        <div class="create-codex">
            <button class="add-codex btn btn--blue" data-modal-trigger="create-codex-modal">Add Codex</button>
            <p class="codex-count"><?php echo count_user_posts(get_current_user_id(), 'codex'); ?>/15</p>
            <?php get_template_part('template-parts/content-modal', null, ['content' => $content, 'id' => $id, 'type' => 'codex']); ?>

        </div>
        <hr class="section-break">
        <ul class="link-list min-list" id="codex">
            <?php
            $userCodex = new WP_Query(array(
                'post_type' => 'codex',
                'posts_per_page' => -1,
                'author' => get_current_user_id(),
            ));

            while ($userCodex->have_posts()) {
                $userCodex->the_post(); ?>
                <li class="codex-item" data-id="<?php echo get_the_ID(); ?>">
                    <div class="codex-title-wrapper">
                        <input class="codex-title-field" value="<?php echo str_replace('Private: ', '', esc_attr(get_the_title())) ?>" readonly>
                        <div class="codex-actions">
                            <button class="edit-codex btn btn--small btn--yellow">Update</button>
                            <button class="delete-codex btn btn--small btn--red">Delete</button>
                        </div>
                    </div>
                    <textarea class="codex-body-field" readonly><?php echo esc_textarea(wp_strip_all_tags(get_the_content())); ?></textarea>
                    <button class="update-codex btn btn--small btn--blue">Save</button>
                </li>
            <?php } ?>
        </ul>
    </div>

<?php
}

get_footer();
?>