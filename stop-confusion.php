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