<?php
if (!is_user_logged_in()) {
    wp_redirect(esc_url(site_url('/')));
    exit;
}

get_header();



while (have_posts()) {
    the_post();
    $current_user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');
    $is_owner = $current_user_id == get_post_field('post_author', get_the_ID());
    $can_edit = $is_admin || $is_owner;

    // Get contract data
    $related_program = get_field('related_programs');
    $related_guardian = get_field('related_guardian');
    $contract_start = get_field('contract_start');
    $contract_end = get_field('contract_end');

    $program_name = 'N/A';
    if ($related_program && !empty($related_program)) {
        $program = is_array($related_program) ? reset($related_program) : $related_program;
        $program_name = esc_html(get_the_title($program));
    }

    $guardian_name = 'N/A';
    if ($related_guardian && !empty($related_guardian)) {
        $guardian = is_array($related_guardian) ? reset($related_guardian) : $related_guardian;
        $guardian_name = esc_html(get_the_title($guardian));
    }

    $is_active = false;
    $now = date('m/d/Y g:i a');
    if ($contract_start && $contract_end) {
        $start_time = strtotime($contract_start);
        $end_time = strtotime($contract_end);
        $current_time = strtotime($now);
        $is_active = ($current_time >= $start_time && $current_time <= $end_time);
    }

    pageBanner();
?>

    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo site_url('/contracts') ?>"><i class="fa fa-home" aria-hidden="true"></i> Contracts Home </a> <span class="metabox__main"><?php echo mg_clean_private_title(get_the_title()); ?></span>
            </p>
        </div>

        <div class="single-contract-item" data-id="<?php echo get_the_ID(); ?>">
            <div class="single-contract-title-wrapper">
                <input type="text" class="single-contract-title-field" name="title" value="<?php echo esc_attr(mg_clean_private_title(get_the_title())); ?>" readonly>
                <span class="single-contract-status <?php echo $is_active ? 'active' : 'inactive'; ?>">
                    <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                </span>
            </div>

            <div class="single-contract-details">
                <div class="single-contract-details__row">
                    <strong>Program:</strong>
                    <select class="single-contract-program-field" name="program" disabled>
                        <option value="<?php echo $program ? esc_attr($program->ID) : ''; ?>"><?php echo $program_name; ?></option>
                    </select>
                </div>

                <div class="single-contract-details__row">
                    <strong>Guardian:</strong>
                    <select class="single-contract-guardian-field" name="guardian" disabled>
                        <option value="<?php echo $guardian ? esc_attr($guardian->ID) : ''; ?>"><?php echo $guardian_name; ?></option>
                    </select>
                </div>

                <div class="single-contract-details__row">
                    <strong>Contract Starts:</strong>
                    <input type="text" class="single-contract-start-date" name="start_date" value="<?php echo date('m/d/Y h:i A', strtotime($contract_start)); ?>" readonly>
                </div>

                <div class="single-contract-details__row">
                    <strong>Contract Ends:</strong>
                    <input type="text" class="single-contract-end-date" name="end_date" value="<?php echo date('m/d/Y h:i A', strtotime($contract_end)); ?>" readonly>
                </div>

                <div class="single-contract-details__row">
                    <strong>Description:</strong>
                    <textarea class="single-contract-description-field" name="description" readonly><?php echo esc_textarea(wp_strip_all_tags(get_the_content())); ?></textarea>
                </div>

                <?php
    // Check if contract is expired
    $current_date = current_time('mysql', true);
    $is_expired = $contract_end && strtotime($contract_end) < strtotime($current_date);
    
    // Check if we should automatically enter edit mode
    $auto_edit = isset($_GET['edit']) && $_GET['edit'] === 'true' && !$is_expired;
    
    if ($can_edit) : ?>
                    <div class="single-contract-actions">
                        <?php if (!$is_expired) : ?>
                            <button data-action="edit-contract" class="btn btn--blue btn--small">Edit</button>
                            <button data-action="update-contract" class="btn btn--blue btn--small" style="display: none;">Update</button>
                        <?php endif; ?>
                        <button data-action="delete-contract" class="btn btn--red btn--small">Delete</button>
                    </div>
                <?php endif; ?>

            </div>


        </div>
    </div>

<?php
}

get_footer();
?>