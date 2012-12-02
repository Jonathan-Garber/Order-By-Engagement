<?php
	$table_name = $wpdb->prefix . "obe_ips";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	$sql = "CREATE TABLE " . $table_name . " ( id mediumint(9) NOT NULL AUTO_INCREMENT,
	ip_address varchar(32) NOT NULL,
	post_id varchar(8) NOT NULL,
	post_type varchar(32) NOT NULL,	
	date varchar(16) NOT NULL,
	timestamp varchar(32) NOT NULL,
	UNIQUE KEY id (id))";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	}
	
	$table_name = $wpdb->prefix . "obe_settings";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	$sql = "CREATE TABLE " . $table_name . " ( id mediumint(9) NOT NULL AUTO_INCREMENT,
	rotation_period varchar(8) NOT NULL,
	deaden_period varchar(8) NOT NULL,
	deaden_factor varchar(8) NOT NULL,
	standard_retreat varchar(8) NOT NULL,	
	standard_advance varchar(8) NOT NULL,
	tracking text NOT NULL,
	timeout text NOT NULL,
	UNIQUE KEY id (id))";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	}	
	
	$rotation = 'daily';
	$dead_period = '20';
	$dead_factor = '3';
	$s_advance = '1';
	$s_retreat = '1';
	$tracking = 'all';
	$timeout = '30';
	
	
	$insert = "INSERT INTO " . $table_name . " 
	(
	rotation_period,
	deaden_period,
	deaden_factor,
	standard_retreat,
	standard_advance,
	tracking,
	timeout
	) 
	VALUES (
	'" . $wpdb->escape($rotation) . "',
	'" . $wpdb->escape($dead_period) . "',
	'" . $wpdb->escape($dead_factor) . "',
	'" . $wpdb->escape($s_retreat) . "',
	'" . $wpdb->escape($s_advance) . "',
	'" . $wpdb->escape($tracking) . "',
	'" . $wpdb->escape($timeout) . "'	
	)";
	$results = $wpdb->query( $insert );	
	
?>