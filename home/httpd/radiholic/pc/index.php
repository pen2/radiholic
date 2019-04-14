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
$template->readFileIntoString("main.html",$main);
$template->readFileIntoString("overall_footer.html",$footer);
$template->setTemplateString($header . $main . $footer);

// set the php self variable which is used to submit the form.
$template->setVariable("phpself",$_SERVER['PHP_SELF']);

/** Create Pages **************************************************************/

// 広告取得
//$template->setVariable("radiholic_supersky",sGetADCodes("radiholic_supersky"));
//$template->setVariable("radiholic_supersky",sGetADCodes("radiholic_optimize"));
//$template->setVariable("radiholic_supersky",sGetADCode());
$template->setVariable("radiholic_supersky",sGetADContents( sGetADKeyword() ));

// Categoriesリストロード
$aCategoryTmp = aGetCategoryLists($mysql['prefix']);
foreach ($aCategoryTmp as $tmp)
{
	$template->setVariable("categoryid",$tmp['categoryid']);
	$template->setVariable("categoryname",$tmp['categoryname']);
	
	$template->addBlock("categories");
}


// RecentRequestsロード
$aRecentRequestsTmp = aGetRecentRequests($mysql['prefix'], 10);
foreach ($aRecentRequestsTmp as $tmp)
{
	$template->setVariable("url",$tmp['url']);
	$template->setVariable("branch",sGetBranchIndicator($tmp['branch']));
	$template->setVariable("shorturl",sGetShortURL($tmp['url'], 20));
	$template->setVariable("title",$tmp['title']);
	$template->setVariable("session",$tmp['session']);
	$template->setVariable("categoryid",$tmp['categoryid']);
	$template->setVariable("inlink",$tmp['inlink']);
	$template->setVariable("req_date",$tmp['req_date']);
	
	if ($tmp['state'] === "new") {
		$template->setVariable("state","待機中");
	}
	elseif ($tmp['state'] === "converting") {
		$template->setVariable("state","登録中");
	}
	
	$template->addBlock("RecentRequests");
}

// RecentConvertsロード
$aRecentConvertsTmp = aGetRecentConverts($mysql['prefix'], 20);
foreach ($aRecentConvertsTmp as $tmp)
{
	$template->setVariable("url",$tmp['url']);
	$template->setVariable("branch",sGetBranchIndicator($tmp['branch']));
	$template->setVariable("shorturl",sGetShortURL($tmp['url'], 20));
	$template->setVariable("title",$tmp['title']);
	$template->setVariable("session",$tmp['session']);
	$template->setVariable("categoryid",$tmp['categoryid']);
	$template->setVariable("inlink",$tmp['inlink']);
	$template->setVariable("fin_date",$tmp['fin_date']);
	$template->setVariable("count",$tmp['count']);
	
	$template->addBlock("RecentConverts");
}

// TopPlaysロード
$aGetTopPlaysTmp = aGetTopPlays($mysql['prefix'], 3);
foreach ($aGetTopPlaysTmp as $tmp)
{
	$template->setVariable("url",$tmp['url']);
	$template->setVariable("branch",sGetBranchIndicator($tmp['branch']));
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
	if ($iTitleScore > 7) {
		$sTitleFontSize = ($iTitleScore / 2) + 8;
		$template->setVariable("TitleFontSize",$sTitleFontSize);
		$template->setVariable("TitleName",$sTitleName);
		$template->setVariable("TitleUrl",urlencode($sTitleName));
		$template->addBlock("TitleCloud");
	}
}

//再生数
$iTotalCount = iGetTotalCount($mysql['prefix']);
$template->setVariable("TotalCount",$iTotalCount);
$iTotalRadio = iGetTotalRadio($mysql['prefix']);
$template->setVariable("TotalRadio",$iTotalRadio);


$template->setVariable("footer",show_user_footer($software_signature));
$template->setVariable("pagename","iPhoneでネットラジオを聞こう！");
//$template->setVariable("pagename","iPhoneでストリーミングラジオを聞こう！");
$template->generateOutput();

/*
sGetInlinkAddress('http://www.mediafactory.co.jp/files/d000093/zero_50_c7ld.asx');
sGetInlinkAddress('http://www.mediafactory.co.jp/files/d000093/zero_47_nys2.asx');
sGetInlinkAddress('http://d-game.dengeki.com/asx/nogizaka-radio-011mko.asx');
*/


?>