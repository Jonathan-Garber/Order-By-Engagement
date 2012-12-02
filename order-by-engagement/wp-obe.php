<?php

/*
	Plugin Name: Order By Engagement
	Description: 
	Version: 1.0.2
	Author: TechStudio
*/

function obe_installer() {
	global $wpdb;
require_once("functions/main/install.php");
}//END INSTALLER

function obe_uninstall() {
	global $wpdb;
require_once("functions/main/uninstall.php");
} //END

register_activation_hook( __FILE__, 'obe_installer' );
register_deactivation_hook( __FILE__, 'obe_uninstall' );
add_action('admin_menu', 'obe_plugin_menu');

function obe_plugin_menu() {
	add_menu_page('Engagement', 'Engagement', 8, __FILE__, obe_sub_menu);
	add_submenu_page(__FILE__, 'Engagement Settings', 'Engagement Settings', 8, 'obe_settings', obe_sub_menu_settings);	
} //END Plugin Menu


function obe_sub_menu_settings(){
include 'functions/obe-settings.php';
}
function obe_sub_menu(){
include 'functions/obe-posts.php';
}


function get_engaged_posts($post_type,$orderby,$order){
	global $wpdb;
	$table_name = $wpdb->prefix . "obe_records";
	
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$query = query_posts(array(
	'post_type' => $post_type,
	'orderby' => 'meta_value_num',
	'meta_key' => $orderby,
	'order' => $order,
	'paged' => $paged
	));
}


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
	global $wpdb;
	$post_id = get_the_Id();
	$post_type = get_post_type($post_id);
	$table_ips = $wpdb->prefix . "obe_ips";
	$table_name = $wpdb->prefix . "obe_settings";
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
	
if ($current_ips = $wpdb->get_results("SELECT * FROM $table_ips WHERE ip_address = '$ip'")){
	foreach ($current_ips as $ip){}
	$ip_timestamp = $ip->timestamp;
	$id = $ip->id;
}else{
	$ip_timestamp = time();
	$insert = "INSERT INTO " . $table_ips . " 
	(
	ip_address,
	post_id,
	post_type,
	date,
	timestamp
	) 
	VALUES (
	'" . $wpdb->escape($ip) . "',
	'" . $wpdb->escape($post_id) . "',
	'" . $wpdb->escape($post_type) . "',
	'" . $wpdb->escape($date) . "',
	'" . $wpdb->escape($ip_timestamp) . "'
	)";
	$results = $wpdb->query( $insert );
}

	$current_settings = $wpdb->get_results("SELECT * FROM $table_name");
	foreach ($current_settings as $cs){}
	$standard_advance = $cs->standard_advance;
	$timeout = $cs->timeout;
	$idle_time = $timestamp - $ip_timestamp;
          
	if ($idle_time > $timeout){
		//since time has passed we now update the IP timestamp for this user with the newest timestamp
		$wpdb->get_results("UPDATE $table_ips SET 
		timestamp='$timestamp',
		post_type='$post_type',
		date='$date'
		WHERE id = $id");		
		if ($pos === false){
			if ($current_tracking = $wpdb->get_results("SELECT tracking FROM $table_name", ARRAY_A)){
				foreach ($current_tracking as $track){
					foreach ($track as $t){
						$tracking = explode(',',$t);
							if (in_array($post_type,$tracking) || in_array('all', $tracking)){
								update_post_meta($post_id, $key, $new_meta);		
								update_post_meta($post_id, 'timestamp', $timestamp);
								if ($max <= $current){
								$new_max = $current + $standard_advance;
								update_post_meta($post_id, 'max_engagement', $new_max);
								}
								$new_current = get_post_meta($post_id, 'current_engagement', true);	
									if ($new_current < 0){
										update_post_meta($post_id, $key, 0);
									}
							}
						}
					}	
				}
			}
		}else{
			//echo "SPAMMER";
		}
	}
	
function obe_get_max($post_id){
$current_max = get_post_meta($post_id, 'max_engagement', true);
return $current_max;
}

add_action('wp', 'obe_is_single');

 function obe_is_single() {
	global $wpdb;
	$post_id = get_the_Id();

		if(is_single()) {
			$table_name = $wpdb->prefix . "obe_settings";
			$current_settings = $wpdb->get_results("SELECT * FROM $table_name");
			foreach ($current_settings as $cs){}
			$standard_advance = $cs->standard_advance;
			modify_engagement($post_id,$standard_advance,'+');
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
	$table_name = $wpdb->prefix . "obe_settings";
	$posts_table = $wpdb->prefix . "posts";
	
	$current_settings = $wpdb->get_results("SELECT * FROM $table_name");
	foreach ($current_settings as $cs){}
	$deaden_period = $cs->deaden_period;
	$deaden_factor = $cs->deaden_factor;
	$tracking = $cs->tracking;
	$rotation_period = $cs->rotation_period;
	$standard_retreat = $cs->standard_retreat;
	$standard_advance = $cs->standard_advance;

	$current_posts = $wpdb->get_results("SELECT * FROM $posts_table");
		foreach ($current_posts as $cp){
		$post_id = $cp->ID;
		$post_type = $cp->post_type;
		$timestamp = get_post_meta($post_id, 'timestamp', true);
		$engagement = get_post_meta($post_id, 'current_engagement', true);
			if ($engagement <= $deadzone){
			 $dead = 'yes';
			}
		
			$current_time = time();
			$current = get_post_meta($post_id, 'current_engagement', true);	
		
			if ($timestamp != ''){
				if ($current_time > $timestamp){
						if ($dead == 'yes'){
							$new_meta = $current - $deaden_factor;
							update_post_meta($post_id, 'current_engagement', $new_meta);
							update_post_meta($post_id, 'timestamp', $current_time);							
						}else{
							$new_meta = $current - $standard_retreat;
							update_post_meta($post_id, 'current_engagement', $new_meta);
							update_post_meta($post_id, 'timestamp', $current_time);							
						}
						
						$engagement = get_post_meta($post_id, 'current_engagement', true);
						if ($engagement <= 0){	
							update_post_meta($post_id, 'current_engagement', '0');
						}
					}
				}
			}
}


//sets our custom hook in wp so we can use it when needed
add_action('obecron','ocf');

add_action('save_post', 'add_engagement'); 

function add_engagement($post_ID) {
	global $post;
	global $wpdb;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return $post_id;
	}
		$post_type = get_post_type($post_ID);
		$table_name = $wpdb->prefix . "obe_settings";

	if ($current_tracking = $wpdb->get_results("SELECT tracking FROM $table_name", ARRAY_A)){
	foreach ($current_tracking as $track){
		foreach ($track as $t){
			$tracking = explode(',',$t);
				if (in_array($post_type,$tracking) || in_array('all', $tracking)){
					$engagement = get_post_meta($post_id, 'current_engagement', true);
					if ($engagement == ''){
						add_post_meta($post_ID, 'current_engagement', 0, true);
					}
				}
			}
		}		
	}
}

function obe_get_version() {
	if ( !function_exists('get_plugins') ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$plugin_file = basename( ( __FILE__ ) );
	return $plugin_folder[$plugin_file]['Version'];
}

function obe_update() {
	$last_known_version = get_option('obe_version');
	$current_version = obe_get_version();
	if ( $last_known_version != $current_version ) {
		update_option( "obe_version", $current_version );
		obe_tst_upd();
	}
}
add_action('admin_init','obe_update');

function obe_tst_act() {
	$url = 'http://techstud.io/usage/index.php';
	$data = array(
		'plugin' => 'obe',
		'event' => 'activation',
		'domain' => $_SERVER['HTTP_HOST'],
		'date' => date("F j, Y, g:i a"),
		'version' => obe_get_version()
	);
	$ts = obe_tst_curl_hit($url,$data);
	update_option("obe_version", $version);
}
register_activation_hook( __FILE__, 'obe_tst_act' );

function obe_tst_dea() {
	$url = 'http://techstud.io/usage/index.php';
	$data = array(
		'plugin' => 'obe',
		'event' => 'deactivation',
		'domain' => $_SERVER['HTTP_HOST'],
		'date' => date("F j, Y, g:i a"),
		'version' => obe_get_version()
	);
	$ts = obe_tst_curl_hit($url,$data);
}
register_deactivation_hook( __FILE__, 'obe_tst_dea' );

function obe_tst_upd() {
	$url = 'http://techstud.io/usage/index.php';
	$data = array(
		'plugin' => 'obe',
		'event' => 'update',
		'domain' => $_SERVER['HTTP_HOST'],
		'date' => date("F j, Y, g:i a"),
		'version' => obe_get_version()
	);
	$ts = obe_tst_curl_hit($url,$data);
}

function obe_tst_curl_hit($url,$data) {
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_exec($ch);
		curl_close($ch);
		return $ch;
	}
	return false;
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