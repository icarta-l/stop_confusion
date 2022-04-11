<?php

namespace StopConfusion;
/**
 * Database
 */
class Database
{
	/**
	 * Theme check functions
	 */
	public function updateStopConfusionThemeCheck(string $slug, int $found) : void 
	{
		global $wpdb;

		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET date_check = NOW(), in_svn = %d WHERE theme_slug = %s";
		$wpdb->query($wpdb->prepare($update_query, $found, $slug));
	}

	public function createStopConfusionThemeCheck(string $slug, int $found) : void {
		global $wpdb;

		$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_theme_check (theme_slug, date_check, in_svn, is_authorized) VALUES(%s, NOW(), %d, %d);";
		$authorized = 0;
		if ($found === 1) {
			$authorized = 1;
		}
		$wpdb->query($wpdb->prepare($insert_query, $slug, $found, $authorized));
	}

	public function getStopConfusionThemeCheck() : array {
		global $wpdb;

		$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check";
		return $wpdb->get_results($retrieve_rows, ARRAY_A);
	}

	private function getStopConfusionLastCheck(string $slug) : string {
		global $wpdb;

		$retrieve_rows = "SELECT date_check FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
		return $wpdb->get_col($wpdb->prepare($retrieve_rows, $slug))[0];
	}

	public function checkIfThemeHadSvn(string $slug) : int {
		global $wpdb;

		$retrieve_rows = "SELECT in_svn FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
		return $wpdb->get_col($wpdb->prepare($retrieve_rows, $slug))[0];
	}

	public function isInDatabase(string $slug) : int {
		global $wpdb;

		$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
		return $wpdb->query($wpdb->prepare($search_query, $slug));
	}

	public function updateThemeAuthorizationStatus(int $authorized, string $slug) : void {
		global $wpdb;

		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_theme_check SET is_authorized = %d WHERE theme_slug = %s";
		$authorized = ($authorized === 0) ? 1 : 0;
		$wpdb->query($wpdb->prepare($update_query, $authorized, $slug));
	}

	public function getAuthorizedThemes() : array {
		global $wpdb;

		$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE is_authorized = 1";
		return $wpdb->get_results($retrieve_rows, ARRAY_A);
	}

	public function deleteThemeFromDatabase(string $slug) : void
	{
		global $wpdb;

		$delete_query = "DELETE FROM " . $wpdb->prefix . "stop_confusion_theme_check WHERE theme_slug = %s";
		$wpdb->query($wpdb->prepare($delete_query, $slug));
	}

	/**
	 * Security alert functions
	 */
	public function createStopConfusionSecurityAlert(string $slug) : void {
		global $wpdb;

		$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_security_alerts (theme_slug, date_check) VALUES(%s, %s);";
		$wpdb->query($wpdb->prepare($insert_query, $slug, $this->getStopConfusionLastCheck($slug)));
	}

	public function getStopConfusionSecurityAlerts() : array {
		global $wpdb;

		$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_security_alerts ORDER BY date_check DESC";
		return $wpdb->get_results($retrieve_rows, ARRAY_A);
	}

	public function securityAlertInDatabase(string $slug) : int {
		global $wpdb;

		$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_security_alerts WHERE theme_slug = %s";
		return $wpdb->query($wpdb->prepare($search_query, $slug));
	}

	public function updateStopConfusionSecurityAlert(string $slug) : void 
	{
		global $wpdb;

		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_security_alerts SET date_check = NOW() WHERE theme_slug = %s";
		$wpdb->query($wpdb->prepare($update_query, $slug));
	}

	public function deleteSecurityAlertFromDatabase(string $slug) : void
	{
		global $wpdb;

		$delete_query = "DELETE FROM " . $wpdb->prefix . "stop_confusion_security_alerts WHERE theme_slug = %s";
		$wpdb->query($wpdb->prepare($delete_query, $slug));
	}

	/**
	 * Last check functions
	 */
	public function updateStopConfusionLastCheck() : void 
	{
		global $wpdb;

		$update_query = "UPDATE " . $wpdb->prefix . "stop_confusion_last_check SET date_check = NOW()";
		$wpdb->query($update_query);
	}

	public function createStopConfusionLastCheck() : void {
		global $wpdb;

		$insert_query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_last_check (date_check) VALUES(NOW());";
		$wpdb->query($insert_query);
	}

	public function lastCheckExists() : int {
		global $wpdb;

		$search_query = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_last_check";
		return $wpdb->query($search_query);
	}

	public function getLastCheck() : array {
		global $wpdb;

		$retrieve_rows = "SELECT * FROM " . $wpdb->prefix . "stop_confusion_last_check";
		return $wpdb->get_results($retrieve_rows, ARRAY_A);
	}

}