<?php

/**
 * This is an example script for the Sendgrid Subuser API 
 * PHP Client by Signature Tech Studio. This is a simple 
 * Script that generates a list of all subusers and an 
 * at-a-glance list of which apps are enabled/disabled for
 * That user.
 * @author Alex Dills, STechStudio
 * @version 1.0
 * @since File available since 12/11/12
 */

require 'classes/sendgridSubuserApi.php';
//Set up your username and password, I prefer to get mine from a separate file
$sendgrid_username = 'SendgridUser';
$sendgrid_password = 'SendgridPass';

//instaniate the Sendgrid object
$sendgrid = new sendgridSubuserApi($sendgrid_username,$sendgrid_password);

//This returns an array of subusers and their info. Each subuser is an array, and within each array, you have info about the subuser
$subuser_list = $sendgrid->getSubusers();

foreach($subuser_list as $subuser_info){
	$current_subuser = $subuser_info['username'];
	$subuser_apps = $sendgrid->getApps($current_subuser);
	foreach($subuser_apps as $app_info){
			listToFile($current_subuser . ":\n");
			echo $current_subuser . ":\n";
		foreach($app_info as $id => $entry){
			listToFile("\t" . $id . " => " . $entry . "\n");
			echo "\t" . $id . " => " . $entry . "\n";
		}
	}
}


function listToFile($string){
	file_put_contents('example.list',$string,FILE_APPEND);
}


?>