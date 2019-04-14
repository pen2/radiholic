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
$template->readFileIntoString("request.html",$main);
$template->readFileIntoString("overall_footer.html",$footer);
$template->setTemplateString($header . $main . $footer);

// set the php self variable which is used to submit the form.
$template->setVariable("phpself",$_SERVER['PHP_SELF']);

/** Create Pages **************************************************************/

$sHasError = "";

// $sHasError = "Maintenance";

$sRequestUrl = sanitize_text($_GET['url']);
if ($sRequestUrl == "") {
	$sHasError = "URLError";
}
elseif (!preg_match('/^http:\/\/.+\/.+\.(asx|wax|wvx|asf|wma)$/', $sRequestUrl ) ) {
	$sHasError = "URLError";
}
elseif (preg_match('/\.\.\./', $sRequestUrl ) ) {
	$sHasError = "URLError";
}
else{
	
	// URLのロード
	$template->setVariable("url",$sRequestUrl);
	
	// メタ情報fetchと解析
	$aMetaArray = aGetInlinkAddress($sRequestUrl);
	
	// dump
	//echo '<pre>';
	//print_r($hMetaArray);
	//echo '</pre>';
	
	// メタ情報ロード
	if ( !empty($aMetaArray) ) {
		foreach ($aMetaArray as $sMetaKey => $sMetaItem) {
			if ( !empty($aMetaArray[$sMetaKey]) ) {
				if (eregi('^anchor', $sMetaKey)) {
					$template->setVariable("sessionguess",$sMetaItem);
					$template->addBlock("sessionguessitem");
				}
				
				if (eregi('^title', $sMetaKey)) {
					$template->setVariable("titleguess",$sMetaItem);
					$template->addBlock("titleguessitem");
				}
				
				if (eregi('^h', $sMetaKey)) {
					$template->setVariable("titleguess",$sMetaItem);
					$template->addBlock("titleguessitem");
			
					$template->setVariable("sessionguess",$sMetaItem);
					$template->addBlock("sessionguessitem");
				}
				
				// inlinkアドレスロード(画像用)
				if (eregi('^page_url', $sMetaKey)) {
					$template->setVariable("inlink",$sMetaItem);
				}
				
			}
		}
	}
	
	
	
	
	
	// Categoriesリストロード
	$aCategoryTmp = aGetCategoryLists($mysql['prefix']);
	foreach ($aCategoryTmp as $tmp)
	{
		$template->setVariable("categoryid",$tmp['categoryid']);
		$template->setVariable("categoryname",$tmp['categoryname']);
		
		$template->addBlock("categories");
	}
	
	
}



/** Print Pages **************************************************************/

if ($sHasError === "") {

	$template->addBlock("Success");
}
else {
	
	$template->setVariable("MetaAutoRefresh","<meta http-equiv=\"refresh\" content=\"30; url=index.php\">");
	$template->addBlock($sHasError);
}

$template->setVariable("footer",show_user_footer($software_signature));
$template->setVariable("pagename","リクエスト");
$template->generateOutput();



?>
