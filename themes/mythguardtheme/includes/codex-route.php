<?php
add_action('rest_api_init', 'mythguard_register_codex_routes');

function mythguard_register_codex_routes()
{
    // Common arguments for routes
    $id_arg = array(
        'required' => true,
        'validate_callback' => function ($param) {
            return is_numeric($param);
        }
    );

    // Register CREATE route
    register_rest_route('mythguard/v1', 'codex', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'mythguard_add_codex',
        'permission_callback' => function () {
            return current_user_can('publish_codexs');
        },
        'args' => array(
            'title' => array(
                'required' => true,
                'type' => 'string'
            ),
            'body' => array(
                'required' => true,
                'type' => 'string'
            )
        )
    ));

    // Register UPDATE route
    register_rest_route('mythguard/v1', 'codex/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'mythguard_update_codex',
        'permission_callback' => function ($request) {
            $codex = get_post($request['id']);
            return current_user_can('edit_codex', $request['id']) && get_current_user_id() == $codex->post_author;
        },
        'args' => array(
            'id' => $id_arg,
            'title' => array(
                'required' => true,
                'type' => 'string'
            ),
            'body' => array(
                'required' => true,
                'type' => 'string'
            )
        )
    ));

    // Register DELETE route
    register_rest_route('mythguard/v1', 'codex/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'mythguard_delete_codex',
        'permission_callback' => function ($request) {
            $codex = get_post($request['id']);
            return current_user_can('delete_codex', $request['id']) && get_current_user_id() == $codex->post_author;
        },
        'args' => array(
            'id' => $id_arg
        )
    ));
}

function mythguard_add_codex($request)
{
    $title = sanitize_text_field($request['title']);
    $body = sanitize_textarea_field($request['body']);

    $codex = wp_insert_post(array(
        'post_type' => 'codex',
        'post_status' => 'publish',
        'post_title' => $title,
        'post_content' => $body,
        'post_author' => get_current_user_id()
    ));

    if (is_wp_error($codex)) {
        return new WP_Error(
            'codex_creation_failed',
            'Failed to create codex entry',
            array('status' => 500)
        );
    }

    return array(
        'id' => $codex,
        'title' => $title,
        'body' => $body,
        'status' => 'success'
    );
}

function mythguard_delete_codex($request)
{
    $codex_id = $request['id'];
    $codex = get_post($codex_id);

    if (!$codex) {
        return new WP_Error(
            'codex_not_found',
            'Codex entry not found',
            array('status' => 404)
        );
    }

    $result = wp_delete_post($codex_id, false);

    if (!$result) {
        return new WP_Error(
            'codex_delete_failed',
            'Failed to delete codex entry',
            array('status' => 500)
        );
    }

    return new WP_REST_Response(array(
        'deleted' => true,
        'message' => 'Codex entry deleted successfully'
    ), 200);
}

function mythguard_update_codex($request)
{
    $codex_id = $request['id'];
    $title = $request['title'];
    $body = $request['body'];
    $user_id = get_current_user_id();

    // Check if the codex exists
    $codex = get_post($codex_id);
    if (!$codex) {
        return new WP_Error(
            'codex_not_found',
            'Codex entry not found',
            array('status' => 404)
        );
    }

    // Check if user owns this codex
    if ($codex->post_author != $user_id) {
        return new WP_Error(
            'codex_update_forbidden',
            'You do not have permission to update this codex entry',
            array('status' => 403)
        );
    }

    // Update the codex
    $updated_post = wp_update_post(array(
        'ID' => $codex_id,
        'post_title' => $title,
        'post_content' => $body,
        'post_type' => 'codex',
        'post_status' => 'publish'
    ), true);

    if (is_wp_error($updated_post)) {
        return new WP_Error(
            'codex_update_failed',
            'Failed to update codex entry',
            array('status' => 500)
        );
    }

    return new WP_REST_Response(array(
        'updated' => true,
        'message' => 'Codex entry updated successfully',
        'codex' => array(
            'id' => $codex_id,
            'title' => $title,
            'body' => $body
        )
    ), 200);
}
