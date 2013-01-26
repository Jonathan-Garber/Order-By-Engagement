<?php

function modify_single_engagement($post_id,$amount,$operator,$key = 'current_engagement'){
$old_meta = get_post_meta($post_id, $key, true);
$current = get_post_meta($post_id, 'current_engagement', true);	
$max = obe_get_max($post_id);
$timestamp = time();
	if ($old_meta != ''){
		switch($operator){
			case '+':
				$new_meta = $old_meta + $amount;
				break;
			case '-':
				$new_meta = $old_meta - $amount;
				break;
			case 'x':
				$new_meta = $old_meta * $amount;
				break;
		}
		update_post_meta($post_id, $key, $new_meta);		
		update_post_meta($post_id, 'timestamp', $timestamp);	
	}else{
		add_post_meta($post_id, $key, $amount);
		add_post_meta($post_id, 'timestamp', $timestamp);	
	}
}

function modify_engagement($post_id,$amount,$operator,$key = 'current_engagement'){

	$obe_settings = get_option('obe_settings');
	$obe_ips = get_option('obe_ips');	
	$post_type = get_post_type($post_id);	
	$referer = $_SERVER['HTTP_REFERER'];
	$url = site_url();
	$pos = strpos($referer, $url);
	$ip = obe_getIP();
	$date = date('Y-m-d');
	$timestamp = time();
	$current = get_post_meta($post_id, 'current_engagement', true);	
	$max = obe_get_max($post_id);
	
	switch($operator){
		case '+':
			$new_meta = $current + $amount;
			break;
		case '-':
			$new_meta = $current - $amount;
			break;
		case 'x':
			$new_meta = $current * $amount;
			break;
	}
	
	//get advance and timeout values from settings
	$standard_advance = $obe_settings[advance];
	$timeout = $obe_settings[throttle];
	$tracking = $obe_settings[tracking];
	
	if ($obe_ips[$ip]){
		$ip_timestamp = $obe_ips[$ip][timestamp];
	}else{
		//build array for this new ip
		$obe_ips[$ip] = array(
					'post_id' => $post_id,
					'post_type' => $post_type,
					'date' => $date,
					'timestamp' => $timestamp
					);
		update_option('obe_ips', $obe_ips);
		$ip_timestamp = $timestamp;
	}
	
	if ($ip_timestamp){
		$idle_time = $timestamp - $ip_timestamp;
		
		if ($idle_time > $timeout){
			$obe_ips[$ip][timestamp] = $timestamp;
			$obe_ips[$ip][post_type] = $post_type;
			$obe_ips[$ip][date] = $date;
			update_option('obe_ips', $obe_ips);	
			
				if (in_array($post_type, $tracking) || in_array('all', $tracking)){				
					update_post_meta($post_id, $key, $new_meta);		
					update_post_meta($post_id, 'timestamp', $timestamp);
				
				if ($max != ''){
					if ($max <= $current){
						$new_max = $current + $standard_advance;
						update_post_meta($post_id, 'max_engagement', $new_max);
					}
				}else{
					update_post_meta($post_id, 'max_engagement', $current);
				}
				
				$new_current = get_post_meta($post_id, 'current_engagement', true);	
					if ($new_current < 0){
						update_post_meta($post_id, $key, 0);
					}
				}
		}
	}
}
	
function obe_get_max($post_id){
	$current_max = get_post_meta($post_id, 'max_engagement', true);
	return $current_max;
}

/*
this function ensures we are viewing a single page or post...
then using modify_engagement it will modify the page/post engagement if the current IP is not throttled for spam protection
*/
function obe_is_single() {
	global $wpdb;
	$post_id = get_the_Id();

		if(is_single()) {
			$obe_settings = get_option('obe_settings');			
			$standard_advance = $obe_settings[advance];
			modify_engagement($post_id,$standard_advance,'+');
		}
}

// Add required meta for Ajax trigger method
function obe_add_meta() {

	if ( is_single() ) {

		$post_id = get_the_Id();						
		$path = plugins_url('ajax.js', __FILE__);
		$plugin_url = plugins_url('process.php', __FILE__);

		// Print additional items to header
		echo "\n<!-- OBE -->\n";
		echo "<meta name=\"post_id\" value=\"$post_id\">\n";
		echo "<meta name=\"plugin_url\" value=\"$plugin_url\">\n";
		echo "<!-- /OBE -->\n\n";

		// Add OBE Ajax script to footer
		wp_register_script( "obe-ajax", $path, "jquery", NULL, TRUE );
		wp_enqueue_script("obe-ajax");
			
	}
}

//help us obecron kenobi your our only hope...
function schedule_obecron($when) {
	wp_schedule_event(time(), $when, 'obecron');
}

function remove_obecron() {
	wp_clear_scheduled_hook('obecron');
}


//our obecron
function ocf() {

	global $wpdb;
	$posts_table = $wpdb->prefix . "posts";
	
	$obe_settings = get_option('obe_settings');
	
	$deaden_period =  $obe_settings[dead_zone];
	$deaden_factor = $obe_settings[dead_factor];
	$tracking = $obe_settings[tracking];
	$rotation_period = $obe_settings[rotation];
	$standard_retreat = $obe_settings[subtract];
	$standard_advance = $obe_settings[advance];

	$current_posts = $wpdb->get_results("SELECT * FROM $posts_table");
	
	foreach ($current_posts as $cp){
		$post_id = $cp->ID;
		$post_type = $cp->post_type;
		$timestamp = get_post_meta($post_id, 'timestamp', true);
		$engagement = get_post_meta($post_id, 'current_engagement', true);
		if ($engagement <= $deadzone) {
			$dead = 'yes';
		}

		$current_time = time();
		$current = get_post_meta($post_id, 'current_engagement', true);	

		if ($timestamp != '') {
			if ($current_time > $timestamp){ 
				if ($dead == 'yes') {
					$new_meta = $current - $deaden_factor;
					update_post_meta($post_id, 'current_engagement', $new_meta);
					update_post_meta($post_id, 'timestamp', $current_time);							
				}
				else {
					$new_meta = $current - $standard_retreat;
					update_post_meta($post_id, 'current_engagement', $new_meta);
					update_post_meta($post_id, 'timestamp', $current_time);							
				}

				$engagement = get_post_meta($post_id, 'current_engagement', true);
				if ($engagement <= 0) {	
					update_post_meta($post_id, 'current_engagement', '0');
				}
			}
		}
	}

}


function add_engagement($post_ID) {

	global $wpdb;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return $post_id;
	}
	$post_type = get_post_type($post_ID);
	$obe_settings = get_option('obe_settings');
	$tracking = $obe_settings[tracking];
	if (in_array($post_type,$tracking) || in_array('all', $tracking)){
		$engagement = get_post_meta($post_id, 'current_engagement', true);
		if ($engagement == ''){
			add_post_meta($post_ID, 'current_engagement', 0, true);
		}
	}

}

function obe_update() {

	$last_known_version = get_option('obe_version');
	$current_version = obe_get_version();	
	
	//if versions are different we update version numbers and push update routines
	if ( $last_known_version != $current_version ) {
		update_option( "obe_version", $current_version );
		obe_update_routine();
	}
	else {
		//version number was not different or did not even exist so we update just the version number variables
		update_option( "obe_version", $current_version );
	}
	
}

function obe_getIP() { 
	$ip; 
	if (getenv("HTTP_CLIENT_IP")) 
	$ip = getenv("HTTP_CLIENT_IP"); 
	else if(getenv("HTTP_X_FORWARDED_FOR")) 
	$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if(getenv("REMOTE_ADDR")) 
	$ip = getenv("REMOTE_ADDR"); 
	else 
	$ip = "UNKNOWN";
	return $ip;
}