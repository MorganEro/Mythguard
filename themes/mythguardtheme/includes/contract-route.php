<?php
add_action('rest_api_init', 'mythguard_register_contract_routes');

function mythguard_register_contract_routes()
{
    // Common arguments for routes
    $id_arg = array(
        'required' => true,
        'validate_callback' => function ($param) {
            return is_numeric($param);
        }
    );

    // Register GET route for all contracts
    register_rest_route('mythguard/v1', 'contracts', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'mythguard_get_contracts',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));

    // Register GET route for single contract
    register_rest_route('mythguard/v1', 'contracts/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'mythguard_get_contract',
        'permission_callback' => function ($request) {
            return is_user_logged_in();
        },
        'args' => array(
            'id' => $id_arg
        )
    ));

    // Register CREATE route
    register_rest_route('mythguard/v1', 'contracts', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'mythguard_add_contract',
        'permission_callback' => function () {
            return current_user_can('publish_contracts');
        },
        'args' => array(
            'title' => array(
                'required' => true,
                'type' => 'string'
            ),
            'content' => array(
                'required' => true,
                'type' => 'string'
            )
        )
    ));

    // Register UPDATE route
    register_rest_route('mythguard/v1', 'contracts/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'mythguard_update_contract',
        'permission_callback' => function ($request) {
            $contract = get_post($request['id']);
            return current_user_can('edit_contract', $request['id']) && get_current_user_id() == $contract->post_author;
        },
        'args' => array(
            'id' => $id_arg,
            'title' => array(
                'required' => false,
                'type' => 'string'
            ),
            'content' => array(
                'required' => false,
                'type' => 'string'
            )
        )
    ));

    // Register DELETE route
    register_rest_route('mythguard/v1', 'contracts/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'mythguard_delete_contract',
        'permission_callback' => function ($request) {
            $contract = get_post($request['id']);
            return current_user_can('administrator') || 
                   (current_user_can('delete_contract', $request['id']) && get_current_user_id() == $contract->post_author);
        },
        'args' => array(
            'id' => $id_arg
        )
    ));

    // Register contract count endpoint
    register_rest_route('mythguard/v1', 'contract-count', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'mythguard_get_contract_count',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
}

function mythguard_get_contracts()
{
    $user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');

    // Query parameters
    $args = array(
        'post_type' => 'contract',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    // If not admin, only show user's own contracts
    if (!$is_admin) {
        $args['author'] = $user_id;
    }

    // Add isAdmin to response
    add_filter('rest_prepare_contract', function($response, $post, $request) use ($is_admin) {
        $response->data['isAdmin'] = $is_admin;
        return $response;
    }, 10, 3);

    $contracts = get_posts($args);
    $formatted_contracts = array();

    foreach ($contracts as $contract) {
        $formatted_contracts[] = array(
            'id' => $contract->ID,
            'title' => $contract->post_title,
            'date' => $contract->post_date,
            'userContractCount' => count_user_posts($user_id, 'contract'),
            'isAdmin' => $is_admin
        );
    }

    return new WP_REST_Response($formatted_contracts, 200);
}

function mythguard_get_contract_count() {
    $user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');
    
    // Get user's contract count
    $args = array(
        'post_type' => 'contract',
        'author' => $user_id,
        'post_status' => 'private',
        'posts_per_page' => -1,
        'fields' => 'ids'
    );
    $user_query = new WP_Query($args);
    
    $response = array(
        'userCount' => $user_query->found_posts,
        'isAdmin' => $is_admin
    );

    if ($is_admin) {
        // Get total contract count
        $args = array(
            'post_type' => 'contract',
            'post_status' => 'private',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        $total_query = new WP_Query($args);
        $response['totalCount'] = $total_query->found_posts;
    }
    
    return new WP_REST_Response($response, 200);
}

function mythguard_get_contract($request)
{
    $contract_id = $request['id'];
    $contract = get_post($contract_id);
    $user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');

    if (!$contract || $contract->post_type !== 'contract') {
        return new WP_Error('no_contract', 'Contract not found', array('status' => 404));
    }

    // Check if user has access to this contract
    if (!$is_admin && $contract->post_author != $user_id) {
        return new WP_Error('forbidden', 'You do not have permission to view this contract', array('status' => 403));
    }

    $response = array(
        'id' => $contract->ID,
        'title' => $contract->post_title,
        'content' => $contract->post_content,
        'date' => $contract->post_date
    );

    return new WP_REST_Response($response, 200);
}

function mythguard_add_contract($request)
{
    $contract = array(
        'post_type' => 'contract',
        'post_status' => 'publish',
        'post_title' => sanitize_text_field($request['title']),
        'post_content' => wp_kses_post($request['content'])
    );

    $contract_id = wp_insert_post($contract);

    if (is_wp_error($contract_id)) {
        return new WP_Error(
            'failed',
            'Failed to create contract',
            array('status' => 500)
        );
    }

    // Save ACF fields
    if (isset($request['meta'])) {
        if (isset($request['meta']['related_program'])) {
            update_field('related_programs', $request['meta']['related_program'], $contract_id);
        }
        if (isset($request['meta']['related_guardian'])) {
            update_field('related_guardian', $request['meta']['related_guardian'], $contract_id);
        }
        if (isset($request['meta']['contract_start'])) {
            update_field('contract_start', $request['meta']['contract_start'], $contract_id);
        }
        if (isset($request['meta']['contract_end'])) {
            update_field('contract_end', $request['meta']['contract_end'], $contract_id);
        }
    }

    return new WP_REST_Response(array(
        'id' => $contract_id,
        'message' => 'Contract created successfully'
    ), 201);
}

function mythguard_update_contract($request)
{
    $contract_id = $request['id'];
    $contract = get_post($contract_id);
    $user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');

    if (!$contract || $contract->post_type !== 'contract') {
        return new WP_Error(
            'no_contract',
            'Contract not found',
            array('status' => 404)
        );
    }

    // Check if user has access to this contract
    if (!$is_admin && $contract->post_author != $user_id) {
        return new WP_Error(
            'contract_update_forbidden',
            'You do not have permission to modify this contract',
            array('status' => 403)
        );
    }

    $updates = array(
        'ID' => $contract_id
    );

    // Only admin can update title and content
    if ($is_admin) {
        if (isset($request['title'])) {
            $updates['post_title'] = sanitize_text_field($request['title']);
        }
        if (isset($request['content'])) {
            $updates['post_content'] = wp_kses_post($request['content']);
        }
    }


    // Update the post if there are title or content changes
    if (isset($updates['post_title']) || isset($updates['post_content'])) {
        $result = wp_update_post($updates);
        if (is_wp_error($result)) {
            return new WP_Error(
                'failed',
                'Failed to update contract',
                array('status' => 500)
            );
        }
    }

    // Update meta fields
    if (isset($request['meta'])) {
        // Handle dates - convert from ISO format if needed
        if (isset($request['meta']['contract_start'])) {
            $start_date = $request['meta']['contract_start'];
            if (strpos($start_date, 'T') !== false) { // ISO format
                $start_date = date('Y-m-d H:i:s', strtotime($start_date));
            }
            update_field('contract_start', $start_date, $contract_id);
        }
        if (isset($request['meta']['contract_end'])) {
            $end_date = $request['meta']['contract_end'];
            if (strpos($end_date, 'T') !== false) { // ISO format
                $end_date = date('Y-m-d H:i:s', strtotime($end_date));
            }
            update_field('contract_end', $end_date, $contract_id);
        }
        
        // Handle program - frontend sends 'related_program', we store as 'related_programs'
        if (isset($request['meta']['related_program'])) {
            update_field('related_programs', $request['meta']['related_program'], $contract_id);
        }
        
        // Handle guardian
        if (isset($request['meta']['related_guardian'])) {
            update_field('related_guardian', $request['meta']['related_guardian'], $contract_id);
        }
    }

    // Get updated meta values
    $updated_meta = array(
        'contract_start' => get_field('contract_start', $contract_id),
        'contract_end' => get_field('contract_end', $contract_id),
        'related_programs' => get_field('related_programs', $contract_id),
        'related_guardian' => get_field('related_guardian', $contract_id)
    );

    return new WP_REST_Response(array(
        'updated' => true,
        'message' => 'Contract updated successfully',
        'contract' => array(
            'id' => $contract_id,
            'title' => $updates['post_title'] ?? $contract->post_title,
            'content' => $updates['post_content'] ?? $contract->post_content,
            'meta' => $updated_meta
        )
    ), 200);
}

function mythguard_delete_contract($request)
{
    $contract_id = $request['id'];
    $contract = get_post($contract_id);

    if (!$contract || $contract->post_type !== 'contract') {
        return new WP_Error(
            'no_contract',
            'Contract not found',
            array('status' => 404)
        );
    }

    $result = wp_delete_post($contract_id, true);

    if (!$result) {
        return new WP_Error(
            'failed',
            'Failed to delete contract',
            array('status' => 500)
        );
    }

    return new WP_REST_Response(array(
        'deleted' => true,
        'message' => 'Contract deleted successfully'
    ), 200);
}
