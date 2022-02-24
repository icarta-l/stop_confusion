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
	
	public function handleThemes() : bool 
	{
		global $wpdb;

		$themes = wp_get_themes();

		array_walk($themes, [$this, 'handleTheme']);

		return $this->security_threat;
	}

	private function handleTheme(object $value, string $slug) : void 
	{
		global $wpdb;

		$response = $this->checkWordpressRemoteRepository($slug);

		if ($this->database->isInDatabase($slug) === 1) {
			$had_svn = $this->database->checkIfThemeHadSvn($slug);
			$this->database->updateStopConfusionThemeCheck($slug, $response);
			$this->handleSecurityThreats($response, $slug, $had_svn);
		} else {
			$this->database->createStopConfusionThemeCheck($slug, $response);
		}
	}

	private function checkForSecurityThreat(string $slug, int $response, int $had_svn) : bool 
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

	private function checkWordpressRemoteRepository(string $slug) : int 
	{
		$url = 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]="' . rawurlencode($slug) . '"';

		$response = wp_remote_get($url);
		$code = wp_remote_retrieve_response_code( $response );

		return ($code === 200) ? 1 : 0;
	}

	private function handleSecurityThreats(int $response, string $slug, int $had_svn)
	{
		if ($this->checkForSecurityThreat($slug, $response, $had_svn) === false) {
			return;
		}
		if ($this->database->securityAlertInDatabase($slug) === 1) {
			$this->database->updateStopConfusionSecurityAlert($slug);
		} else {
			$this->database->createStopConfusionSecurityAlert($slug);
		}
		$this->security_threat = true;
	}
}