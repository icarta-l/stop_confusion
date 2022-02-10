<?php 
require_once str_replace("admin", "functions.php", __DIR__);

?>
<div class="wrap">
	<h1><?= __('Stop Confusion', 'stop_confusion') ?></h1>
	<table class="my-view">
		<tr>
			<th>ID</th>
			<th>Theme Slug</th>
			<th>Last Check</th>
			<th>In SVN</th>
		</tr>
	</table>
</div>