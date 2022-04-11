<?php
namespace StopConfusion;

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

		$themes = \wp_get_themes();
		$this->checkForDeletedThemes($themes);

		\array_walk($themes, [$this, 'handleTheme']);

		$this->handleLastCheck();

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

	private function handleLastCheck() : void
	{
		if ($this->database->lastCheckExists() === 0) {
			$this->database->createStopConfusionLastCheck();
		} else {
			$this->database->updateStopConfusionLastCheck();
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
		$url = 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]="' . \rawurlencode($slug) . '"';

		$response = \wp_remote_get($url);
		$code = \wp_remote_retrieve_response_code( $response );

		return ($code === 200) ? 1 : 0;
	}

	private function handleSecurityThreats(int $response, string $slug, int $had_svn) : void
	{
		if ($this->database->securityAlertInDatabase($slug) === 1) {
			$this->database->updateStopConfusionSecurityAlert($slug);
		} else {
			if ($this->checkForSecurityThreat($slug, $response, $had_svn) === false) {
				return;
			}
			$this->database->createStopConfusionSecurityAlert($slug);
		}
		$this->database->updateThemeAuthorizationStatus(1, $slug);
		$this->security_threat = true;
	}

	private function checkForDeletedThemes(array $themes) : void
	{
		$themes_in_database = $this->database->getStopConfusionThemeCheck();
		$slugs = \array_keys($themes);
		foreach ($themes_in_database as $theme) {
			if (\in_array($theme['theme_slug'], $slugs)) {
				continue;
			}
			$this->database->deleteThemeFromDatabase($theme['theme_slug']);
		}
	}
}