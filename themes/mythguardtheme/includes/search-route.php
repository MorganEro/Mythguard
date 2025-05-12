<?php
add_action('rest_api_init', 'mythguard_register_search_route');

function mythguard_register_search_route()
{
    register_rest_route('mythguard/v1', 'search', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'mythguardSearchHandler',
        'permission_callback' => '__return_true',
        'args' => array(
            'term' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($param) {
                    return !empty($param) && is_string($param);
                }
            )
        )
    ));
}

function mythguardSearchHandler($request)
{
    try {
        // Get and validate search term
        $term = $request->get_param('term');
        if (empty($term)) {
            return new WP_Error(
                'empty_term',
                'Search term cannot be empty',
                array('status' => 400)
            );
        }

        // Setup main query
        $mainQuery = new WP_Query(array(
            'post_type' => array('guardian', 'post', 'page', 'program', 'location', 'event'),
            's' => $term,
            'posts_per_page' => 50 // Limit results for performance
        ));
    $results = array(
        'generalInfo' => array(),
        'guardians' => array(),
        'programs' => array(),
        'locations' => array(),
        'events' => array()
    );

    while ($mainQuery->have_posts()) {
        $mainQuery->the_post();


        if (get_post_type() == 'guardian') {
            array_push($results['guardians'], array(
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'image' => get_the_post_thumbnail_url(0, 'guardianLandscape')
            ));
        } elseif (get_post_type() == 'program') {
            array_push($results['programs'], array(
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'id' => get_the_ID(),
            ));
        } elseif (get_post_type() == 'location') {
            array_push($results['locations'], array(
                'title' => get_the_title(),
                'permalink' => get_permalink()
            ));
        } elseif (get_post_type() == 'event') {
            $eventDate = new DateTime(get_field('event_date'));
            $description = null;
            if (has_excerpt()) {
                $description = get_the_excerpt();
            } else {
                $description = wp_trim_words(get_the_content(), 18);
            }
            array_push($results['events'], array(
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'month' => $eventDate->format('M'),
                'day' => $eventDate->format('d'),
                'description' => $description,
            ));
        } else {
            array_push($results['generalInfo'], array(
                'postType' => get_post_type(),
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'authorName' => get_the_author()
            ));
        }
    }

    if ($results['programs']) {
        $programsMetaQuery = array('relation' => 'OR');
        foreach ($results['programs'] as $program) {
            array_push($programsMetaQuery, array(
                'key' => 'related_programs',
                'value' => '"' . $program['id'] . '"',
                'compare' => 'LIKE'
            ));
        }

        $programRelationshipQuery = new WP_Query(array(
            'post_type' => array('guardian', 'event'),
            'meta_query' => $programsMetaQuery,
        ));

        while ($programRelationshipQuery->have_posts()) {
            $programRelationshipQuery->the_post();


            if (get_post_type() == 'guardian') {
                array_push($results['guardians'], array(
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'image' => get_the_post_thumbnail_url(0, 'guardianLandscape')
                ));
            } elseif (get_post_type() == 'event') {
                $eventDate = new DateTime(get_field('event_date'));
                $description = null;
                if (has_excerpt()) {
                    $description = get_the_excerpt();
                } else {
                    $description = wp_trim_words(get_the_content(), 18);
                }
                array_push($results['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'month' => $eventDate->format('M'),
                    'day' => $eventDate->format('d'),
                    'description' => $description,
                ));
            }
        }

        $results['guardians'] = array_values(array_unique($results['guardians'], SORT_REGULAR));
        $results['events'] = array_values(array_unique($results['events'], SORT_REGULAR));
    };



        // Ensure we reset the post data
        wp_reset_postdata();
        
        // Return results with proper headers
        $response = new WP_REST_Response($results);
        $response->set_status(200);
        
        return $response;
    } catch (Exception $e) {
        return new WP_Error(
            'search_error',
            'An error occurred while performing the search',
            array('status' => 500)
        );
    }
}

