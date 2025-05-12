<?php

add_action('rest_api_init', 'mythguard_like_route');

function mythguard_like_route() {
    // Common arguments for routes
    $id_arg = array(
        'required' => true,
        'validate_callback' => function($param) {
            return is_numeric($param);
        }
    );

    // Register CREATE route
    register_rest_route('mythguard/v1', 'manageLike', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'createLike',
        'permission_callback' => function() {
            return current_user_can('publish_posts');
            //return is_user_logged_in();

        },
        'args' => array(
            'guardianId' => $id_arg
        )
    ));

    // Register DELETE route
    register_rest_route('mythguard/v1', 'manageLike', array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'deleteLike',
        'permission_callback' => function() {
            return current_user_can('publish_posts');
            
        },
        'args' => array(
            'like' => $id_arg
        ),
        'args' => array(
            'like' => $id_arg
        )
    ));
}

function createLike($data) {
    if (is_user_logged_in()) {
        $guardian = sanitize_text_field($data['guardianId']);

        $existQuery = new WP_Query(array(
            'author' => get_current_user_id(),
            'post_type' => 'like',
            'meta_query' => array(
                array(
                    'key' => 'liked_guardian_id',
                    'compare' => '=',
                    'value' => $guardian
                )
            )
        ));

        if ($existQuery->found_posts == 0 && get_post_type($guardian) == 'guardian') {
            // Create new like post
            return wp_insert_post(array(
                'post_type' => 'like',
                'post_status' => 'publish',
                'post_title' => 'Like on ' . get_the_title($guardian),
                'meta_input' => array(
                    'liked_guardian_id' => $guardian
                )
            ));
        } else {
            die("Invalid guardian id");
        }
    } else {
        die("Only logged in users can like guardians.");
    }
}

function deleteLike($data) {
    $likeId = sanitize_text_field($data['like']);
    if (get_current_user_id() == get_post_field('post_author', $likeId) && get_post_type($likeId) == 'like') {
        wp_delete_post($likeId, true);
        return 'Like deleted.';
    } else {
        die("You do not have permission to delete that.");
    }
}