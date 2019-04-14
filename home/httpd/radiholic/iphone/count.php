<?php
/******************************************************************************
* Kazuya Shimanuki  *
******************************************************************************/

// include needed files
require('../db_config.php');
require('../global.php');

// iPhone check
if( isiPhone() ){
	// ok
} else {
	redirect('../');
}

// connect to the database
db_connect($mysql['username'],$mysql['password'],$mysql['database'],$mysql['host']);

// assign config options from database to an array
$config = get_config($mysql['prefix']);

debug_mode($config['debug_mode']);

/** Update Pages **************************************************************/


$sId = sanitize_text($_GET['id']);
if ($sId == "") {
	redirect('../');
}
elseif (!preg_match('/^.{32}\.mp3$/', $sId ) ) {
	redirect('../');
}
else {
	bUpdateFilenameCount($mysql['prefix'], $sId);
}

?>