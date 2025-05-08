<?php

/**
 * Plugin Name: Lawyer Finder
 * Description: A custom plugin to manage and display lawyers with filters by state and practice area.
 * Version: 1.0
 * Author: Softcrafty
 */

if (!defined('ABSPATH')) exit;



function ld_enqueue_scripts()
{
    // Enqueue Bootstrap CSS (use the latest version or a custom version)
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', [], '4.5.2');

    // Enqueue jQuery UI Autocomplete
    wp_enqueue_script('jquery-ui-autocomplete');

    // Enqueue your custom autocomplete.js script
    wp_enqueue_script('ld-autocomplete', plugin_dir_url(__FILE__) . 'js/autocomplete.js', ['jquery', 'jquery-ui-autocomplete'], null, true);

    // Localize script for autocomplete.js
    wp_localize_script('ld-autocomplete', 'ld_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);

    // Enqueue Bootstrap JS (using the latest version or your custom version)
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', ['jquery'], '4.5.2', true);

    // Enqueue your custom ld-lawyer-ajax.js script
    wp_enqueue_script('ld-lawyer-ajax', plugin_dir_url(__FILE__) . 'js/ld-lawyer-ajax.js', ['jquery'], null, true);

    // Localize script for ld-lawyer-ajax.js
    wp_localize_script('ld-lawyer-ajax', 'ldLawyerAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ld_lawyer_nonce'),
    ));

    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', ['jquery'], null, true);
    wp_enqueue_script('ld-select2-init', plugin_dir_url(__FILE__) . 'js/ld-select2-init.js', ['jquery', 'select2-js'], null, true);


    wp_enqueue_script('ld-pagi-ajax', plugin_dir_url(__FILE__) . 'js/ld-pagi-ajax.js', ['jquery'], null, true);
    // Localize script for AJAX
    wp_localize_script('ld-pagi-ajax', 'ld_lawyer_vars', array(
        'nonce' => wp_create_nonce('ld_lawyer_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php')
    ));


    wp_enqueue_script('ld-result-pagi-ajax', plugin_dir_url(__FILE__) . 'js/ld-result-pagi-ajax.js', ['jquery'], null, true);

    wp_localize_script('ld-result-pagi-ajax', 'ld_resultlawyer_vars', array(
        'nonce' => wp_create_nonce('ld_resultlawyer_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php')
    ));




    wp_enqueue_style('ld-custom-style', plugin_dir_url(__FILE__) . 'css/custom-style.css');
}

add_action('wp_enqueue_scripts', 'ld_enqueue_scripts');


// Register Lawyer Custom Post Type
function ld_register_lawyer_post_type()
{
    register_post_type('lawyer', [
        'labels' => [
            'name' => 'Lawyers',
            'singular_name' => 'Lawyer'
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'lawyers'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'ld_register_lawyer_post_type');

function ld_create_lawyer_directory_page()
{
    // Check if page already exists
    $page_title = 'Lawyers Founder';
    $page_check = get_page_by_title($page_title);

    if (!$page_check) {
        // Create the page
        $page_data = array(
            'post_title'     => $page_title,
            'post_content'   => '[lawyer_list]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1, // You can customize the author
        );
        wp_insert_post($page_data);
    }
}
register_activation_hook(__FILE__, 'ld_create_lawyer_directory_page');


//file Includes
require_once plugin_dir_path(__FILE__) . 'includes/ld-lawyer-info-callback.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode/filter.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode/lawyer-listings.php';



// Add Custom Fields (Meta Boxes)
function ld_add_lawyer_meta_boxes()
{
    add_meta_box('ld_lawyer_info', 'Lawyer Information', 'ld_lawyer_info_callback', 'lawyer', 'normal', 'high');
}
add_action('add_meta_boxes', 'ld_add_lawyer_meta_boxes');


// Filter Lawyers based on query parameters
function ld_filter_lawyer_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('lawyer')) {
        if (!empty($_GET['ld_state'])) {
            $query->set('meta_query', [
                [
                    'key' => '_ld_state',
                    'value' => sanitize_text_field($_GET['ld_state']),
                    'compare' => 'LIKE'
                ]
            ]);
        }
        if (!empty($_GET['ld_specialization'])) {
            $meta_query = $query->get('meta_query') ?: [];
            $meta_query[] = [
                'key' => '_ld_specializations',
                'value' => sanitize_text_field($_GET['ld_specialization']),
                'compare' => 'LIKE'
            ];
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'ld_filter_lawyer_query');


// AJAX for Specialization autocomplete
function ld_get_specializations_ajax()
{
    global $wpdb;
    $term = sanitize_text_field($_GET['term']);
    $results = $wpdb->get_col(
        $wpdb->prepare("
            SELECT DISTINCT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_ld_specializations'
            AND meta_value LIKE %s
            LIMIT 10
        ", '%' . $wpdb->esc_like($term) . '%')
    );

    $suggestions = [];
    foreach ($results as $entry) {
        $parts = explode(',', $entry);
        foreach ($parts as $part) {
            $trimmed = trim($part);
            if (stripos($trimmed, $term) !== false && !in_array($trimmed, $suggestions)) {
                $suggestions[] = $trimmed;
            }
        }
    }

    echo json_encode(array_values(array_unique($suggestions)));
    wp_die();
}
add_action('wp_ajax_ld_get_specializations', 'ld_get_specializations_ajax');
add_action('wp_ajax_nopriv_ld_get_specializations', 'ld_get_specializations_ajax');
