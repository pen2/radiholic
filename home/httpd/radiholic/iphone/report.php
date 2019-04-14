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

error_reporting(E_WARNING);

$sId = sanitize_text($_GET['id']);
$sMailaddr = sanitize_text($_POST['mailaddr']);
$sReport = sanitize_text($_POST['report']);

if ($sId == "") {
	print "未知のエラーです E1";
}
elseif (!preg_match('/^.{32}\.mp3$/', $sId ) ) {
	print "未知のエラーです E2";
}
elseif (!preg_match('/^.+\@.+$/', $sMailaddr ) ) {
	print $sMailaddr."メールアドレスが正しくありません";
}
elseif ($sReport == "") {
	print "報告内容が入力されていません";
}
else {
	bUpdateFilenameCount($mysql['prefix'], $sId);
		// タスクのインサート
	$rtnCode = iInsertReport($sId,$sMailaddr,$sReport,$_SERVER["REMOTE_ADDR"],$mysql['prefix']);
	if ($rtnCode == 200) {
		print "報告ありがとうございました";
	}
	else {
		print "報告でエラーが発生しました";
	}
}

?>