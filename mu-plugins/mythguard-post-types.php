<?php
function mythguard_custom_post_types()
{
    // Gathering Post Type
    register_post_type('gathering', array(
        'capability_type' => 'gathering',
        'map_meta_cap' => true,
        'public' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'excerpt'),
        'rewrite' => array(
            'slug' => 'gatherings',
        ),
        'has_archive' => true,
        'labels' => array(
            'name' => 'Gatherings',
            'singular_name' => 'Gathering',
            'add_new_item' => 'Add New Gathering',
            'edit_item' => 'Edit Gathering',
            'all_items' => 'All Gatherings',

        ),

        'menu_icon' => 'dashicons-calendar-alt',
    ));
    // Location Post Type
    register_post_type('location', array(
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array(
            'slug' => 'locations',
        ),
        'has_archive' => true,
        'labels' => array(
            'name' => 'Locations',
            'singular_name' => 'Location',
            'add_new_item' => 'Add New Location',
            'edit_item' => 'Edit Location',
            'all_items' => 'All Locations',

        ),

        'menu_icon' => 'dashicons-location-alt',
    ));

    // Program Post Type
    register_post_type('program', array(
        'supports' => array('title', 'editor'),
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array(
            'slug' => 'programs',
        ),
        'has_archive' => true,
        'labels' => array(
            'name' => 'Programs',
            'singular_name' => 'Program',
            'add_new_item' => 'Add New Program',
            'edit_item' => 'Edit Program',
            'all_items' => 'All Programs',

        ),

        'menu_icon' => 'dashicons-shield-alt',
    ));

    // Guardian Post Type
    register_post_type('guardian', array(
        'supports' => array('title', 'editor', 'thumbnail'),
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array(
            'slug' => 'guardians',
        ),
        'has_archive' => true,
        'labels' => array(
            'name' => 'Guardians',
            'singular_name' => 'Guardian',
            'add_new_item' => 'Add New Guardian',
            'edit_item' => 'Edit Guardian',
            'all_items' => 'All Guardians',

        ),

        'menu_icon' => 'dashicons-groups',
    ));

    // Codex Post Type
    register_post_type('codex', array(
        'capability_type' => 'codex',
        'map_meta_cap' => true,
        'supports' => array('title', 'editor'),
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => array(
            'slug' => 'codexs',
        ),
        'labels' => array(
            'name' => 'Codexs',
            'singular_name' => 'Codex',
            'add_new_item' => 'Add New Codex',
            'edit_item' => 'Edit Codex',
            'all_items' => 'All Codexs',
        ),

        'menu_icon' => 'dashicons-welcome-write-blog',
    ));

    // Contract Post Type
    register_post_type('contract', array(
        'capability_type' => 'contract',
        'map_meta_cap' => true,
        'supports' => array('title', 'editor'),
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'publicly_queryable' => true, 
        'rewrite' => array(
            'slug' => 'contracts',
        ),
        'labels' => array(
            'name' => 'Contracts',
            'singular_name' => 'Contract',
            'add_new_item' => 'Add New Contract',
            'edit_item' => 'Edit Contract',
            'all_items' => 'All Contracts',
        ),

        'menu_icon' => 'dashicons-clipboard',
    ));

    // Like Post Type
    register_post_type('like', array(
        'supports' => array('title'),
        'public' => false,
        'show_ui' => true,
        'labels' => array(
            'name' => 'Likes',
            'singular_name' => 'Like',
            'add_new_item' => 'Add New Like',
            'edit_item' => 'Edit Like',
            'all_items' => 'All Likes',
        ),

        'menu_icon' => 'dashicons-heart',
    ));
}
add_action('init', 'mythguard_custom_post_types');
