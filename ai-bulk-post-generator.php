<?php
/**
 * Plugin Name: AI Bulk Post Generator
 * Plugin URI: https://wpdeveloper.holeiholo.com
 * Description: Automatically generates and publishes bulk posts with AI-generated content, keywords, and images.
 * Version: 1.0
 * Author: Hasibul Hasan Rajib
 * Author URI: https://hasibulhasan.holeiholo.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-bulk-post-generator
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add a menu page for plugin settings
function ai_bulk_post_generator_menu() {
    add_menu_page(
        'AI Bulk Post Generator',
        'AI Bulk Posts',
        'manage_options',
        'ai-bulk-post-generator',
        'ai_bulk_post_generator_page',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'ai_bulk_post_generator_menu');

// Plugin settings page
function ai_bulk_post_generator_page() {
    ?>
    <div class="wrap my-plugin-wrapper">
        <h1>AI Bulk Post Generator</h1>
        <form method="post" action="">
            <?php wp_nonce_field('ai_bulk_generate_nonce', 'ai_bulk_nonce'); ?>
            
            <label for="post_count">Number of Posts:</label>
            <input type="number" name="post_count" id="post_count" value="10" min="1" max="100" required>
            <br><br>

            <label for="keywords">Enter Keywords (comma separated):</label>
            <textarea name="keywords" id="keywords" rows="3" required></textarea>
            <br><br>

            <label for="category">Select Category:</label>
            <select name="category" id="category">
                <?php
                $categories = get_categories();
                foreach ($categories as $category) {
                    echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                }
                ?>
            </select>
            <br><br>
            
            <input type="submit" name="generate_posts" value="Generate Posts" class="button button-primary">
        </form>
    </div>
    <?php

    if (isset($_POST['generate_posts'])) {
        if (!isset($_POST['ai_bulk_nonce']) || !wp_verify_nonce($_POST['ai_bulk_nonce'], 'ai_bulk_generate_nonce')) {
            wp_die('Security check failed.');
        }

        $post_count = intval($_POST['post_count']);
        $keywords = sanitize_textarea_field($_POST['keywords']);
        $category_id = intval($_POST['category']);

        ai_bulk_generate_posts($post_count, $keywords, $category_id);
    }
}

// AI Content Generator Function
function ai_generate_content($title, $keyword) {
    return "<p>AI-generated content for: <strong>$title</strong>. This article covers information related to $keyword.</p>";
}

// AI Image Generator (Fetch from Free Sources)
function ai_fetch_image($keyword) {
    return esc_url("https://source.unsplash.com/800x600/?" . urlencode($keyword));
}

// Generate Bulk Posts
function ai_bulk_generate_posts($post_count, $keywords, $category_id) {
    $keywords_array = explode(',', $keywords);
    $keyword_count = count($keywords_array);

    for ($i = 0; $i < $post_count; $i++) {
        $random_keyword = trim($keywords_array[rand(0, $keyword_count - 1)]);
        $title = sanitize_text_field("AI-Generated Post About " . ucfirst($random_keyword));
        $content = ai_generate_content($title, $random_keyword);
        $image_url = ai_fetch_image($random_keyword);

        $post_data = array(
            'post_title'    => wp_strip_all_tags($title),
            'post_content'  => "<img src='$image_url' alt='" . esc_attr($random_keyword) . "'><p>$content</p>",
            'post_status'   => 'publish',
            'post_category' => array($category_id),
            'post_type'     => 'post'
        );

        wp_insert_post($post_data);
    }

    echo '<div class="updated"><p><strong>' . esc_html($post_count) . ' posts created successfully!</strong></p></div>';
}

// Enqueue Plugin CSS (Admin Panel)
function ai_plugin_enqueue_admin_styles($hook) {
    if ($hook !== 'toplevel_page_ai-bulk-post-generator') {
        return;
    }
    wp_enqueue_style('ai-plugin-admin-css', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('admin_enqueue_scripts', 'ai_plugin_enqueue_admin_styles');

// Enqueue Bootstrap in Admin Panel
function ai_plugin_enqueue_admin_bootstrap($hook) {
    if ($hook !== 'toplevel_page_ai-bulk-post-generator') {
        return;
    }
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'ai_plugin_enqueue_admin_bootstrap');
?>
