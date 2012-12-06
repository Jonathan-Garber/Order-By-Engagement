<?php

/*
Plugin Name: Order By Engagement
Description: Order By Engagement is a WordPress plugin designed to allow theme developers to order posts by a value which corresponds to how engaged with a post users appear to be.
Version: 1.1.0
Author: TechStudio
Author URI: http://techstudio.co
*/
require_once 'functions/functions.php';


/*
The plugin checks versions on admin_init .. however if the update you released does not require any special function to run
you simply leave the update_routine empty
obe_update_routine will run anytime the version installed does not match the last version installed on site.
*/
function obe_update_routine(){
/*
leave empty unless this release actually has settings or variables to add/change

The routine below is in place to ensure people who upgrade catch the new default settings
*/
$obe_settings = get_option('obe_settings');
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
	
global $wpdb;
	$table_name = $wpdb->prefix . "obe_ips";
   $sqldrop = "DROP TABLE IF EXISTS $table_name";
   $results = $wpdb->query( $sqldrop );

	$table_name = $wpdb->prefix . "obe_settings";
   $sqldrop = "DROP TABLE IF EXISTS $table_name";
   $results = $wpdb->query( $sqldrop );   	
	
	
}



function obe_installer() {
//get current settings
$obe_settings = get_option('obe_settings');
	
	//current settings are empty so we add defaults
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
} //END Plugin Menu

function obe_sub_menu_settings(){
include 'pages/obe-settings.php';
}

//runs on every wp-admin page load to check if our plugin is up to date
add_action('admin_init','obe_update');
function obe_get_version() {
$plugin_data = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];
return $plugin_version;
}

//sets our custom hook in wp so we can use it when needed
add_action('obecron','ocf');

//adds engagement to any post the user edits thats supposed to be tracked and defaults to 0
add_action('save_post', 'add_engagement'); 

//when WP loads it fires the check to see if we are viewing a single page or post
add_action('wp', 'obe_is_single');