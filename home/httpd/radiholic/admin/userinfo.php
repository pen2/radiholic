<?php
/******************************************************************************
* This file is part of the Deadlock PHP User Management System.               *
*                                                                             *
* File Description: Show information for a specific user                      *
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

// connect to the database
db_connect($mysql['username'],$mysql['password'],$mysql['database'],$mysql['host']);

// assign config options from database to an array
$config = get_config($mysql['prefix']);

debug_mode($config['debug_mode']);

// remove users that have not verified their email after 72 hours if email verification is enabled
if($config['verify_email']=='true' && $config['prune_inactive_users']=='true'){
	PruneInactiveUsers($mysql['prefix']);
}

// start the session
admin_sessions($config['admin_session_expire']);
if(!isset($_SESSION['logged_in'])){
	redirect('./login.php');
}

if(check_user_exists($_GET['user'],$mysql['prefix'])):

$result = mysql_query('SELECT * FROM '.$mysql['prefix'].'users WHERE username="'.$_GET['user'].'"');
while (($row = mysql_fetch_array($result)) != false) {
	$name = $row['firstname'].' '.$row['lastname'];
	$country = $row['country'];
	$phone = $row['phone'];
	$username = $row['username'];
	$email = $row['email'];
	$status = $row['status'];
	$RegistrationDate = date($config['date_format'],$row['registration_timestamp']);
}
if($country=='Not Selected'){
	$country = '<i>Not Available</i>';
}
if(empty($phone)){
	$phone = '<i>Not Available</i>';
}

switch($status){
	case '2':
	$statustext = '<font color="green">Active</font>';
	break;
	case '1':
	$statustext = '<font color="red">Inactive</font> - <i>Needs admin approval</i>';
	break;
	case '0':
	$statustext = '<font color="red">Inactive</font> - <i>Needs email verification</i>';
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Deadlock - User Information</title>
<script type="text/javascript" src="../images/hint.js">
/***********************************************
* Show Hint script- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/
</script>
<script type="text/javascript">
function deleteuser(username){
	var answer = confirm('Are you sure you want to delete the user "'+username+'"?');
	if(answer==true){
		window.location="./userdel.php?user="+username;
	}
}
function denyuser(username){
	var answer = confirm('Are you sure you want to deny the user "'+username+'"? This will completely remove them from the database.');
	if(answer==true){
		window.location="./useraccept.php?action=deny&user="+username;
	}
}
function acceptuser(username){
	var answer = confirm('Are you sure you want to accept the user "'+username+'"? This will update their status to approved and will give them access to the protected area.');
	if(answer==true){
		window.location="./useraccept.php?action=accept&user="+username;
	}
}
</script>
<link href="../images/admin.css" rel="stylesheet" type="text/css" media="all" />
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
    <td height="20" colspan="2" class="style2"><strong><a href="./index.php">Top</a>: User Information </strong></td>
  </tr>
  <tr>
    <td height="22" colspan="2" class="style2">Information about a specific user can be found below.</td>
  </tr>
  <tr>
    <td height="19" colspan="2"><br />
      <table width="70%" border="0">
      <tr>
        <td width="31%" class="style5">Full Name:</td>
        <td width="69%" class="style2"><?=$name?></td>
      </tr>
      <tr>
        <td class="style5">Username:</td>
        <td class="style2"><?=$username?></td>
      </tr>
      <tr>
        <td class="style5">Email Address: </td>
        <td class="style2"><? if($status=='2'): ?><a href="./bulkemail.php?user=<?=$username?>"><?=$email?></a><? else: print $email; endif; ?></td>
      </tr>
      <tr>
        <td class="style5">Country:</td>
        <td class="style2"><?=$country?></td>
      </tr>
      <tr>
        <td class="style5">Phone:</td>
        <td class="style2"><?=FormatPhoneNumber($phone)?></td>
      </tr>
      <tr>
        <td class="style5">Date Registered:</td>
        <td class="style2"><?=$RegistrationDate?></td>
      </tr>
      <tr>
        <td class="style5">Status:</td>
        <td class="style2"><?=$statustext?></td>
      </tr>
    </table>
      <br />
      <? if($status=='1'): ?>
      <input name="Button" type="button" value="Accept" onclick="acceptuser('<?=$username?>')" />
      <input name="Button" type="button" value="Decline" onclick="denyuser('<?=$username?>')" />
      <? else: ?>
      <input name="Button" type="button" value="Delete" onclick="deleteuser('<?=$username?>')" />
      <input type="submit" value="Edit" onclick="window.location='./edituser.php?user=<?=$username?>'" />
      <? endif; ?>
      <br />

  <span class="style2"><br />
      <br /><a href="./userlist.php">&lt;&lt; Back to user list</a><br/><br/><br/></span></td>
  </tr>
  <tr>
    <td height="21" colspan="2" class="footercell"><div align="center"><?show_footer($software_signature)?></div></td>
  </tr>
</table>
</body>
</html>
<? else: ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Deadlock - User Information</title>
<script type="text/javascript" src="../images/hint.js">
/***********************************************
* Show Hint script- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/
</script>
<link href="../images/admin.css" rel="stylesheet" type="text/css" media="all" />
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
    <td height="20" colspan="2" class="style2"><strong><a href="./index.php">Top</a>: User Information </strong></td>
  </tr>
  <tr>
    <td height="22" colspan="2" class="style2">Information about a specific user is below. This page also shows the user's last 10 logins. </td>
  </tr>
  <tr>
    <td height="19" colspan="2"><br />
      <span class="style2">Sorry, but the specified user was not found in the database. <br />
      <br /><a href="./userlist.php">&lt;&lt; Back to user list</a><br/><br/><br/>
      </span></td>
  </tr>
  
  <tr>
    <td height="21" colspan="2" class="footercell"><div align="center"><?show_footer($software_signature)?></div></td>
  </tr>
</table>
</body>
</html>
<? endif; ?>