<?php
/**
 * Plugin Name: Stop Confusion
 * Author: Idan Carta
 * Text Domain: stop_confusion
 * Update URI: false
 */

require plugin_dir_path(__FILE__) . './admin/classes/DebugHelper.php';

function stop_confusion_custom_menu_page() {
    add_menu_page(
        __('Stop Confusion', 'stop_confusion'),
        __('Stop Confusion', 'stop_confusion'),
        'manage_options',
        plugin_dir_path(__FILE__) . 'admin/view.php'
    );
}
add_action('admin_menu', 'stop_confusion_custom_menu_page');

function stop_confusion_enqueue_scripts_and_styles($hook) {
    wp_enqueue_style('stop_confusion-style', plugins_url( '/admin/style.css', __FILE__ ));
    if ($hook === "themes.php") {
        wp_enqueue_script('stop_confusion-theme', plugin_dir_url( __FILE__ ) . '/admin/js/theme.js', array('jquery'));   
    }
}
add_action('admin_enqueue_scripts', 'stop_confusion_enqueue_scripts_and_styles');

function defer_js( $tag, $handle ) {
    $defer = [
        'stop_confusion-theme',
    ];

    if ( in_array( $handle, $defer ) ) {
        $tag = str_replace( ' src', ' defer="defer" src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'defer_js', 10, 2);

function stop_confusion_admin_classes() {
    $debugHelper = new DebugHelper("classes.log");
    $debugHelper->delete();
    $debugHelper->debug("Updates");
    $array = get_site_transient( 'update_themes' );
    $debugHelper->debug($array);
}
add_action("wp", "stop_confusion_admin_classes");

// add_filter( 'site_transient_update_themes', 'remove_update_themes' );
// function remove_update_themes( $value ) {
//     return null;
// }

function stop_confusion_create_table() {
    global $wpdb;
    $query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'stop_confusion_theme_check(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    theme_slug VARCHAR(60) NOT NULL,
    date_check DATETIME NOT NULL,
    in_svn BOOLEAN NOT NULL,
    PRIMARY KEY(id)
    )
    ENGINE=INNODB';
    $create_table = $wpdb->query($query);
    $wpdb->print_error();
}
register_activation_hook(__FILE__, 'stop_confusion_create_table');
