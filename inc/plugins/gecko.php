<?php
if(!defined("IN_MYBB"))
{
	die("You Cannot Access This File Directly. Please Make Sure IN_MYBB Is Defined.");
}

function gecko_info()
{
	return array(
		"name"  => "Gecko",
		"description"=> "Gecko is a bug tracking solution for MyBB",
		"website"        => "http://community.mybb.com/",
		"author"        => "Polarbear541 & Vernier",
		"version"        => "1.0",
		"guid"             => "",
		"compatibility" => "16*"
	);
}

function gecko_activate()
{
	global $db;
	$gecko_group = array(
		'name'  => 'gecko',
		'title'      => "Gecko",
		'description'    => "Settings for the Gecko plugin",
		'disporder'    => "1",
		'isdefault'  => "0",
	);
	
	$db->insert_query('settinggroups', $gecko_group);
	$gid = $db->insert_id(); 
	
	$gecko_setting_1 = array(
		'name'        => 'gecko_global_switch',
		'title'            => "Enable?",
		'description'    => "Please select on to enable Gecko or off to disable Gecko",
		'optionscode'    => 'onoff',
		'value'        => '1',
		'disporder'        => 1,
		'gid'            => intval($gid),
	);
	
	$db->insert_query('settings', $gecko_setting_1);
	rebuild_settings();
}

function gecko_deactivate()
{
	global $db;
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('gecko_global_switch')");
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='gecko'");
	rebuild_settings();
}
?>