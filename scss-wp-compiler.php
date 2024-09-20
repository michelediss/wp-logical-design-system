<?php
/*
Plugin Name: Logical Design System
Plugin URI: https://github.com/michelediss/wp-logical-design-system
Description: Finally a logical design system: color palette & typographic scale generator + 50 google font pairings
Version: 1.0
Author: Michele Paolino
Author URI: https://michelepaolino.me
*/

// Include the SCSSPHP library
require 'libs/scssphp/scss.inc.php';

use ScssPhp\ScssPhp\Compiler;

// Function to add the "Compile SCSS" button to the wpadminbar
function add_compile_button($wp_admin_bar) {
    // Define the properties of the new button
    $args = array(
        'id' => 'compile_scss', // Button ID
        'title' => 'Compile SCSS', // Button title
        'href' => '#', // Use JavaScript to handle the click
        'meta' => array(
            'class' => 'compile-scss-class', // CSS class for styling
            'title' => 'Compile SCSS' // Tooltip title
        )
    );
    // Add the button to the wpadminbar
    $wp_admin_bar->add_node($args);
}
// Hook the function to 'admin_bar_menu' action with high priority (999)
add_action('admin_bar_menu', 'add_compile_button', 999);

// Function to handle the SCSS compilation request via AJAX
function handle_scss_compilation_ajax() {
    // Check for nonce security
    check_ajax_referer('scss_compilation_nonce', 'security');

    $scss = new Compiler(); // Initialize the SCSS compiler
    $scss->setImportPaths(__DIR__ . '/scss/'); // Set the import path for SCSS files

    // Read the main SCSS file content
    $scss_content = file_get_contents(__DIR__ . '/scss/main.scss');

    // If the SCSS file cannot be read, return an error message
    if ($scss_content === false) {
        wp_send_json_error('Unable to read the main.scss file');
    }

    try {
        // Compile the SCSS content to CSS
        $compiled_css = $scss->compile($scss_content);
        // Save the compiled CSS to a file
        file_put_contents(__DIR__ . '/output-css/style.css', $compiled_css);

        // Set the formatter to compressed for minified CSS
        $scss->setFormatter('ScssPhp\ScssPhp\Formatter\Compressed');
        // Compile the minified CSS
        $minified_css = $scss->compile($scss_content);
        // Save the minified CSS to a file
        file_put_contents(__DIR__ . '/output-css/style.min.css', $minified_css);

        // Return success response
        wp_send_json_success('Compilation completed successfully!');
    } catch (\ScssPhp\ScssPhp\Exception\CompilerException $e) {
        // Handle SCSS compiler exceptions and return the error message
        wp_send_json_error('Error during SCSS compilation: ' . $e->getMessage());
    } catch (\Exception $e) {
        // Handle general exceptions and return the error message
        wp_send_json_error('General error: ' . $e->getMessage());
    }
}
// Hook the AJAX functions for logged-in users
add_action('wp_ajax_compile_scss', 'handle_scss_compilation_ajax');

// Function to enqueue the compiled and minified CSS file in WordPress
function swell_scales_enqueue_styles() {
    // Get the URL path of the minified CSS file
    $min_css_path = plugin_dir_url(__FILE__) . 'output-css/style.min.css';

    // Enqueue the minified CSS file with a handle 'swell-scales-styles-min'
    wp_enqueue_style('swell-scales-styles-min', $min_css_path);
}
// Hook the function to 'wp_enqueue_scripts' action to include the CSS file in the frontend
add_action('wp_enqueue_scripts', 'swell_scales_enqueue_styles');

// Enqueue the JavaScript for AJAX handling
function enqueue_scss_compilation_script() {
    // Enqueue the custom script
    wp_enqueue_script('scss_compilation_script', plugin_dir_url(__FILE__) . 'scss-compilation.js', array('jquery'), null, true);

    // Localize the script with necessary data
    wp_localize_script('scss_compilation_script', 'scss_compilation', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('scss_compilation_nonce')
    ));
}
// Hook to enqueue the script in admin
add_action('admin_enqueue_scripts', 'enqueue_scss_compilation_script');
