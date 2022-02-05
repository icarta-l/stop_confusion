<?php 
global $wpdb;
$query = "INSERT INTO " . $wpdb->prefix . "stop_confusion_theme_check (theme_slug, date_check, in_svn) VALUES(%s, NOW(), %d);"
?>
<div class="wrap">
	<h1><?= __('Stop Confusion', 'stop_confusion') ?></h1>
	<?php
	$themes = wp_get_themes();
	foreach ($themes as $key => $value) {
		$response = wp_remote_get('https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]="' . esc_attr($key) . '"');
	$code = wp_remote_retrieve_response_code( $response );
		?>
		<p href="#" class="theme-found"><?= esc_html($key) ?> : <?= esc_html($code) ?></p>
		<?php
		$found = ($code >= 200 && $code < 300) ? 1 : 0;
		$result = $wpdb->query($wpdb->prepare($query, $key, $found));
		var_dump($result);
	}
	?>
</div>