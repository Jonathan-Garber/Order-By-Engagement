<?php
global $wpdb;
	$table_name = $wpdb->prefix . 'obe_settings';
	$posts_table = $wpdb->prefix . 'posts';
	
	$custom_posts = $wpdb->get_results("SELECT DISTINCT post_type FROM $posts_table WHERE post_type != 'page' AND post_type != 'revision' AND post_type != 'post'");
	
	if (isset($_POST['save_settings'])){
	$rotation = $_POST['rotation_period'];
	$dead_period = $_POST['dead_period'];
	$dead_factor = $_POST['dead_factor'];
	$advance = $_POST['standard_advance'];
	$retreat = $_POST['standard_retreat'];
	$tracking = $_POST['tracking'];
	$timeout = $_POST['standard_timeout'];
	
	remove_obecron();
	switch ($rotation) {
		case 'daily':
			schedule_obecron('daily');
			break;
		case 'hourly':
			schedule_obecron('hourly');
			break;
		case 'twiced':
			schedule_obecron('twicedaily');
			break;
		}	
	
	$track = '';
		foreach ($tracking as $key=>$tr){
			$track .= $tr.',';
		}

			$wpdb->get_results("UPDATE $table_name SET 
			rotation_period='$rotation',
			deaden_period='$dead_period',
			deaden_factor='$dead_factor',
			tracking='$track',
			standard_advance='$advance',
			standard_retreat='$retreat',
			timeout='$timeout'
			WHERE id = 1");				
}
	
	
	
	
	$current_settings = $wpdb->get_results("SELECT * FROM $table_name");
	foreach ($current_settings as $cs){}
	$deaden_period = $cs->deaden_period;
	$deaden_factor = $cs->deaden_factor;
	$tracking = $cs->tracking;
	
	$tracking = explode(',',$tracking);
	
	$rotation_period = $cs->rotation_period;
	$standard_retreat = $cs->standard_retreat;
	$standard_advance = $cs->standard_advance;
	$standard_timeout = $cs->timeout;
	

$schedule = wp_get_schedule(obecron);

if ($schedule == 'twicedaily'){
	$schedule = 'twice daily';
}

$schedule = ucfirst($schedule);
	
?>
<h3>Current Rotation: <?php echo $schedule ?></h3>
<h4>Engage Settings</h4>
<form method="POST">
Rotation Period: <select name="rotation_period">

<option <?php if($rotation_period == 'hourly'){ echo 'selected="yes"'; } ?> value="hourly">Hourly</option>
<option <?php if($rotation_period == 'daily'){ echo 'selected="yes"'; } ?> value="daily">Daily</option>
<option <?php if($rotation_period == 'twiced'){ echo 'selected="yes"'; } ?> value="twiced">Twice Daily</option>
</select>

How often to check topics for popularity..<br/>

Dead Zone: <input type="text" name="dead_period" value="<?php echo $deaden_period ?>"><br/>
At what point level should a topic be considered Dead<br/>

Dead Factor: <input type="text" name="dead_factor" value="<?php echo $deaden_factor ?>"><br/>
How many points should be removed from a dead topic upon rotation<br/>

Default Retreat: <input type="text" name="standard_retreat" value="<?php echo $standard_retreat ?>"><br/>
How many points should be removed from a topic when its not dead during rotation <br/>

Default Advance: <input type="text" name="standard_advance" value="<?php echo $standard_advance ?>"><br/>
How many points should be added to a topic when its viewed<br/>

Default Timeout: <input type="text" name="standard_timeout" value="<?php echo $standard_timeout ?>"><br/>
How long before a refresh from user/ip will add points to an article.<br/>

<br/>
What Post Types to Track:<br/>
<select multiple="yes" name="tracking[]">
<?php 
foreach ($custom_posts as $cps){ 
$post_type = $cps->post_type;
?>
<option <?php if (in_array($post_type, $tracking)) echo 'selected="selected"'; ?>value="<?php echo $post_type ?>"><?php echo $post_type ?></option>
<?php
}
?>
<option <?php if (in_array('all', $tracking)) echo 'selected="selected"'; ?> value="all">Track All Post Types</option>
</select>
<br/>
<input type="submit" name="save_settings" value="Save Settings">