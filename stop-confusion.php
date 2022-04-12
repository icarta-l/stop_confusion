<?php
/**
 * Plugin Name: Stop Confusion
 * Author: Idan Carta-Lag
 * Text Domain: stop-confusion
 * Description: This plugin allows you to check your themes' presence in WordPress remote repository from the admin panel, and block unwanted theme updates to prevent security breach.
 * Version: 0.1
 */

require_once plugin_dir_path(__FILE__) . './admin/classes/DebugHelper.php';
require_once plugin_dir_path(__FILE__) . './admin/classes/CheckThemeSecurity.php';
require_once plugin_dir_path(__FILE__) . './admin/classes/Database.php';

function stop_confusion_custom_menu_page() {
    add_menu_page(
        __('Stop Confusion', 'stop-confusion'),
        __('Stop Confusion', 'stop-confusion'),
        'manage_options',
        plugin_dir_path(__FILE__) . 'admin/view.php'
    );
}
add_action('admin_menu', 'stop_confusion_custom_menu_page');

function stop_confusion_enqueue_scripts_and_styles($hook) {
    if ($hook === "stop_confusion/admin/view.php") {
        wp_enqueue_style('stop_confusion-style', plugin_dir_url( __FILE__ ) . '/admin/style.css');
        wp_enqueue_script('stop_confusion-view', plugin_dir_url( __FILE__ ) . '/admin/js/view.js', array('wp-api'));
        wp_localize_script( 'wp-api', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) );
    }
}
add_action('admin_enqueue_scripts', 'stop_confusion_enqueue_scripts_and_styles');

function stop_confusion_defer_js( $tag, $handle ) {
    $defer = [
        'stop_confusion-view'
    ];

    if ( in_array( $handle, $defer ) ) {
        $tag = str_replace( ' src', ' defer="defer" src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'stop_confusion_defer_js', 10, 2);

function stop_confusion_create_table() {
    global $wpdb;

    $wpdb->show_errors();
    $create_main_table = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'stop_confusion_theme_check(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    theme_slug VARCHAR(60) NOT NULL UNIQUE,
    date_check DATETIME NOT NULL,
    in_svn BOOLEAN NOT NULL,
    is_authorized BOOLEAN NOT NULL,
    INDEX in_theme_slug (theme_slug),
    INDEX in_date_check (date_check),
    PRIMARY KEY(id)
    )
    ENGINE=INNODB';
    $create_alert_table = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'stop_confusion_security_alerts(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    theme_slug VARCHAR(60) NOT NULL UNIQUE,
    date_check DATETIME NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (theme_slug)
    REFERENCES ' . $wpdb->prefix . 'stop_confusion_theme_check(theme_slug)
    ON DELETE CASCADE
    )
    ENGINE=INNODB';
    $create_last_check_table = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'stop_confusion_last_check(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    date_check DATETIME NOT NULL,
    PRIMARY KEY(id)
    )
    ENGINE=INNODB';
    $create_table = $wpdb->query($create_main_table);
    $create_secondary_table = $wpdb->query($create_alert_table);
    $create_tertiary_table = $wpdb->query($create_last_check_table);
    $wpdb->print_error();
}
register_activation_hook(__FILE__, 'stop_confusion_create_table');

function stop_confusion_register_rest_route() {
    register_rest_route('stop_confusion/v1', '/themes', array(
        array(
            "methods" => WP_REST_Server::READABLE,
            "callback" => 'stop_confusion_get_all_themes',
            "permission_callback" => function() {
                return current_user_can('administrator');
            }
        ),
        array(
            "methods" => WP_REST_Server::EDITABLE,
            "callback" => 'stop_confusion_update_theme_scan',
            "permission_callback" => function() {
                return current_user_can('administrator');
            }
        )
    ));
    register_rest_route('stop_confusion/v1', '/theme/authorization', array(
        array(
            "methods" => WP_REST_Server::EDITABLE,
            "callback" => 'stop_confusion_toggle_authorization_on_theme',
            "permission_callback" => function() {
                return current_user_can('administrator');
            }
        )
    ));
    register_rest_route('stop_confusion/v1', '/themes/threat', array(
        array(
            "methods" => WP_REST_Server::READABLE,
            "callback" => 'stop_confusion_print_security_alerts',
            "permission_callback" => function() {
                return current_user_can('administrator');
            }
        )
    ));
}
add_action('rest_api_init', 'stop_confusion_register_rest_route');

function stop_confusion_get_all_themes() {
    $data = (new \StopConfusion\Database())->getStopConfusionThemeCheck();
    return new WP_REST_Response($data, 200);
}

function stop_confusion_update_theme_scan() {
    $result = (new \StopConfusion\CheckThemeSecurity())->handleThemes();
    $rows = (new \StopConfusion\Database())->getStopConfusionThemeCheck();
    $data = [
        "security_threat" => $result,
        "rows" => $rows
    ];
    return new WP_REST_Response($data, 200);
}

function stop_confusion_toggle_authorization_on_theme(WP_REST_Request $request) {
    $data = json_decode($request->get_body());

    $database = new \StopConfusion\Database();
    $database->updateThemeAuthorizationStatus($data->authorized, $data->theme_slug);
    if ($database->securityAlertInDatabase($data->theme_slug) && $data->authorized === 0) {
        $database->deleteSecurityAlertFromDatabase($data->theme_slug);
    }
    $themes = $database->getStopConfusionThemeCheck();
    return new WP_REST_Response($themes, 200);
}

function stop_confusion_print_security_alerts() {
    $data = (new \StopConfusion\Database())->getStopConfusionSecurityAlerts();
    return new WP_REST_Response($data, 200);
}

function stop_confusion_filter_update_theme($value, $transient) {
    $authorized_themes = (new \StopConfusion\Database())->getAuthorizedThemes();
    $theme_slugs = [];
    foreach ($authorized_themes as $authorized_theme) {
        $theme_slugs[] = $authorized_theme['theme_slug'];
    }
    if (isset($value) && is_object($value)) {
        $slugs_with_updates = array_keys($value->response);
    }
    foreach ($slugs_with_updates as $slug) {
        if (in_array($slug, $theme_slugs)) {
            continue;
        }
        unset($value->response[$slug]);
    }
    return $value;
}
add_filter('site_transient_update_themes','stop_confusion_filter_update_theme', 10, 2);

function stop_confusion_handle_last_check() {
    $database = new \StopConfusion\Database();
    $last_check = $database->getLastCheck();
    if ( !empty($last_check) ) {
        $last_check = new DateTime($last_check[0]['date_check']);
        $now = new DateTime();
        $interval = (int) $now->diff($last_check)->format('%d');
    }
    if (empty($last_check) || $interval >= 7) {
        (new \StopConfusion\CheckThemeSecurity)->handleThemes();
    }
}
add_action('admin_init', 'stop_confusion_handle_last_check');