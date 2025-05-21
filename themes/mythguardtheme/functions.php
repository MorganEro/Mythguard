<?php

/**
 * MythGuard Theme Functions
 * 
 * This file organizes all the functionality for the MythGuard theme.
 */

/**
 * SECTION 1: Required Files & Dependencies
 * Include all external files and set up initial dependencies
 */
require_once get_theme_file_path('/includes/search-route.php');
require_once get_theme_file_path('/includes/codex-route.php');
require_once get_theme_file_path('/includes/like-route.php');
require_once get_theme_file_path('/includes/contract-route.php');
require_once get_theme_file_path('/includes/calendar-route.php');

/**
 * SECTION 2: Theme Setup & Features
 * Register theme support, menus, image sizes, etc.
 */
function mythguard_features()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('guardianLandscape', 400, 260, true);
    add_image_size('pageBanner', 1500, 350, true);
}
add_action('after_setup_theme', 'mythguard_features');


/**
 * SECTION 3: REST API Configuration
 * Set up REST API endpoints and fields
 */
function mythguard_custom_rest_fields()
{
    // Register ACF fields for guardians and programs
    register_rest_field('guardian', 'acf', array(
        'get_callback' => function($object) {
            return get_fields($object['id']);
        }
    ));

    register_rest_field('program', 'acf', array(
        'get_callback' => function($object) {
            return get_fields($object['id']);
        }
    ));

    // Register count endpoint
    register_rest_route('mythguard/v1', '/codex-count', array(
        'methods' => 'GET',
        'callback' => function () {
            return array(
                'count' => count_user_posts(get_current_user_id(), 'codex')
            );
        },
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));


    // Register other fields
    register_rest_field('post', 'authorName', array(
        'get_callback' => function () {
            return get_the_author();
        }
    ));

    register_rest_field('codex', 'userCodexCount', array(
        'get_callback' => function ($post) {
            return count_user_posts(get_current_user_id(), 'codex');
        }
    ));
    register_rest_field('contract', 'userContractCount', array(
        'get_callback' => function ($post) {
            return count_user_posts(get_current_user_id(), 'contract');
        }
    ));
}
add_action('rest_api_init', 'mythguard_custom_rest_fields');

function pageBanner($args = NULL)
{
    if (!isset($args['title'])) {
        $args['title'] = mg_clean_private_title(get_the_title());
    }

    if (!isset($args['subtitle'])) {
        $args['subtitle'] = get_field('page_banner_subtitle');
    }

    if (!isset($args['photo'])) {
        if (get_field('page_banner_background_image') && !is_archive() && !is_home()) {
            $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
        } else {
            $args['photo'] = get_theme_file_uri('/images/default-background-image.jpg.webp');
        }
    }

?>
    <div class="page-banner">
        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo'] ?>)"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title']; ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle']; ?></p>
            </div>
        </div>
    </div>

<?php
}


/**
 * SECTION 4: Asset Management
 * Handle all script and style enqueuing
 */
function mythguard_files()
{
    // Leaflet Map Dependencies
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
    wp_enqueue_style('leaflet-routing-css', 'https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css', array(), '3.2.12');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
    wp_enqueue_script('leaflet-routing-js', 'https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js', array('leaflet-js'), '3.2.12', true);
    wp_enqueue_script('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js', array('leaflet-js'), '2.4.0', true);
    wp_enqueue_style('leaflet-control-geocoder', 'https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css', array(), '2.4.0');

    // WordPress API Dependencies
    wp_enqueue_script('wp-api-fetch');
    wp_localize_script('wp-api-fetch', 'wpApiSettings', array(
        'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest')
    ));
    wp_enqueue_script('mythguard_main_js', get_theme_file_uri('/build/index.js'), array('jquery', 'leaflet-js', 'wp-api', 'wp-api-fetch'), '1.0', true);

    // Localize script with theme URL and nonce
    wp_localize_script('mythguard_main_js', 'mythguardData', array(
        'root_url' => get_site_url(),
        'theme_url' => get_theme_file_uri(),
        'nonce' => wp_create_nonce('wp_rest')
    ));

    // Typography and Icons
    wp_enqueue_style('custom-google-font', '//fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Satisfy&display=swap');
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
        array(),
        '6.5.0'
    );

    // Theme Styles
    wp_enqueue_style('mythguard_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('mythguard_extra_styles', get_theme_file_uri('/build/index.css'));
}

add_action('wp_enqueue_scripts', 'mythguard_files');



/**
 * SECTION 5: Content Modifications
 * Customize how WordPress displays content
 */

/**
 * Strips 'Private: ' prefix from a title
 *
 * @param string $title The title to clean
 * @return string The cleaned title
 */
function mg_clean_private_title($title) {
    return str_replace('Private: ', '', $title);
}

/**
 * Customize the archive title
 *
 * @param string $title The title to customize
 * @return string The customized title
 */
function mythguard_archive_title($title)
{
    if (is_author()) {
        $title = 'Posts by ' . get_the_author();
    }
    return $title;
}
add_filter('get_the_archive_title', 'mythguard_archive_title');


/**
 * SECTION 6: Query Modifications
 * Customize WordPress queries
 */
function mythguard_adjust_queries($query)
{
    if (!is_admin() && is_post_type_archive('program') && $query->is_main_query()) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', -1);
    }

    if (!is_admin() && is_post_type_archive('gathering') && $query->is_main_query()) {
        $today = date('Y-m-d H:i:s');
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
            array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'datetime'
            )
        ));
    }

    if (!is_admin() && is_post_type_archive('guardian') && $query->is_main_query()) {
        $query->set('posts_per_page', 6);
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'mythguard_adjust_queries');


/**
 * SECTION 7: User Management
 * Handle user roles and capabilities
 */
function redirectSubsToFrontend()
{
    $ourCurrentUser = wp_get_current_user();

    if (count($ourCurrentUser->roles) == 1 && $ourCurrentUser->roles[0] == 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
}
add_action('admin_init', 'redirectSubsToFrontend');

function noAdminBarForSubscribers()
{
    $ourCurrentUser = wp_get_current_user();

    if (count($ourCurrentUser->roles) == 1 && $ourCurrentUser->roles[0] == 'subscriber') {
        show_admin_bar(false);
    }
}
add_action('wp_loaded', 'noAdminBarForSubscribers');

add_filter('wp_insert_post_data', 'mythguard_make_codex_private', 10, 2);
add_filter('wp_insert_post_data', 'mythguard_make_contract_private', 10, 2);

function mythguard_make_codex_private($data, $postarr)
{

    if (count_user_posts(get_current_user_id(), 'codex') > 15 && !$postarr['ID']) {
        wp_send_json_error([
            'message' => 'You have reached the maximum limit of 15 codex entries'
        ], 403);
        return;
    }
    if ($data['post_type'] == 'codex' && $data['post_status'] != 'trash') {
        $data['post_status'] = 'private';
    }
    return $data;
}

function mythguard_make_contract_private($data, $postarr)
{

    $user_id = get_current_user_id();
    if (!current_user_can('administrator') && count_user_posts($user_id, 'contract') > 5 && !$postarr['ID']) {
        wp_send_json_error([
            'message' => 'You have reached the maximum limit of 5 contracts'
        ], 403);
        return;
    }
    if ($data['post_type'] == 'contract' && $data['post_status'] != 'trash') {
        $data['post_status'] = 'private';
    }
    return $data;
}
function mythguard_contract_template_redirect() {
    if (is_singular('contract')) {
        if (!is_user_logged_in()) {
            wp_redirect(esc_url(site_url('/')));
            exit;
        }
        $post = get_post();
        if (!current_user_can('administrator') && $post->post_author != get_current_user_id()) {
            wp_redirect(esc_url(site_url('/')));
            exit;
        }
    }
}
add_action('template_redirect', 'mythguard_contract_template_redirect');


/**
 * SECTION 8: Login Customizations
 * Customize the WordPress login screen
 */
add_filter('login_headerurl', 'mythguard_login_header_url');

function mythguard_login_header_url()
{
    return esc_url(home_url());
}

add_filter('login_headertext', 'mythguard_login_header_text');

function mythguard_login_header_text()
{
    return '<strong>Myth<span class="c-gold">Guard</span></strong>';
}

add_action('login_enqueue_scripts', 'mythguard_login_css');

function mythguard_login_css()
{
    wp_enqueue_style("custom-google-font", "//fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Open+Sans:ital,wght@0,400..700;1,400..700&display=swap");
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
        array(),
        '6.5.0'
    );
    wp_enqueue_style("mythguard_main_styles", get_theme_file_uri("/build/style-index.css"));
    wp_enqueue_style("mythguard_extra_styles", get_theme_file_uri("/build/index.css"));
}

// Add CSS to swap how password visibility icons appear

add_action('login_head', function () {
?>
    <style>
        .dashicons-visibility:before {
            content: "\f530" !important;
            /* dashicons-hidden */
        }

        .dashicons-hidden:before {
            content: "\f177" !important;
            /* dashicons-visibility */
        }
    </style>
<?php
});
