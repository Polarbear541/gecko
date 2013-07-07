<?php
//Gecko Class
if(!defined("IN_MYBB"))
{
	die("You Cannot Access This File Directly. Please Make Sure IN_MYBB Is Defined.");
}

class Gecko
{
	private $mybb;
	private $db;
	
	function _construct($mybbc,$dbc)
	{
		$this->mybb = $mybbc;
		$this->db = $dbc;
	}

	function createIssue($input)
	{
		//var_dump($input);
		$issue_name = $this->db->escape_string(htmlspecialchars($input['name']));
		var_dump($issue_name);
		//$db->write_query("INSERT INTO ".TABLE_PREFIX."gecko_projects VALUES('','$project_name', '', '$date')");
	}
	
	function editIssue()
	{
	
	}
	
	function deleteIssue()
	{
	
	}

	function isManager()
	{

	}

	function isDeveloper()
	{
		
	}
}
?>