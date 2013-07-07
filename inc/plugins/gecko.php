<?php
if(!defined("IN_MYBB"))
{
	die("You Cannot Access This File Directly. Please Make Sure IN_MYBB Is Defined.");
}

$plugins->add_hook('admin_tools_menu', 'gecko_admin_tools_menu');
$plugins->add_hook('admin_tools_action_handler', 'gecko_admin_tools_action_handler');
$plugins->add_hook('admin_tools_permissions', 'gecko_admin_tools_permissions');
$plugins->add_hook('fetch_wol_activity_end', 'gecko_fetch_wol_activity');
$plugins->add_hook('build_friendly_wol_location_end', 'gecko_build_friendly_wol_location_end');

function gecko_info()
{
	return array(
		"name"  => "Gecko",
		"description"=> "Gecko is a bug tracking solution for MyBB",
		"website"        => "http://community.mybb.com/",
		"author"        => "Polarbear541 &amp; Vernier",
		"version"        => "1.0",
		"guid"             => "",
		"compatibility" => "16*"
	);
}

function gecko_is_installed()
{
	global $db;
	if($db->table_exists("gecko_projects"))
	{
		return true;
	}
	return false;
}


function gecko_install()
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

	$db->write_query(
		"CREATE TABLE ".TABLE_PREFIX."gecko_projects
		(
			id int(15) NOT NULL Auto_Increment,
			project_name varchar(100),
			development_status varchar(50),
			last_updated int(50),
			PRIMARY KEY (id)
			) ENGINE=MyISAM;"
	);

	$db->write_query(
		"CREATE TABLE ".TABLE_PREFIX."gecko_issues
		(
			id int(15) NOT NULL Auto_Increment,
			issue_name varchar(100),
			issue_desc text,
			uid int(15),
			pid int(15),
			status int(15),
			PRIMARY KEY (id)
			) ENGINE=MyISAM;"
	);

    $db->write_query(
        "CREATE TABLE ".TABLE_PREFIX."gecko_users
        (
            id int(15) NOT NULL Auto_Increment,
            username varchar(100),
            manager int(1),
            developer int(1),
            PRIMARY KEY (id)
            ) ENGINE=MyISAM;"
    );

}

function gecko_uninstall()
{
	global $db;
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('gecko_global_switch')");
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='gecko'");
	rebuild_settings();

	$db->write_query("DROP TABLE ".TABLE_PREFIX."gecko_projects");
	$db->write_query("DROP TABLE ".TABLE_PREFIX."gecko_issues");
    $db->write_query("DROP TABLE ".TABLE_PREFIX."gecko_users");
    require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
}

function gecko_activate()
{
	global $db;
		require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
  $new_templates = array();
  
// Dashboard template contents

  $new_templates['gecko_dashboard'] = '
<html>
<head>
<title>Gecko</title>
{$headerinclude}
</head>
<body>
{$header}
{$gecko_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center" colspan="4"><strong>Projects</strong></td>
</tr>
<tr>
<td class="tcat" width="40%"><strong>Name</strong></td>
<td class="tcat" width="20%"><strong>Issues</strong></td>
<td class="tcat" width="20%"><strong>Development Status</strong></td>
<td class="tcat"width="20%"><strong>Last Updated</strong></td>
</tr>
{$gecko_projects_list}
</table>
</td>
</table>
{$footer}
</body>
</html>
';

$new_templates['gecko_nav'] = '
<table width="100%" border="0" align="center">
<td  width="180" valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
    <tr>
        <td class="thead"><strong>Menu</strong></td>
    </tr>
    <tr><td class="trow1 smalltext"><a href="gecko.php">Dashboard</a></td></tr>
        <tr>
        <td class="tcat">
            <div class="float_right expcolimage"><img src="{$theme[\'imgdir\']}/collapse{$collapsedimg[\'geckobugs_img\']}.gif" id="geckobugs_img" class="expander" alt="[-]" title="[-]" /></div>
            <div><span class="smalltext"><strong>Bugs</strong></span></div>
        </td>
    </tr>
    <tbody style="{$collapsed[\'geckobugs_img\']}" id="geckobugs_img">
        <tr><td class="trow1 smalltext"><a href="gecko.php?type=bug&amp;action=report" >Report a bug</a></td></tr>
        <tr><td class="trow1 smalltext"><a href="gecko.php?type=bug&amp;action=view&amp;filter=1">View my bugs</a></td></tr>
        <tr><td class="trow1 smalltext"><a href="gecko.php?type=bug&amp;action=view&amp;filter=0">View all bugs</a></td></tr>
    </tbody>
<tr>
        <td class="tcat">
            <div class="float_right"><img src="{$theme[\'imgdir\']}/collapse{$collapsedimg[\'modcpforums\']}.gif" id="geckofeatures_img" class="expander" alt="[-]" title="[-]" /></div>
            <div><span class="smalltext"><strong>Features</strong></span></div>
        </td>
    </tr>
    <tbody id="geckofeatures_e">
        <tr><td class="trow1 smalltext"><a href="gecko.php?type=feature&amp;action=suggest">Suggest a feature</a></td></tr>
        <tr><td class="trow1 smalltext"><a href="gecko.php?type=feature&amp;action=view&amp;filter=1">View my suggested features</a></td></tr>
        <tr><td class="trow1 smalltext"><a href="gecko.php?type=feature&amp;action=view&amp;filter=0">View all suggested features</a></td></tr>
    </tbody>
    {$gecko_nav_admin}
</td>
</table>
';

$new_templates['gecko_nav_admin'] = '
<tr>
        <td class="tcat">
            <div class="float_right"><img src="{$theme[\'imgdir\']}/collapse{$collapsedimg[\'modcpforums\']}.gif" id="geckoadmin_img" class="expander" alt="[-]" title="[-]" /></div>
            <div><span class="smalltext"><strong>Administration</strong></span></div>
        </td>
    </tr>
    <tbody id="geckoadmin_e">
        <tr><td class="trow1 smalltext"><a href="gecko.php?type=feature&amp;action=suggest">Dashboard</a></td></tr>
    </tbody>
';


$new_templates['gecko_bug_report'] = '
<html>
<head>
<title>Gecko - Report a bug</title>
{$headerinclude}
</head>
<body>
{$header}
{$gecko_nav}
<td valign="top">
<head>
<title>Gecko - View my bugs</title>
<form action="./gecko.php?action=create&amp;type=bug" method="POST">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center" colspan="4"><strong>Report Bug</strong></td>
</tr>
<tr>
<td class="tcat">Project: <select name="project">{$gecko_projects_list}</select></td>
</tr>
<tr>
<td class="trow1">Issue Name: <input type="text" name="name" /> Status: <select name="status"><option value="1">New</option><option value="2">Confirmed</option><option value="3">In Progress</option><option value="4">Complete</option></select></td>
</tr>
<tr>
<td class="trow2">Description:<br /><textarea rows="10" cols="150" name="description"></textarea></td>
</tr>
<tr>
<td class="trow1"><input type="submit" name="createbug" value="Submit Issue" /></td>
</tr>
</table>
</form>
</td>
</table>
{$footer}
</body>
</html>
';

$new_templates['gecko_bug_mybugs'] = '
<html>
{$headerinclude}
</head>
<body>
{$header}
{$gecko_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center" colspan="4"><strong>View my bugs</strong></td>
</tr>
<tr>
<td class="tcat">View my reported bugs</td>
</tr>
<tr>
<td class="trow1">All my reported bugs are shown below.</td>
</tr>
</table>
</td>
</table>
{$footer}
</body>
</html>
';

$new_templates['gecko_bug_allbugs'] = '
<html>
<head>
<title>Gecko - View all bugs</title>
{$headerinclude}
</head>
<body>
{$header}
{$gecko_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center" colspan="4"><strong>View all bugs</strong></td>
</tr>
<tr>
<td class="tcat">View all reported bugs</td>
</tr>
<tr>
<td class="trow1">All reported bugs are shown below.</td>
</tr>
</table>
</td>
</table>
{$footer}
</body>
</html>
';

$new_templates['gecko_feature_suggest'] = '
<html>
<head>
<title>Gecko - Suggest a feature</title>
{$headerinclude}
</head>
<body>
{$header}
{$gecko_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center" colspan="4"><strong>Suggest a feature</strong></td>
</tr>
<tr>
<td class="tcat">Suggest a feature</td>
</tr>
<tr>
<td class="trow1">Suggest a feature.</td>
</tr>
</table>
</td>
</table>
{$footer}
</body>
</html>
';

$new_templates['gecko_feature_myfeatures'] = '
<html>
<head>
<title>Gecko - Show my feature</title>
{$headerinclude}
</head>
<body>
{$header}
{$gecko_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center" colspan="4"><strong>Show my features</strong></td>
</tr>
<tr>
<td class="tcat">Show my features</td>
</tr>
<tr>
<td class="trow1">Shows all my features below.</td>
</tr>
</table>
</td>
</table>
{$footer}
</body>
</html>
';

$new_templates['gecko_feature_allfeatures'] = '
<html>
<head>
<title>Gecko - Show all features</title>
{$headerinclude}
</head>
<body>
{$header}
{$gecko_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center" colspan="4"><strong>Show all features</strong></td>
</tr>
<tr>
<td class="tcat">Show all features</td>
</tr>
<tr>
<td class="trow1">show all features below.</td>
</tr>
</table>
</td>
</table>
{$footer}
</body>
</html>
';


foreach($new_templates as $title => $template)
  {
    $new_template = array(
      'title'   => $db->escape_string($title),
      'template'  => $db->escape_string($template),
      'sid'   => '-1',
      'version' => '140',
      'dateline'  => TIME_NOW
    );
    
    $db->insert_query('templates', $new_template);
  }

}

function gecko_deactivate()
{
	global $db;

	$delete_templates = array(
    'gecko_dashboard',
    'gecko_nav',
    'gecko_nav_admin',
    'gecko_bug_report',
    'gecko_bug_mybugs',
    'gecko_bug_allbugs',
    'gecko_feature_suggest',
    'gecko_feature_myfeatures',
    'gecko_feature_allfeatures',
  );
  
  foreach($delete_templates as $template)
  {
    $db->delete_query('templates', "title='{$template}'");
  }
}

function gecko_admin_tools_menu(&$sub_menu)
{
    global $mybb;
    if ($mybb->settings['gecko_global_switch'] == 1){
    $sub_menu[] = array('id' => 'gecko', 'title' => 'Gecko', 'link' => 'index.php?module=tools-gecko');
}
}

function gecko_admin_tools_action_handler(&$actions)
{
    $actions['gecko'] = array('active' => 'gecko', 'file' => 'gecko.php');
}

function gecko_admin_tools_permissions(&$admin_permissions)
{
    $admin_permissions['gecko'] = "Can manage Gecko?";
}

function gecko_delete_project($id)
{
	global $db;
        $db->delete_query('gecko_projects', "id='{$id}'");
}


function gecko_fetch_wol_activity(&$user_activity)
{
    global $user, $mybb;

    //get the base filename
    $split_loc = explode(".php", $user_activity['location']);
    if($split_loc[0] == $user['location'])
    {
        $filename = '';
    }
    else
    {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }

    //get parameters of the URI
    if($split_loc[1])
    {
        $temp = explode("&amp;", my_substr($split_loc[1], 1));
        foreach($temp as $param)
        {
            $temp2 = explode("=", $param, 2);
            $temp2[0] = str_replace("amp;", '', $temp2[0]);
            $parameters[$temp2[0]] = $temp2[1];
        }
    }
    
    switch($filename)
    {
        case "gecko":
            if($parameters['type'] == "bug")
            {
            	// It's a bug
            	if ($parameters['action'] == "report")
            	{
            		// Reporting a bug
            		$user_activity['activity'] = "gecko_bug_report";
            	}
            	if ($parameters['action'] == "view")
            	{
            		// Viewing bugs
            		if ($parameters['filter'] == "1")
            		{
            			// Viewing own bugs
            			$user_activity['activity'] = "gecko_bug_view_own";
            		}
            		if ($parameters['filter'] == "0")
            		{
            			// Viewing all bugs
            			$user_activity['activity'] = "gecko_bug_view_all";
            		}
            	}
                
            }
            elseif ($parameters['type'] == "feature")
            {
            	// It's a feature
            	if ($parameters['action'] == "suggest")
            	{
            	// Suggesting a feature
            		$user_activity['activity'] = "gecko_feature_suggest";
            	}
            	if ($parameters['filter'] == "1")
            	{
            	// Viewing own features
            		$user_activity['activity'] = "gecko_feature_view_own";
            	}
            	if ($parameters['filter'] == "0")
            	{
            	// Viewing all features
            		$user_activity['activity'] = "gecko_feature_view_all";
            	}
            }
            else
            {
            	// No $parameters - assume it's the dashboard
                $user_activity['activity'] = "gecko_dashboard";
            }
            break;
    }
    
    return $user_activity;
}

function gecko_build_friendly_wol_location_end(&$plugin_array)
{
    global $db, $lang, $mybb, $_SERVER;

    // Define URLs
    if($mybb->settings['seourls'] == "yes" || ($mybb->settings['seourls'] == "auto" && $_SERVER['SEO_SUPPORT'] == 1))
    {
        define('GECKO_DASHBOARD_URL', "gecko.php");
        define('GECKO_BUG_REPORT_URL', "gecko.php?type=bug&amp;action=report");
        define('GECKO_BUG_VIEW_OWN_URL', "gecko.php?type=bug&amp;action=view&amp;filter=1");
        define('GECKO_BUG_VIEW_ALL_URL', "gecko.php?type=bug&amp;action=view&amp;filter=0");
        define('GECKO_FEATURE_SUGGEST_URL', "gecko.php?type=feature&amp;action=suggest");
        define('GECKO_FEATURE_VIEW_OWN_URL', "gecko.php?type=feature&amp;action=view&amp;filter=1");
        define('GECKO_FEATURE_VIEW_ALL_URL', "gecko.php?type=feature&amp;action=view&amp;filter=0");
    }
    else
    {
        define('GECKO_DASHBOARD_URL', "gecko.php");
        define('GECKO_BUG_REPORT_URL', "gecko.php?type=bug&amp;action=report");
        define('GECKO_BUG_VIEW_OWN_URL', "gecko.php?type=bug&amp;action=view&amp;filter=1");
        define('GECKO_BUG_VIEW_ALL_URL', "gecko.php?type=bug&amp;action=view&amp;filter=0");
        define('GECKO_FEATURE_SUGGEST_URL', "gecko.php?type=feature&amp;action=suggest");
        define('GECKO_FEATURE_VIEW_OWN_URL', "gecko.php?type=feature&amp;action=view&amp;filter=1");
        define('GECKO_FEATURE_VIEW_ALL_URL', "gecko.php?type=feature&amp;action=view&amp;filter=0");
    }
    
   $lang->load('gecko');

    switch($plugin_array['user_activity']['activity'])
    {
        case "gecko_dashboard":
            $plugin_array['location_name'] = $lang->sprintf($lang->viewing_dashboard, GECKO_DASHBOARD_URL);
            break;

        case 'gecko_bug_report':
            $plugin_array['location_name'] = $lang->sprintf($lang->reporting_bug, GECKO_BUG_REPORT_URL);
            break;

        case 'gecko_bug_view_own':
            $plugin_array['location_name'] = $lang->sprintf($lang->viewing_own_bugs, GECKO_BUG_VIEW_OWN_URL);
            break;

        case 'gecko_bug_view_all':
            $plugin_array['location_name'] = $lang->sprintf($lang->viewing_all_bugs, GECKO_BUG_VIEW_ALL_URL);
            break;

        case 'gecko_feature_suggest':
            $plugin_array['location_name'] = $lang->sprintf($lang->suggesting_feature, GECKO_FEATURE_SUGGEST_URL);
            break;

        case 'gecko_feature_view_own':
            $plugin_array['location_name'] = $lang->sprintf($lang->viewing_own_features, GECKO_FEATURE_VIEW_OWN_URL);
            break;

        case 'gecko_feature_view_all':
            $plugin_array['location_name'] = $lang->sprintf($lang->viewing_all_features, GECKO_FEATURE_VIEW_ALL_URL);
            break;
    }

    return $plugin_array;

} 

?>