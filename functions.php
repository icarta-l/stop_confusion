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

/**
 * Database functions
 * 
 */

function update_stop_confusion_theme_check($key, $found) : void {
	global $wpdb;
	
	$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET date_check = NOW(), in_svn = %d WHERE theme_slug = %s";
	$wpdb->query($wpdb->prepare($update_query, $found, $key));
}

function create_stop_confusion_theme_check($key, $found) : void {
	global $wpdb;
	
	$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_theme_check (theme_slug, date_check, in_svn, is_blocked) VALUES(%s, NOW(), %d, %d);";
	$wpdb->query($wpdb->prepare($insert_query, $key, $found, $found));
}

function get_stop_confusion_theme_check() : array {
	global $wpdb;

	$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check";
	return $wpdb->get_results($retrieve_rows, ARRAY_A);
}

function is_in_database($key) : int {
	global $wpdb;

	$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
	return $wpdb->query($wpdb->prepare($search_query, $key));
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