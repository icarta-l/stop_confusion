<?php 
require_once str_replace("admin", "functions.php", __DIR__);

?>
<div class="wrap">
	<h1><?php _e('Stop Confusion', 'stop-confusion') ?></h1>
	<p>This plugin allows you to check your themes' presence in WordPress remote repository from the admin panel, and block unwanted theme updates to prevent security breach.</p>
	<?php
	?>
	<div class="table-wrapper">
		<table class="my-view">
			<tr>
				<th>ID</th>
				<th>Theme Slug</th>
				<th>Last Check</th>
				<th>In SVN</th>
				<th>Authorized</th>
			</tr>
		</table>
	</div>
	<div class="update-wrapper">
		<a href="#" class="update-action">Update</a>
	</div>
	<div class="security-alerts"></div>
</div>