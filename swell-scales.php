<?php
/*
Plugin Name: Swell Scales for WordPress
Description: Enhance your Tailwind CSS projects with a typographic scales generator, font pairing system, and color palette creator. 
Version: 1.0
Author: Michele Paolino
*/

require 'libs/scssphp/scss.inc.php';

use ScssPhp\ScssPhp\Compiler;

function swell_scales_compile_scss() {
    $scss = new Compiler();

    $scss->setImportPaths( plugin_dir_path( __FILE__ ) . 'scss/' );

    $scss_content = file_get_contents( plugin_dir_path( __FILE__ ) . 'scss/main.scss' );

    if ($scss_content === false) {
        error_log('Impossibile leggere il file main.scss');
        return;
    }

    try {
        $compiled_css = $scss->compile($scss_content);
        file_put_contents( plugin_dir_path( __FILE__ ) . 'output-css/style.css', $compiled_css );

        $scss->setFormatter('ScssPhp\ScssPhp\Formatter\Compressed');
        $minified_css = $scss->compile($scss_content);
        file_put_contents( plugin_dir_path( __FILE__ ) . 'output-css/style.min.css', $minified_css );
    } catch (Exception $e) {
        error_log('Errore durante la compilazione SCSS: ' . $e->getMessage());
    }
}

function swell_scales_enqueue_styles() {
    $css_path = plugin_dir_url( __FILE__ ) . 'output-css/style.css';
    $min_css_path = plugin_dir_url( __FILE__ ) . 'output-css/style.min.css';

    wp_enqueue_style( 'swell-scales-styles', $css_path );
    wp_enqueue_style( 'swell-scales-styles-min', $min_css_path );
}
add_action( 'wp_enqueue_scripts', 'swell_scales_enqueue_styles' );

function swell_scales_watch_scss_changes() {
    $scss_directory = plugin_dir_path( __FILE__ ) . 'scss/';
    $last_check_file = plugin_dir_path( __FILE__ ) . 'scss/.last_scss_check';

    $last_check = @file_get_contents($last_check_file);
    if ($last_check === false) {
        $last_check = time();
        file_put_contents($last_check_file, $last_check);
    }

    $last_check = (int)$last_check;

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scss_directory));
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'scss' && filemtime($file) > $last_check) {
            swell_scales_compile_scss();
            file_put_contents($last_check_file, time());
            break;
        }
    }
}
add_action('init', 'swell_scales_watch_scss_changes');
