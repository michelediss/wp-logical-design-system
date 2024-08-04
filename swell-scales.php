<?php
/*
Plugin Name: Swell Scales for WordPress
Description: Enhance your WordPress projects with a typographic scales generator, font pairing system, and color palette creator. 
Version: 1.0
Author: Michele Paolino
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
        'href' => admin_url('?action=compila_scss'), // URL the button points to
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

// Function to handle the SCSS compilation request
function gestisci_compilazione_scss() {
    // Check if the 'compila_scss' action is triggered
    if (isset($_GET['action']) && $_GET['action'] === 'compila_scss') {
        $scss = new Compiler(); // Initialize the SCSS compiler
        $scss->setImportPaths(__DIR__ . '/scss/'); // Set the import path for SCSS files

        // Read the main SCSS file content
        $scss_content = file_get_contents(__DIR__ . '/scss/main.scss');

        // If the SCSS file cannot be read, terminate with an error message
        if ($scss_content === false) {
            wp_die('Unable to read the main.scss file');
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

            // Display a success message and terminate
            wp_die('Compilation completed successfully!');
        } catch (\ScssPhp\ScssPhp\Exception\CompilerException $e) {
            // Handle SCSS compiler exceptions and display the error message
            wp_die('Error during SCSS compilation: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Handle general exceptions and display the error message
            wp_die('General error: ' . $e->getMessage());
        }
    }
}
// Hook the function to 'admin_init' action
add_action('admin_init', 'gestisci_compilazione_scss');

// Function to enqueue the compiled and minified CSS file in WordPress
function swell_scales_enqueue_styles() {
    // Get the URL path of the minified CSS file
    $min_css_path = plugin_dir_url(__FILE__) . 'output-css/style.min.css';

    // Enqueue the minified CSS file with a handle 'swell-scales-styles-min'
    wp_enqueue_style('swell-scales-styles-min', $min_css_path);
}
// Hook the function to 'wp_enqueue_scripts' action to include the CSS file in the frontend
add_action('wp_enqueue_scripts', 'swell_scales_enqueue_styles');
