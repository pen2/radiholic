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
$template->readFileIntoString("all.html",$main);
$template->readFileIntoString("overall_footer.html",$footer);
$template->setTemplateString($header . $main . $footer);

// set the php self variable which is used to submit the form.
$template->setVariable("phpself",$_SERVER['PHP_SELF']);

/** Create Pages **************************************************************/

// 広告取得
$template->setVariable("radiholic_supersky",sGetADContents( sGetADKeyword() ));

// TopPlaysロード
$aGetTopPlaysTmp = aGetTopPlays($mysql['prefix'], 3);
foreach ($aGetTopPlaysTmp as $tmp)
{
	$template->setVariable("url",$tmp['url']);
	$template->setVariable("shorturl",sGetShortURL($tmp['url'], 20));
	$template->setVariable("title",$tmp['title']);
	$template->setVariable("titleurl",urlencode($tmp['title']));
	$template->setVariable("session",$tmp['session']);
	$template->setVariable("categoryid",$tmp['categoryid']);
	$template->setVariable("inlink",$tmp['inlink']);
	$template->setVariable("filename",$tmp['filename']);
	$template->setVariable("fin_date",$tmp['fin_date']);
	$template->setVariable("count",$tmp['count']);
	
	$template->addBlock("TopPlays");
}

// タイトルクラウド
$aGetTitleStatsTmp = aGetTitleStats($mysql['prefix']);
foreach ($aGetTitleStatsTmp as $sTitleName => $iTitleScore)
{
	if ($iTitleScore > 0) {
		$sTitleFontSize = ($iTitleScore / 2) + 8;
		$template->setVariable("TitleFontSize",$sTitleFontSize);
		$template->setVariable("TitleName",$sTitleName);
		$template->setVariable("TitleUrl",urlencode($sTitleName));
		$template->addBlock("TitleCloud");
	}
}


$template->setVariable("footer",show_user_footer($software_signature));
$template->setVariable("pagename","全てのラジオ");
$template->generateOutput();


?>