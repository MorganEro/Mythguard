<?php

if (!is_user_logged_in()) {
    wp_redirect(esc_url(site_url('/')));
    exit;
}

get_header();

while (have_posts()) {
    the_post();
    pageBanner();

    $is_admin = current_user_can('administrator');
    $user_id = get_current_user_id();
    $id = 'create-contract-modal';
    ob_start();
    get_template_part('template-parts/content-contract-form');
    $content = ob_get_clean();
?>


    <div class="container container--narrow page-section">
        <div class="create-contract">
            <button class="add-contract btn btn--blue" data-modal-trigger="create-contract-modal">New Contract</button>
            <p class="contract-count"></p>
            <?php get_template_part('template-parts/content-modal', null, ['content' => $content, 'id' => $id]); ?>
        </div>
        <hr class="section-break">


        <ul class="link-list min-list" id="contracts">
            <?php
            $contract_query_args = array(
                'post_type' => 'contract',
                'posts_per_page' => 10,
                'paged' => get_query_var('paged', 1),
            );
            if (!$is_admin) {
                $contract_query_args['author'] = $user_id;
            }

            $contract_query = new WP_Query($contract_query_args);

            if ($contract_query->have_posts()) :
                while ($contract_query->have_posts()) :
                    $contract_query->the_post();
                    $contract_date = get_the_date('F j, Y');
                    $contract_start = get_field('contract_start');
                    $contract_end = get_field('contract_end');

                    $related_program = get_field('related_programs');
                    $related_guardian = get_field('related_guardian');

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
            ?>
                    <li class="contract-item" data-id="<?php echo get_the_ID(); ?>">
                        <h2 class="contract-item__heading headline headline--medium">
                            <a href="<?php the_permalink(); ?>"><?php echo str_replace('Private: ', '', get_the_title()) ?></a>
                            <small class="single-contract-status <?php echo $is_active ? 'active' : 'inactive'; ?>"><?php echo $is_active ? 'Active' : 'Inactive'; ?></small>
                        </h2>
                        <div class="meta-info">
                            <div class="meta-info__row">
                                <strong>Program: </strong>
                                <span><?php 
                                    if ($program && $program_name !== 'N/A') {
                                        echo '<a href="' . esc_url(get_permalink($program)) . '">' . $program_name . '</a>';
                                    } else {
                                        echo $program_name;
                                    }
                                ?></span>
                            </div>
                            <div class="meta-info__row">
                                <strong>Guardian: </strong>
                                <span><?php 
                                    if ($guardian && $guardian_name !== 'N/A') {
                                        echo '<a href="' . esc_url(get_permalink($guardian)) . '">' . $guardian_name . '</a>';
                                    } else {
                                        echo $guardian_name;
                                    }
                                ?></span>
                            </div>
                            <small class="contract-date">Created on <?php echo $contract_date; ?></small>
                        </div>
                    </li>
                <?php
                endwhile;
                echo paginate_links(array(
                    'total' => $contract_query->max_num_pages,
                ));
            else :
                ?>
                <h2>No contracts found.</h2>
            <?php endif;

            wp_reset_postdata();
            ?>
        </ul>
    </div>

<?php
}

get_footer();
?>