<?php

require_once '../../../../wp-load.php';
require_once 'functions.php';
$post_id = $_POST['post_id'];

$obe_settings = get_option('obe_settings');
$standard_advance = $obe_settings[advance];	
modify_engagement($post_id,$standard_advance,'+');		
