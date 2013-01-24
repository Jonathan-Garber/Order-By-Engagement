<?php
global $wpdb;
	$posts_table = $wpdb->prefix . 'posts';
	
	$custom_posts = $wpdb->get_results("SELECT DISTINCT post_type FROM $posts_table WHERE post_type != 'revision'");
	
	if (isset($_POST['save_settings'])){
	$rotation = $_POST['rotation_period'];
	$dead_period = $_POST['dead_period'];
	$dead_factor = $_POST['dead_factor'];
	$advance = $_POST['standard_advance'];
	$retreat = $_POST['standard_retreat'];
	$tracking = $_POST['tracking'];
	$timeout = $_POST['standard_timeout'];
	
	//new PHP or AJAX Setting for triggering engagement
	$trigger = $_POST['trigger'];
	
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
			//build default settings array
		$new_settings = array(
			'rotation' => $rotation,
			'dead_zone' => $dead_period,
			'dead_factor' => $dead_factor,
			'advance' => $advance,
			'subtract' => $retreat,
			'tracking' => $tracking,
			'throttle' => $timeout,
			'trigger' =>  $trigger
			);
		//update settings option with new default settings...
		update_option('obe_settings', $new_settings);
		$resp = 'Settings have been saved.';
		
}
	
	
	
	$obe_settings = get_option('obe_settings');

	$deaden_period = $obe_settings[dead_zone];	
	$deaden_factor = $obe_settings[dead_factor];
	$tracking = $obe_settings[tracking];	
	$rotation_period = $obe_settings[rotation];
	$standard_retreat = $obe_settings[subtract];
	$standard_advance = $obe_settings[advance];
	$standard_timeout = $obe_settings[throttle];
	$trigger = $obe_settings[trigger];
	

$schedule = wp_get_schedule(obecron);

if ($schedule == 'twicedaily'){
	$schedule = 'twice daily';
}

$schedule = ucfirst($schedule);
$last_known_version = get_option('obe_version');	
?>
<div class="wrap">
<h3>Current Rotation: <?php echo $schedule ?></h3>
<h4>Engage Settings</h4>
<form method="POST">
Trigger Method: <select name="trigger">
<option <?php if($trigger == 'ajax'){ echo 'selected="yes"'; } ?> value="ajax">Ajax</option>
<option <?php if($trigger == 'php'){ echo 'selected="yes"'; } ?> value="php">PHP</option>
</select><br/>

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
<br/><br/>
<?php if ($resp) { ?>
<strong>Settings Saved</strong><br/><br/>
<?php } ?>
<small>Plugin Version: <?php echo $last_known_version ?></small>
</div>