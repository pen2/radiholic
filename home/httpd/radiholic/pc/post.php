<?php
/******************************************************************************
* Kazuya Shimanuki  *
******************************************************************************/

// include needed files
require('../db_config.php');
require('../global.php');

// iPhone check
if( isiPhone() ){
	redirect('../');
} else {
	// ok
}

// connect to the database
db_connect($mysql['username'],$mysql['password'],$mysql['database'],$mysql['host']);

// assign config options from database to an array
$config = get_config($mysql['prefix']);

debug_mode($config['debug_mode']);

// require the template engine class (MiniTemplator)
require('../lib/MiniTemplator.class.php');
$template = new MiniTemplator;

// set template
$template->readFileIntoString("overall_header.html",$header);
$template->readFileIntoString("post.html",$main);
$template->readFileIntoString("overall_footer.html",$footer);
$template->setTemplateString($header . $main . $footer);

// set the php self variable which is used to submit the form.
$template->setVariable("phpself",$_SERVER['PHP_SELF']);

/** Create Pages **************************************************************/

$sHasError = "";

$sRequestUrl = sanitize_text($_POST['url']);
$bRequestBranch = false;
if(isset($_POST['branch'])) $bRequestBranch = true;
$sRequestTitle = sanitize_text($_POST['title']);
$sRequestSession = sanitize_text($_POST['session']);
$sRequestCategory = sanitize_text($_POST['category']);
if ($sRequestUrl == "") {
	$sHasError = "URLError";
}
elseif (!preg_match('/^http:\/\/.+\/.+\.(asx|wax|wvx|asf|wma)$/', $sRequestUrl ) ) {
	$sHasError = "URLError";
}
elseif (preg_match('/^http:\/\/www\.simulradio\.jp\/.+$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^.+fc2.+$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^.+bbnradio.+$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^.+bbc.co.uk.+$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^.+darazfm.com.+$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^.+streaming.+$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^.+koyasan-0.+$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^http:\/\/.+\/.+live\.(asx|wax|wvx|asf|wma)$/', $sRequestUrl ) ) {
	$sHasError = "URLError"; #live
}
elseif (preg_match('/^http:\/\/.+\/rjwmt\.asx$/', $sRequestUrl ) ) {
    $sHasError = "URLError"; #live
}
elseif ($sRequestTitle == "" || $sRequestSession == "") {
	$sHasError = "MetaError";
}
elseif ($_SERVER["REMOTE_ADDR"] == "210.253.198.26") {
	$sHasError = "TempError";
}
else{
	// リンク元URLの取得
	$sInlinkUrl = sGetInlinkAddressTSV($sRequestUrl);
	
	// タスクのインサート
	$rtnCode = iInsertRequest($sRequestUrl,$bRequestBranch,$sRequestTitle,$sRequestSession,$sRequestCategory,$sInlinkUrl,$_SERVER["REMOTE_ADDR"],$mysql['prefix']);
	if ($rtnCode == 400) {
		$sHasError = "URLDuplicate";
	}
	elseif ($rtnCode == 500) {
		$sHasError = "DBError";
	}
}

if ($sHasError === "") {
	
	$template->setVariable("MetaAutoRefresh","<meta http-equiv=\"refresh\" content=\"30; url=index.php\">");
	$template->addBlock("Success");
}
else {
	$template->setVariable("ReturnString","リクエストは受け付けませんでした");
	$template->setVariable("ReturnDetail",$sHasError);
	$template->setVariable("ReturnTodo","ブラウザの[戻る]で戻ってください。");
	
	$template->setVariable("MetaAutoRefresh","<meta http-equiv=\"refresh\" content=\"30; url=index.php\">");
	$template->addBlock($sHasError);
}

$template->setVariable("footer",show_user_footer($software_signature));
$template->setVariable("pagename","リクエスト");
$template->generateOutput();


?>
