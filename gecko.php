<?php

define('IN_MYBB', 1);
require "./global.php";

//Initialise Class
require "./inc/plugins/gecko/class_gecko.php";
$gecko = new Gecko($mybb,$db);

if ($mybb->settings['gecko_global_switch'] == 1)
{
eval("\$gecko_nav_admin =\"".$templates->get("gecko_nav_admin")."\";");
eval("\$gecko_nav =\"".$templates->get("gecko_nav")."\";");

if ($mybb->input['action'] == '' || $mybb->input['action'] == 'dashboard')
{

add_breadcrumb("Dashboard", "gecko.php");

$query = $db->write_query("SELECT * FROM ".TABLE_PREFIX."gecko_projects");

while ($row = $db->fetch_array($query))
{
	eval("\$gecko_projects_list .=\"
<tr>
<td class='trow1' align='center'><span class='smalltext'><a href='gecko.php?action=manageproject&amp;id=".$row['id']."'>".$row['project_name']."</a></span></td>
<td class='trow2' align='center'><span class='smalltext'>".$row['issues']."</span></td>
<td class='trow1' align='center'><span class='smalltext'>".$row['development_status']."</span></td>
<td class='trow2' align='center'><span class='smalltext'>".my_date($mybb->settings['dateformat'], $row['last_updated'])."<br />".my_date($mybb->settings['timeformat'], $row['last_updated'])."</span></td>
</tr>
\";");
}

if ($db->num_rows($query) == 0)
{
	eval("\$gecko_projects_list =\"
<tr>
<td class='trow1' align='center' colspan='4'><span class='smalltext'>No projects yet</span></td>
</tr>
\";");
}
	// We're at the dashboard - lets show the dashboard page
eval("\$gecko_dashboard =\"".$templates->get("gecko_dashboard")."\";");

output_page($gecko_dashboard);
}

if ($mybb->input['type'] == 'bug')
{
	// It's a bug

	if ($mybb->input['action'] == 'report')
	{

        add_breadcrumb("Report a Bug", "gecko.php?type=bug&amp;action=report");

		$query = $db->write_query("SELECT * FROM ".TABLE_PREFIX."gecko_projects");
		//Generate project list
		while ($row = $db->fetch_array($query))
		{
			eval("\$gecko_projects_list .=\"<option value=".$row['id'].">".$row['project_name']."</option>\";");
		}

		eval("\$gecko_bug_report =\"".$templates->get("gecko_bug_report")."\";");
		output_page($gecko_bug_report);
	}
	
	if($mybb->input['action'] == 'create')
	{
		$gecko->createIssue($mybb->input);
	}

	if ($mybb->input['action'] == 'view' && $mybb->input['filter'] == 0 )
	{
		// we're viewing all reported bugs

        add_breadcrumb("View all Reported Bugs", "gecko.php?type=bug&amp;action=view&amp;filter=0");
		eval("\$gecko_bug_allbugs =\"".$templates->get("gecko_bug_allbugs")."\";");
		output_page($gecko_bug_allbugs);
	}

	if ($mybb->input['action'] == 'view' && $mybb->input['filter'] == 1)
	{
		// viewing only the users own reported bugs

        add_breadcrumb("View your Reported Bugs", "gecko.php?type=bug&amp;action=view&amp;filter=1");
		eval("\$gecko_bug_mybugs =\"".$templates->get("gecko_bug_mybugs")."\";");
		output_page($gecko_bug_mybugs);
	}
}

if ($mybb->input['type'] == 'feature')
{
	// It's a feature

	if ($mybb->input['action'] == 'suggest')
	{
		// It's a feature suggestion

        add_breadcrumb("Suggest a Feature", "gecko.php?type=feature&amp;action=suggest");
		eval("\$gecko_feature_suggest =\"".$templates->get("gecko_feature_suggest")."\";");
		output_page($gecko_feature_suggest);
	}

	if ($mybb->input['action'] == 'view' && $mybb->input['filter'] == 0)
	{
		// we're viewing all suggested features

        add_breadcrumb("View all Suggested Features", "gecko.php?type=feature&amp;action=suggest&amp;filter=0");
		eval("\$gecko_feature_allfeatures =\"".$templates->get("gecko_feature_allfeatures")."\";");
		output_page($gecko_feature_allfeatures);
	}

	if ($mybb->input['action'] == 'view' && $mybb->input['filter'] == 1)
	{
		// viewing only the users own suggested features

        add_breadcrumb("View my Suggested Features", "gecko.php?type=feature&amp;action=suggest&amp;filter=1");
		eval("\$gecko_feature_myfeatures =\"".$templates->get("gecko_feature_myfeatures")."\";");
		output_page($gecko_feature_myfeatures);
	}
}

}

else
{
	die("Gecko has been disabled by the administrator.");
}

?>