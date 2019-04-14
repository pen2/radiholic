<?php
/******************************************************************************
* This file is part of the Deadlock PHP User Management System.               *
*                                                                             *
* File Description: Update a user's status to verified                        *
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

if(isset($_GET['user'])){
	// get the user's current info
	if(check_user_exists($_GET['user'],$mysql['prefix'])){
		$sql = 'SELECT * FROM '.$mysql['prefix'].'users WHERE `username`=\''.$_GET['user'].'\'';
		if($result = mysql_query($sql)){
			while(($row = mysql_fetch_array($result)) != false){
				$username = $row['username'];
				$firstname = $row['firstname'];
				$lastname = $row['lastname'];
				$email = $row['email'];
				$phone = $row['phone'];
				$country = $row['country'];
				$username = $row['username'];
				$password = $row['password'];
			}
		} else {
			die('The following MySQL query failed. '.$sql);
		}
	}

	// check if the user exists, just to prevent errors. if they exist, remove them from the database
	if($_GET['action']=='deny'){
		if(check_user_exists($_GET['user'],$mysql['prefix'])){
			if(!remove_user($_GET['user'],$mysql['prefix'])){
				$error=1;
			} else {
				if($config['email_user_accept']=='true'){
					if(!sendmail($email,$config['admin_email'],get_email_subject($mysql['prefix'],'user_AccountDenied'),get_email_body($firstname,$lastname,$email,$username,$password,$config['protected_area_url'],$config['deadlock_url'],$config['admin_email'],$mysql['prefix'],'user_AccountDenied'))){
						die('Deadlock was unable to send an email to the user.');
					}
				}
			}
		}
	}

	// check if the user exists, if so, change their status to 2
	if($_GET['action']=='accept'){
		if(check_user_exists($_GET['user'],$mysql['prefix'])){
			if(!accept_user_request($_GET['user'],$mysql['prefix'])){
				$error=1;
			} else {
				if($config['email_user_accept']=='true'){
					if(!sendmail($email,$config['admin_email'],get_email_subject($mysql['prefix'],'user_AccountApproved'),get_email_body($firstname,$lastname,$email,$username,$password,$config['protected_area_url'],$config['deadlock_url'],$config['admin_email'],$mysql['prefix'],'user_AccountApproved'))){
						die('Deadlock was unable to send an email to the user.');
					}
				}
				generate_htpasswd($mysql['prefix']);
			}
		}
	}
}

if(!isset($error)):
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<META HTTP-EQUIV="Refresh" CONTENT="5; URL=./userrequests.php">
<title>Deadlock - User Updated Successfully</title>
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
    <td height="20" colspan="2" class="style2"><strong><a href="./index.php">Top</a>: User Updated Successfully </strong></td>
  </tr>
  <tr>
    <td height="28" colspan="2" class="style2">The user &quot;<?=htmlentities($_GET['user'])?>&quot; was updated successfully! Please wait while your are redirected to the user request list.</td>
  </tr>
  <tr>
    <td height="19" colspan="2"><br />
      <span class="style2">If you are not redirected within 5 seconds, <a href="./userrequests.php">click here</a>...</span><br /><br /><br /></td>
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
<META HTTP-EQUIV="Refresh" CONTENT="5; URL=./userinfo.php?user=<?=htmlentities($_GET['user'])?>">
<title>Deadlock - User Updated Successfully</title>
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
    <td height="20" colspan="2" class="style2"><strong><a href="./index.php">Top</a>: User Update Failed </strong></td>
  </tr>
  <tr>
    <td height="28" colspan="2" class="style2">The user <?=htmlentities($_GET['user'])?> was unable to be updated. Please make sure MySQL is running and setup correctly. Please wait while your are redirected to the request list.</td>
  </tr>
  <tr>
    <td height="19" colspan="2"><br />
      <span class="style2">If you are not redirected within 5 seconds, <a href="./userrequests.php">click here</a>...</span><br /><br /><br /></td>
  </tr>
  
  <tr>
    <td height="21" colspan="2" class="footercell"><div align="center"><?show_footer($software_signature)?></div></td>
  </tr>
</table>
</body>
</html>
<?php 
endif;
?>