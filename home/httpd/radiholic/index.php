<?php
/******************************************************************************
* Kazuya Shimanuki  *
******************************************************************************/

// include needed files
require('./db_config.php');
require('./global.php');

// iPhone check
if( isiPhone() ){
	redirect('iphone/');
} else {
	redirect('pc/');
}

?>