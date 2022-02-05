<?php
/**
 * Plugin Name: Stop Confusion
 * Author: Idan Carta
 * Text Domain: stop_confusion
 */

function stop_confusion_custom_menu_page() {
    add_menu_page(
        __('Stop Confusion', 'stop_confusion'),
        __('Stop Confusion', 'stop_confusion'),
        'manage_options',
        plugin_dir_path(__FILE__) . 'admin/view.php'
    );
}
add_action('admin_menu', 'stop_confusion_custom_menu_page');

function stop_confusion_enqueue_styles() {
    wp_enqueue_style('stop_confusion-style', plugins_url( '/admin/style.css', __FILE__ ));
}
add_action('admin_enqueue_scripts', 'stop_confusion_enqueue_styles');

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