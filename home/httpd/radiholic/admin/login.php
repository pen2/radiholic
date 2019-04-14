<?php
/******************************************************************************
* This file is part of the Deadlock PHP User Management System.               *
*                                                                             *
* File Description: This is the admin panel login page.                       *
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

// set the session name so that there is no conflict
session_name('admin_sid');

// start the session
session_start();

// if the query string says to logout, remove the session
if(isset($_GET['cmd']) && $_GET['cmd'] == 'logout' && isset($_SESSION['logged_in'])){
	session_destroy();
	redirect($_SERVER['PHP_SELF']);
}

// if the admin is already logged in, redirect to index.php
if(isset($_SESSION['logged_in'])){
	redirect('./index.php');
}



if(isset($_POST['submit'])){
	$numfailed = CheckFailedLogins($mysql['prefix'],$_SERVER['REMOTE_ADDR']);
	if($numfailed >= 5){
		$message = 'You have reached the maximum number of failed login attempts (5). Please wait 10 minutes and try again.';
	} else {
		if($_POST['password'] == $config['admin_pass']){
			$_SESSION['logged_in'] = 1;
			redirect('index.php');
		} else {
			LogFailedLogin($mysql['prefix'],'admin');
			$numfailed = CheckFailedLogins($mysql['prefix'],$_SERVER['REMOTE_ADDR']);
			$numleft = 5 - $numfailed;
			$message = 'The password you entered was incorrect. All failed logins are logged. You have '.$numleft.' login attempts left.';
		}
	}
} else {
	$message = 'Welcome to the administration panel. Please enter your password below, then click &quot;Login&quot; to login to access this area. Please note that all failed attemps are logged in the database. After 5 failed logins, you will not be able to login to the panel for 10 minutes.';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Deadlock - Admin Login</title>
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
    <td height="20" colspan="2" class="style2"><strong><a href="./index.php">Top</a>: Admin Panel Login </strong></td>
  </tr>
  <tr>
    <td height="22" colspan="2" class="style2"><?=$message?></td>
  </tr>
  <tr>
    <td height="19" colspan="2"><br /><br/><div align="center">
      <form id="form1" name="form1" method="post" action="<?=$_SERVER['PHP_SELF']?>?<?=$_SERVER['QUERY_STRING']?>">
        <span class="style2">Password:</span>
        <input name="password" type="password" id="password" />
        <input type="submit" value="Login" />
        <input name="submit" type="hidden" id="submit" value="1" />
      </form>
    </div><br/><br/><br/></td>
  </tr>
  <tr>
    <td height="21" colspan="2" class="footercell"><div align="center"><?show_footer($software_signature)?></div></td>
  </tr>
</table>
</body>
</html>