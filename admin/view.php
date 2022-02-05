<?php 
require_once str_replace("admin", "functions.php", __DIR__);

global $wpdb;
?>
<div class="wrap">
	<h1><?= __('Stop Confusion', 'stop_confusion') ?></h1>
	<?php
	check_all_themes();
	?>
</div>