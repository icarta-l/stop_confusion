<?php

/**
 * Database
 */
class Database
{
	/**
	 * Theme check functions
	 */
	public function update_stop_confusion_theme_check(string $slug, int $found) : void 
	{
		global $wpdb;

		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET date_check = NOW(), in_svn = %d WHERE theme_slug = %s";
		$wpdb->query($wpdb->prepare($update_query, $found, $slug));
	}

	public function create_stop_confusion_theme_check(string $slug, int $found) : void {
		global $wpdb;

		$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_theme_check (theme_slug, date_check, in_svn, is_blocked) VALUES(%s, NOW(), %d, %d);";
		$wpdb->query($wpdb->prepare($insert_query, $slug, $found, $this->get_blocked_value($found)));
	}

	public function get_stop_confusion_theme_check() : array {
		global $wpdb;

		$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check";
		return $wpdb->get_results($retrieve_rows, ARRAY_A);
	}

	private function get_stop_confusion_last_check(string $slug) : string {
		global $wpdb;

		$retrieve_rows = "SELECT date_check FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
		return $wpdb->get_col($wpdb->prepare($retrieve_rows, $slug))[0];
	}

	public function check_if_theme_had_svn(string $slug) : int {
		global $wpdb;

		$retrieve_rows = "SELECT in_svn FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
		return $wpdb->get_col($wpdb->prepare($retrieve_rows, $slug))[0];
	}

	public function is_in_database(string $slug) : int {
		global $wpdb;

		$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
		return $wpdb->query($wpdb->prepare($search_query, $slug));
	}

	public function update_theme_blocked_status(int $blocked, string $slug) : void {
		global $wpdb;

		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET is_blocked = %d WHERE theme_slug = %s";
		$blocked = ($blocked === 0) ? 1 : 0;
		$wpdb->query($wpdb->prepare($update_query, $blocked, $slug));
	}

	public function get_blocked_themes() : array {
		global $wpdb;

		$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE is_blocked = 1";
		return $wpdb->get_results($retrieve_rows, ARRAY_A);
	}

	private function get_blocked_value(int $found) : int {
		return ($found === 1) ? 0 : 1;
	}

	/**
	 * Security alert functions
	 */
	public function create_stop_confusion_security_alert(string $slug) : void {
		global $wpdb;

		$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_security_alerts (theme_slug, date_check) VALUES(%s, %s);";
		$wpdb->query($wpdb->prepare($insert_query, $slug, $this->get_stop_confusion_last_check($slug)));
	}

	public function get_stop_confusion_security_alerts() : array {
		global $wpdb;

		$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_security_alerts ORDER BY date_check DESC";
		return $wpdb->get_results($retrieve_rows, ARRAY_A);
	}

	public function security_alert_in_database(string $slug) : int {
		global $wpdb;

		$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_security_alerts WHERE theme_slug = %s";
		return $wpdb->query($wpdb->prepare($search_query, $slug));
	}

	public function update_stop_confusion_security_alert(string $slug) : void 
	{
		global $wpdb;

		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_security_alerts SET date_check = NOW() WHERE theme_slug = %s";
		$wpdb->query($wpdb->prepare($update_query, $slug));
	}
}