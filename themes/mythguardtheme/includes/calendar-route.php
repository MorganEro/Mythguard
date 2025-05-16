<?php

add_action('rest_api_init', 'mythguard_register_calendar_route');

function mythguard_register_calendar_route() {
    register_rest_route('mythguard/v1', '/calendar', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'mythguard_get_calendar_dates',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ));
}

function mythguard_get_calendar_dates() {
    if (!is_user_logged_in()) {
        return new WP_Error('not_logged_in', 'You must be logged in to view calendar dates', array('status' => 401));
    }

    $dates = array(
        'contracts' => array(),
        'gatherings' => array()
    );

    // Get Contract dates with proper status and author filtering
    $user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');

    $args = array(
        'post_type' => 'contract',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'private'),
        'fields' => 'ids' // First get just IDs to verify query works
    );

    // If not admin, only show user's own contracts
    if (!$is_admin) {
        $args['author'] = $user_id;
    }

    // Save query args for debugging
    $dates['debug']['query_args'] = $args;

    // Get contract IDs first
    $contract_ids = get_posts($args);
    if (empty($contract_ids)) {
        return $dates;
    }

    // Now get full contract data
    $args['fields'] = 'all';
    $args['post__in'] = $contract_ids;
    $contracts = get_posts($args);




    foreach ($contracts as $contract) {
        // Get dates in the same way as gatherings
        $start_date = get_field('contract_start', $contract->ID);
        $end_date = get_field('contract_end', $contract->ID);



        if ($start_date && $end_date) {
            try {
                // Parse and validate the dates
                $start = DateTime::createFromFormat('Y-m-d H:i:s', $start_date);
                $end = DateTime::createFromFormat('Y-m-d H:i:s', $end_date);
                
                if ($start && $end) {


                    // Add start date
                    $dates['contracts'][] = array(
                        'date' => $start->format('Y-m-d'),
                        'title' => $contract->post_title,
                        'url' => get_permalink($contract->ID),
                        'type' => 'contract',
                        'isStart' => true
                    );

                    // Add end date
                    $dates['contracts'][] = array(
                        'date' => $end->format('Y-m-d'),
                        'title' => $contract->post_title,
                        'url' => get_permalink($contract->ID),
                        'type' => 'contract',
                        'isEnd' => true
                    );
                }
            } catch (Exception $e) {
                // Skip contracts with invalid dates
            }
        }
    }

    // Get Gathering dates
    $gatherings = get_posts(array(
        'post_type' => 'gathering',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    foreach ($gatherings as $gathering) {
        $event_date = get_field('event_date', $gathering->ID);
        if ($event_date) {
            $date = new DateTime($event_date);
            $dates['gatherings'][] = array(
                'date' => $date->format('Y-m-d'),
                'title' => $gathering->post_title,
                'url' => get_permalink($gathering->ID),
                'type' => 'gathering'
            );
        }
    }


    return rest_ensure_response($dates);
}
