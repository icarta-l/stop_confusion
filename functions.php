<?php

function check_all_themes() {
	global $wpdb;
	$themes = wp_get_themes();
	// non-existant theme for test
	$themes["kqdqszqdk"] = "value";
	array_walk($themes, 'check_theme');
	$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check";
	$result = $wpdb->get_results($retrieve_rows, ARRAY_A);
	echo "<pre>";
	var_dump($result);
	echo "</pre>";
}

function check_theme($value, $key) {
	global $wpdb;
	$response = wp_remote_get('https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]="' . esc_attr($key) . '"');
	$code = wp_remote_retrieve_response_code( $response );
	?>
	<p href="#" class="theme-found"><?= esc_html($key) ?> : <?= esc_html($code) ?></p>
	<?php
	$found = ($code === 200) ? 1 : 0;
	$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
	$is_in_database = $wpdb->query($wpdb->prepare($search_query, $key));
	if ($is_in_database === 1) {
		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET date_check = NOW(), in_svn = %d WHERE theme_slug = %s";
		$result = $wpdb->query($wpdb->prepare($update_query, $found, $key));
		return;
	}
	$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_theme_check (theme_slug, date_check, in_svn) VALUES(%s, NOW(), %d);";
	$result = $wpdb->query($wpdb->prepare($insert_query, $key, $found));
}