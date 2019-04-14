<?php
/******************************************************************************
* This file is part of the Deadlock PHP User Management System.               *
*                                                                             *
* File Description: This file creates a list of all users with the option to  *
* edit or delete them.                                                        *
*                                                                             *
* Deadlock is free software; you can redistribute it and/or modify            *
* it under the terms of the GNU General Public License as published by        *
* the Free Software Foundation; either version 2 of the License, or           *
* (at your option) any later version.                                         *
*                                                                             *
* Deadlock is distributed in the hope that it will be useful,                 *
* but WITHOUT ANY WARRANTY; without even the implied warranty of              *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               *
* GNU General Public License for more details.                                *
*                                                                             *
* You should have received a copy of the GNU General Public License           *
* along with Deadlock; if not, write to the Free Software                     *
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA  *
******************************************************************************/

// include needed files
require('../db_config.php');
require('../global.php');
require("../lib/Pager.class.php");

// connect to the database
db_connect($mysql['username'], $mysql['password'], $mysql['database'],$mysql['host']);

// assign config options from database to an array
$config = get_config($mysql['prefix']);

debug_mode($config['debug_mode']);

// remove users that have not verified their email after 72 hours if email verification is enabled
if($config['verify_email']=='true' && $config['prune_inactive_users']=='true'){
	PruneInactiveUsers($mysql['prefix']);
}

// start the session
admin_sessions($config['admin_session_expire']);
if (!isset($_SESSION['logged_in']))
{
	redirect('./login.php');
}

// start class
$p = new Pager;

// results per page
$limit = 30;

// Find the start depending on $_GET['page'] (declared if it's null)
$start = $p->findStart($limit);

if (!empty($_GET['search']))
{
	$sql = 'SELECT * FROM `'.$mysql['prefix'].'users` WHERE CONCAT( `firstname`,`lastname`, `username` ) LIKE \'%'.mysql_escape_string($_GET['search']).'%\' and `status`=0';
	$sql2 = $sql.' LIMIT '.$start.', '.$limit;
}

else
{
	// list all users
	$sql = 'SELECT * FROM '.$mysql['prefix'].'users WHERE status=0 ORDER BY lastname';
	$sql2 = $sql.' LIMIT '.$start.', '.$limit;
}

if ($result = mysql_query($sql2))
{
	if (@mysql_num_rows($result) > 0)
	{
		$userlist = '';
		while (($row = mysql_fetch_array($result)) != false)
		{
			$userlist .= '<tr class="style2"><td>'.$row['lastname'].', '.$row['firstname'].'</td><td>'.$row['username'].'</td><td>'.str_chop($row['email'],30).'</td><td><a href="./userinfo.php?user='.$row['username'].'"><a href="#" onclick="deleteuser(\''.$row['username'].'\')"><img src="../images/delete15px.gif" alt="Remove" border="0" title="Remove" /></a> <a href="#" onclick="validateuser(\''.$row['username'].'\')"><img src="../images/accept15px.gif" alt="Validate" border="0" title="Validate" /></a></tr>'."\n";
		}
	}
	else
	{
		if (empty($_GET['search']))
		{
			$userlist = '<tr><td colspan="4"><span class="style11">There are currently no inactive users.</span></td></tr>';
		}
		else
		{
			$userlist = '<tr><td colspan="4"><span class="style11">Your search returned 0 results.</span></td></tr>';
		}
	}
}

else
{
	die('The MySQL query failed. MySQL said: '.mysql_error());
}

$count = mysql_num_rows(mysql_query($sql));
$pages = $p->findPages($count, $limit);
// get the page list
$pagelist = $p->pageList($_GET['page'], $pages);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Deadlock - Inactive Users</title>
<link href="../images/admin.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="../images/hint.js">
/***********************************************
* Show Hint script- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/
</script>
<script type="text/javascript">
function deleteuser(username){
	var answer = confirm('Are you sure you want to delete the inactive user "'+username+'"?');
	if(answer==true){
		window.location="./userdel.php?r=inactive&user="+username;
	}
}
function validateuser(username){
	var answer = confirm('Are you sure you want to validate the user "'+username+'"? This will make it as though they verified their email address.');
	if(answer==true){
		window.location="./validateuser.php?user="+username;
	}
}
</script>
</head>
<body>
<table width="549" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="329" height="58"><a href="./index.php"><img src="../images/header_logo.gif" width="252" height="58" border="0" /></a></td>
    <td width="220"><div align="right"><img src="../images/tux.gif" width="48" height="48" /></div></td>
  </tr>
  <tr>
    <td height="2" colspan="2"><img src="../images/grey_pixel.gif" width="100%" height="2" /></td>
  </tr>
  <tr>
    <td height="20" colspan="2" class="style2"><strong><a href="./index.php">Top</a>: Inactive Users</strong></td>
  </tr>
  <tr>
    <td height="28" colspan="2" class="style2">This is a list of all inactive users in the database. These users have not yet validated their email address. You may validate their email for them by clicking the green check mark, or you may delete it so that they can no longer validate their email address by clicking the red 'x'. You may also enter a first name, last name OR username into the search box to find a user.</td>
  </tr>
  <tr>
    <td height="19" colspan="2"><br />
        <form id="form1" name="form1" method="get" action="<?=$_SERVER['PHP_SELF']?>">
          <span class="style2">Search:</span>
          <input type="text" name="search" />
          <input type="submit" value="Go" /><? if(!empty($_GET['search'])): ?><input type="button" value="View All" onclick="window.location='<?=$_SERVER['PHP_SELF']?>'" /><? endif; ?>
        </form>
        <br />
      <table width="100%" border="0">
        <tr>
          <td width="25%" class="style5">Name</td>
          <td width="26%" class="style5">Username</td>
          <td width="28%" class="style5">Email</td>
          <td width="21%" class="style5">Actions</td>
        </tr>
       <?=$userlist?>
      </table><br />
      <? if($count > 0): ?><div align="center"><span class="style2">Page:</span> <span class="style5"><?=$pagelist?></span></div><br /><? endif; ?>
    <br /></td>
  </tr>

  <tr>
    <td height="21" colspan="2" class="footercell"><div align="center"><?show_footer($software_signature)?></div></td>
  </tr>
</table>
</body>
</html>