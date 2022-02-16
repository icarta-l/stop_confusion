<?php

require_once 'Database.php';

/**
 * Check for theme security
 */
class CheckThemeSecurity
{
	private bool $security_threat = false;

	public function __construct()
	{
		$this->database = new Database();
	}
	
	public function handle_themes() : bool 
	{
		global $wpdb;

		$themes = wp_get_themes();

		array_walk($themes, [$this, 'handle_theme']);

		return $this->security_threat;
	}

	private function handle_theme(object $value, string $slug) : void 
	{
		global $wpdb;

		$response = $this->check_wordpress_remote_repository($slug);

		if ($this->database->is_in_database($slug) === 1) {
			$had_svn = $this->database->check_if_theme_had_svn($slug);
			$this->database->update_stop_confusion_theme_check($slug, $response);
			$this->handle_security_threats($response, $slug, $had_svn);
		} else {
			$this->database->create_stop_confusion_theme_check($slug, $response);
		}
	}

	private function check_for_security_threat(string $slug, int $response, int $had_svn) : bool 
	{
		if ($response !== 1) {
			return false;
		}
		if ($had_svn === 0) {
			return true;
		} else {
			return false;
		}
	}

	private function check_wordpress_remote_repository(string $slug) : int 
	{
		$url = 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]="' . rawurlencode($slug) . '"';

		$response = wp_remote_get($url);
		$code = wp_remote_retrieve_response_code( $response );

		return ($code === 200) ? 1 : 0;
	}

	private function handle_security_threats(int $response, string $slug, int $had_svn)
	{
		if ($this->check_for_security_threat($slug, $response, $had_svn) === false) {
			return;
		}
		if ($this->database->security_alert_in_database($slug) === 1) {
			$this->database->update_stop_confusion_security_alert($slug);
		} else {
			$this->database->create_stop_confusion_security_alert($slug);
		}
		$this->security_threat = true;
	}
}