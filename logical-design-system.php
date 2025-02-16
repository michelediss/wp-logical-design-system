<?php
/*
Plugin Name: Logical Design System
Plugin URI: https://github.com/michelediss/wp-logical-design-system
Description: Finally a logical design system: color palette & typographic scale generator + 50 Google font pairings
Version: 1.0
Author: Michele Paolino
Author URI: https://michelepaolino.me
*/

// Include the SCSSPHP library using the correct path
require_once plugin_dir_path(__FILE__) . 'libs/scssphp/scss.inc.php';

use ScssPhp\ScssPhp\Compiler;

// Function to add the "Compile SCSS" button to the wpadminbar
function add_compile_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }

    $args = array(
        'id'    => 'compile_scss',
        'title' => 'Compile SCSS',
        'href'  => '#',
        'meta'  => array(
            'class' => 'compile-scss-class',
            'title' => 'Compile SCSS'
        )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'add_compile_button', 999);

// Function to handle SCSS compilation via AJAX
function handle_scss_compilation_ajax() {
    check_ajax_referer('scss_compilation_nonce', 'security');

    handle_scss_compilation();
}
add_action('wp_ajax_compile_scss', 'handle_scss_compilation_ajax');

// Function to handle SCSS compilation and integration of plugin CSS
function handle_scss_compilation() {
    $scss = new Compiler();

    // Define SCSS paths
    $plugin_sass_path    = plugin_dir_path(__FILE__) . 'scss/';
    $theme_sass_path     = get_stylesheet_directory() . '/assets/scss/';
    $bootstrap_sass_path = $plugin_sass_path . 'bootstrap/scss/';

    // Set import paths for SCSS
    $scss->setImportPaths(array($theme_sass_path, $plugin_sass_path, $bootstrap_sass_path));

    // Path to main.scss file in the plugin
    $main_scss_path = $plugin_sass_path . 'main.scss';

    try {
        // Read the content of main.scss
        $main_scss = file_get_contents($main_scss_path);

        // Compile the main SCSS
        $compiled_css = $scss->compile($main_scss);

        // Include plugin CSS
        $plugin_css_files = get_option('lds_plugin_css_files', array());

        $plugins_css = '';

        foreach ($plugin_css_files as $css_file) {
            $src = $css_file['src'];

            // Get the physical path of the CSS file
            if (strpos($src, 'http') !== false) {
                $css_path = str_replace(site_url(), ABSPATH, $src);
            } else {
                $css_path = ABSPATH . ltrim($src, '/');
            }

            // Check if the file exists
            if (file_exists($css_path)) {
                // Read the content of the CSS file
                $css_content = file_get_contents($css_path);
                // Add the CSS content to the variable
                $plugins_css .= $css_content . "\n";
            }
        }

        // Destination path for the compiled CSS
        $output_css_dir     = get_stylesheet_directory() . '/assets/css/';
        $compiled_css_path  = $output_css_dir . 'lds-style.css';

        // Create the folder if it doesn't exist
        if (!is_dir($output_css_dir)) {
            mkdir($output_css_dir, 0755, true);
        }

        // Append the plugin CSS to the compiled CSS
        $final_css = $compiled_css . "\n" . $plugins_css;

        // Save the combined CSS to the destination path
        if (file_put_contents($compiled_css_path, $final_css) === false) {
            return;
        }

        // Minify the compiled CSS
        $scss->setFormatter('ScssPhp\ScssPhp\Formatter\Compressed');
        $minified_css = $scss->compile($main_scss);

        // Append the plugin CSS to the minified CSS
        $minified_final_css = $minified_css . "\n" . $plugins_css;

        $minified_css_path = $output_css_dir . 'lds-style.min.css';

        // Save the combined minified CSS
        if (file_put_contents($minified_css_path, $minified_final_css) === false) {
            return;
        }

        // If the call is via AJAX, send a success response
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_success('Compilation completed successfully!');
        }
    } catch (\ScssPhp\ScssPhp\Exception\CompilerException $e) {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_error('Error during SCSS compilation: ' . $e->getMessage());
        }
    } catch (\Exception $e) {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_error('General error: ' . $e->getMessage());
        }
    }
}

// Function to collect plugin CSS during page loading
function lds_collect_plugin_css() {
    global $wp_styles;

    // Initialize the array for plugin CSS
    $plugin_css_files = array();

    // Iterate over all enqueued styles
    foreach ($wp_styles->queue as $handle) {
        $style = $wp_styles->registered[$handle];
        $src   = $style->src;

        // Check if the CSS comes from a plugin
        if (strpos($src, plugins_url()) !== false) {
            // Add the CSS to the list
            $plugin_css_files[] = array(
                'handle' => $handle,
                'src'    => $src,
            );
        }
    }

    // Save the list in an option
    update_option('lds_plugin_css_files', $plugin_css_files);
}
add_action('wp_enqueue_scripts', 'lds_collect_plugin_css', 1000);

// Function to deregister plugin CSS
function lds_deregister_plugin_styles() {
    $plugin_css_files = get_option('lds_plugin_css_files', array());

    foreach ($plugin_css_files as $css_file) {
        $handle = $css_file['handle'];
        wp_deregister_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'lds_deregister_plugin_styles', 9999);

// Function to enqueue the compiled and minified CSS in WordPress
function swell_scales_enqueue_styles() {
    $min_css_url  = get_stylesheet_directory_uri() . '/assets/css/lds-style.min.css';
    $min_css_path = get_stylesheet_directory() . '/assets/css/lds-style.min.css';

    if (file_exists($min_css_path)) {
        wp_enqueue_style('logical-design-system-styles-min', $min_css_url, array(), filemtime($min_css_path));
    }
}
add_action('wp_enqueue_scripts', 'swell_scales_enqueue_styles', 20);

// Plugin activation function
function lds_plugin_activation() {
    $source_dir      = plugin_dir_path(__FILE__) . 'scss/input/';
    $destination_dir = get_stylesheet_directory() . '/assets/scss/';

    if (!is_dir($source_dir)) {
        return;
    }

    if (!is_dir($destination_dir)) {
        if (!mkdir($destination_dir, 0755, true)) {
            return;
        }
    }

    recursive_copy($source_dir, $destination_dir);
}

// Function to recursively copy directories and files without overwriting existing files
function recursive_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                recursive_copy($srcPath, $dstPath);
            } else {
                if (!file_exists($dstPath)) {
                    copy($srcPath, $dstPath);
                }
            }
        }
    }
    closedir($dir);
}
register_activation_hook(__FILE__, 'lds_plugin_activation');

// Function to enqueue JavaScript for AJAX handling
function enqueue_scss_compilation_script() {
    wp_enqueue_script('scss_compilation_script', plugin_dir_url(__FILE__) . 'scss-compilation.js', array('jquery'), null, true);

    wp_localize_script('scss_compilation_script', 'scss_compilation', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('scss_compilation_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_scss_compilation_script');

// Function to dequeue CSS files of parent and child themes
function dequeue_child_theme_styles() {
    wp_dequeue_style('logical-theme-style');
    wp_dequeue_style('logical-theme-child-style');
}
add_action('wp_enqueue_scripts', 'dequeue_child_theme_styles', 20);

// Scheduling compilation via WP-Cron
function schedule_scss_compilation() {
    if (!wp_next_scheduled('nightly_scss_compilation')) {
        wp_schedule_event(strtotime('04:00:00'), 'daily', 'nightly_scss_compilation');
    }
}
add_action('wp', 'schedule_scss_compilation');

// Hook to run SCSS compilation every night at 4:00 AM
add_action('nightly_scss_compilation', 'handle_scss_compilation');

// Function to remove the WP-Cron event upon plugin deactivation
function lds_plugin_deactivation() {
    $timestamp = wp_next_scheduled('nightly_scss_compilation');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'nightly_scss_compilation');
    }
}
register_deactivation_hook(__FILE__, 'lds_plugin_deactivation');
