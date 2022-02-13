<?php

function handle_themes() : void {
	global $wpdb;

	$themes = wp_get_themes();

	array_walk($themes, 'handle_theme');
}

function handle_theme($value, $key) : void {
	global $wpdb;

	$response = check_wordpress_remote_repository($key);

	if (is_in_database($key) === 1) {
		update_stop_confusion_theme_check($key, $response);
	} else {
		create_stop_confusion_theme_check($key, $response);
	}
}

function get_blocked_value(int $found) : int {
	return ($found === 1) ? 0 : 1;
}

/**
 * Database functions
 * 
 */

function update_stop_confusion_theme_check($slug, $found) : void {
	global $wpdb;
	
	$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET date_check = NOW(), in_svn = %d WHERE theme_slug = %s";
	$wpdb->query($wpdb->prepare($update_query, $found, $slug));
}

function create_stop_confusion_theme_check($slug, $found) : void {
	global $wpdb;
	
	$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_theme_check (theme_slug, date_check, in_svn, is_blocked) VALUES(%s, NOW(), %d, %d);";
	$wpdb->query($wpdb->prepare($insert_query, $slug, $found, get_blocked_value($found)));
}

function get_stop_confusion_theme_check() : array {
	global $wpdb;

	$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check";
	return $wpdb->get_results($retrieve_rows, ARRAY_A);
}

function is_in_database($slug) : int {
	global $wpdb;

	$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
	return $wpdb->query($wpdb->prepare($search_query, $slug));
}

function update_theme_blocked_status(int $blocked, string $slug) : void {
	global $wpdb;

	$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET is_blocked = %d WHERE theme_slug = %s";
	$blocked = ($blocked === 0) ? 1 : 0;
	$wpdb->query($wpdb->prepare($update_query, $blocked, $slug));
}

function get_blocked_themes() : array {
	global $wpdb;

	$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE is_blocked = 1";
	return $wpdb->get_results($retrieve_rows, ARRAY_A);
}

/**
 * External call
 * 
 */

function check_wordpress_remote_repository($key) : int {
	$url = 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]="' . rawurlencode($key) . '"';

	$response = wp_remote_get($url);
	$code = wp_remote_retrieve_response_code( $response );

	return ($code === 200) ? 1 : 0;
}

/**
 * Debug functions
 * 
 */

function print_found_theme($key, $code) : void { ?>

	<p href="#" class="theme-found"><?= esc_html($key) ?> : <?= esc_html($code) ?></p>

	<?php
}

function print_stop_confusion_theme_check() : void {
	echo "<pre>";
	var_dump(get_stop_confusion_theme_check());
	echo "</pre>";
}