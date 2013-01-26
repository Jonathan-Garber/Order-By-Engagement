<?php

/*
Plugin Name: Order By Engagement
Description: Order By Engagement allows developers to track post engagement and sort by those tracked values.
Version: 1.2.0
Author: TechStudio
Author URI: http://techstudio.co
*/

include "functions/functions.php";

function obe_update_routine() {

	$obe_settings = get_option('obe_settings');

	if ( empty($obe_settings) ) {
		// Build default settings array
		$new_settings = array(
			'rotation' => 'daily',
			'dead_zone' => 20,
			'dead_factor' => 3,
			'advance' => 1,
			'subtract' => 1,
			'tracking' => array('all'),
			'throttle' => 30
			);
		// Update settings option with new default settings...
		update_option('obe_settings', $new_settings);
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . "obe_ips";
	$sqldrop = "DROP TABLE IF EXISTS $table_name";
	$results = $wpdb->query( $sqldrop );

	$table_name = $wpdb->prefix . "obe_settings";
	$sqldrop = "DROP TABLE IF EXISTS $table_name";
	$results = $wpdb->query( $sqldrop ); 
	
}

function obe_installer() {

	// Get current settings
	$obe_settings = get_option('obe_settings');
	
	// Current settings are empty so we add defaults
	if (empty($obe_settings)){
		//build default settings array
		$new_settings = array(
			'rotation' => 'daily',
			'dead_zone' => 20,
			'dead_factor' => 3,
			'advance' => 1,
			'subtract' => 1,
			'tracking' => array('all'),
			'throttle' => 30
			);
		//update settings option with new default settings...
		update_option('obe_settings', $new_settings);
	}

}

function obe_uninstall() {
	delete_option('obe_settings');
	delete_option('obe_ips');
}

register_activation_hook( __FILE__, 'obe_installer' );
register_deactivation_hook( __FILE__, 'obe_uninstall' );
add_action('admin_menu', 'obe_plugin_menu');

function obe_plugin_menu() {
	add_menu_page('Engagement', 'Engagement', 8, __FILE__, obe_sub_menu_settings);
}

function obe_sub_menu_settings(){
	include "pages/obe-settings.php";
}

// Runs on every wp-admin page load to check if our plugin is up to date

function obe_get_version() {
	$plugin_data = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data['Version'];
	return $plugin_version;
}
add_action('admin_init','obe_update');

// Sets our custom hook in wp so we can use it when needed
add_action('obecron','ocf');

// Adds engagement to any post the user edits thats supposed to be tracked and defaults to 0
add_action('save_post', 'add_engagement'); 

// Get setting for what trigger type to use
$obe_settings = get_option('obe_settings');
$trigger = $obe_settings[trigger];
if ( $trigger == 'php' ) {
	//when WP loads it fires the check to see if we are viewing a single page or post
	//does not work with Caching because caching causes php not to run
	add_action('wp', 'obe_is_single');
}
else {
	// Adds post ID to html meta tags and if single adds jquery/javascript/ajax
	add_action('wp_head', 'obe_add_meta');
}
