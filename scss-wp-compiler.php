<?php
/*
Plugin Name: Logical Design System
Plugin URI: https://github.com/michelediss/wp-logical-design-system
Description: Finally a logical design system: color palette & typographic scale generator + 50 google font pairings
Version: 1.0
Author: Michele Paolino
Author URI: https://michelepaolino.me
*/

// Includi la libreria SCSSPHP utilizzando il percorso corretto
require_once plugin_dir_path(__FILE__) . 'libs/scssphp/scss.inc.php';

use ScssPhp\ScssPhp\Compiler;

// Funzione per aggiungere il bottone "Compile SCSS" alla wpadminbar
function add_compile_button($wp_admin_bar) {
    // Controlla se l'utente ha i permessi necessari
    if (!current_user_can('manage_options')) {
        return;
    }

    // Definisci le proprietà del nuovo bottone
    $args = array(
        'id' => 'compile_scss', // ID del bottone
        'title' => 'Compile SCSS', // Titolo del bottone
        'href' => '#', // Usa JavaScript per gestire il click
        'meta' => array(
            'class' => 'compile-scss-class', // Classe CSS per lo styling
            'title' => 'Compile SCSS' // Tooltip title
        )
    );
    // Aggiungi il bottone alla wpadminbar
    $wp_admin_bar->add_node($args);
}
// Hook della funzione all'azione 'admin_bar_menu' con priorità alta (999)
add_action('admin_bar_menu', 'add_compile_button', 999);




function handle_scss_compilation_ajax() {
    check_ajax_referer('scss_compilation_nonce', 'security');

    $scss = new Compiler();

    // Definisci i percorsi SCSS
    $plugin_sass_path = plugin_dir_path(__FILE__) . 'scss/';
    $theme_sass_path = get_stylesheet_directory() . '/assets/scss/';
    $bootstrap_sass_path = $plugin_sass_path . 'bootstrap/scss/'; // Percorso di Bootstrap nel plugin

    // Verifica che i percorsi SCSS esistano
    if (!is_dir($theme_sass_path)) {
        wp_send_json_error('La directory SCSS del tema non esiste: ' . $theme_sass_path);
    }

    if (!is_dir($plugin_sass_path)) {
        wp_send_json_error('La directory SCSS del plugin non esiste: ' . $plugin_sass_path);
    }

    if (!is_dir($bootstrap_sass_path)) {
        wp_send_json_error('La directory Bootstrap SCSS non esiste: ' . $bootstrap_sass_path);
    }

    // Imposta i percorsi di importazione per SCSS: tema, plugin, Bootstrap
    $scss->setImportPaths(array($theme_sass_path, $plugin_sass_path, $bootstrap_sass_path));

    // Percorso al file main.scss nel plugin
    $main_scss_path = $plugin_sass_path . 'main.scss';

    // Verifica che il file main.scss esista
    if (!file_exists($main_scss_path)) {
        wp_send_json_error('Impossibile trovare il file main.scss: ' . $main_scss_path);
    }

    try {
        // Leggi e compila il contenuto di main.scss
        $main_scss = file_get_contents($main_scss_path);
        $compiled_css = $scss->compile($main_scss);

        // Percorso di destinazione per il CSS compilato
        $output_css_dir = get_stylesheet_directory() . '/assets/css/';
        $compiled_css_path = $output_css_dir . 'lds-style.css';

        // Crea la cartella se non esiste
        if (!is_dir($output_css_dir)) {
            mkdir($output_css_dir, 0755, true);
        }

        // Salva il CSS compilato nel percorso di destinazione
        if (file_put_contents($compiled_css_path, $compiled_css) === false) {
            wp_send_json_error('Impossibile scrivere il CSS compilato in: ' . $compiled_css_path);
        }

        // Imposta il formatter a 'Compressed' per minificare il CSS
        $scss->setFormatter('ScssPhp\ScssPhp\Formatter\Compressed');
        $minified_css = $scss->compile($main_scss);
        $minified_css_path = $output_css_dir . 'lds-style.min.css';

        // Salva il CSS minificato
        if (file_put_contents($minified_css_path, $minified_css) === false) {
            wp_send_json_error('Impossibile scrivere il CSS minificato in: ' . $minified_css_path);
        }

        wp_send_json_success('Compilazione completata con successo!');
    } catch (\ScssPhp\ScssPhp\Exception\CompilerException $e) {
        wp_send_json_error('Errore durante la compilazione SCSS: ' . $e->getMessage());
    } catch (\Exception $e) {
        wp_send_json_error('Errore generale: ' . $e->getMessage());
    }
}
add_action('wp_ajax_compile_scss', 'handle_scss_compilation_ajax');



// Funzione per enqueue del CSS compilato e minificato in WordPress
function swell_scales_enqueue_styles() {
    // **Modifica 4:** Percorso URL del file CSS minificato nella directory del tema
    $min_css_url = get_stylesheet_directory_uri() . '/assets/css/lds-style.min.css';

    // **Modifica 5:** Percorso fisico del file CSS minificato nella directory del tema
    $min_css_path = get_stylesheet_directory() . '/assets/css/lds-style.min.css';

    // Verifica che il file CSS minificato esista prima di enqueue
    if (file_exists($min_css_path)) {
        // Enqueue del file CSS minificato con handle 'logical-design-system-styles-min'
        wp_enqueue_style('logical-design-system-styles-min', $min_css_url, array(), filemtime($min_css_path));
    } else {
        // Logga un errore se il file CSS minificato non esiste
        error_log('Il file CSS minificato non esiste: ' . $min_css_path);
    }
}
// Hook della funzione all'azione 'wp_enqueue_scripts' per includere il file CSS nel frontend
add_action('wp_enqueue_scripts', 'swell_scales_enqueue_styles');


// Funzione di attivazione del plugin
function lds_plugin_activation() {
    // Percorso alla directory 'scss/input' del plugin
    $source_dir = plugin_dir_path(__FILE__) . 'scss/input/';

    // Percorso alla directory 'assets/scss' del tema attivo
    $destination_dir = get_stylesheet_directory() . '/assets/scss/';

    // Verifica che la directory sorgente esista
    if (!is_dir($source_dir)) {
        error_log('La directory sorgente non esiste: ' . $source_dir);
        return;
    }

    // Se la directory di destinazione non esiste, creala
    if (!is_dir($destination_dir)) {
        if (!mkdir($destination_dir, 0755, true)) {
            error_log('Impossibile creare la directory di destinazione: ' . $destination_dir);
            return;
        }
    }

    // Copia ricorsivamente il contenuto dalla sorgente alla destinazione senza sovrascrivere file esistenti
    recursive_copy($source_dir, $destination_dir);
}

// Funzione per copiare directory e file in modo ricorsivo senza sovrascrivere file esistenti
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

// Aggancia la funzione di attivazione all'hook 'register_activation_hook'
register_activation_hook(__FILE__, 'lds_plugin_activation');


// Funzione per enqueue del JavaScript per la gestione AJAX
function enqueue_scss_compilation_script() {
    // Enqueue del custom script
    wp_enqueue_script('scss_compilation_script', plugin_dir_url(__FILE__) . 'scss-compilation.js', array('jquery'), null, true);

    // Localizza lo script con i dati necessari
    wp_localize_script('scss_compilation_script', 'scss_compilation', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('scss_compilation_nonce')
    ));
}
// Hook per enqueue dello script nell'admin
add_action('admin_enqueue_scripts', 'enqueue_scss_compilation_script');

// Funzione per dequeue dei file CSS del tema padre e figlio
function dequeue_child_theme_styles() {
    wp_dequeue_style('logical-theme-style'); 
    wp_dequeue_style('logical-theme-child-style'); 

}
add_action('wp_enqueue_scripts', 'dequeue_child_theme_styles', 20);


?>
