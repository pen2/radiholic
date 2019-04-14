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

// require the template engine class (MiniTemplator)
require('../lib/MiniTemplator.class.php');
$template = new MiniTemplator;

// set template
$template->readFileIntoString("overall_header.html",$header);
$template->readFileIntoString("search.html",$main);
$template->readFileIntoString("overall_footer.html",$footer);
$template->setTemplateString($header . $main . $footer);

// set the php self variable which is used to submit the form.
$template->setVariable("phpself",$_SERVER['PHP_SELF']);

/** Create Pages **************************************************************/

$sHasError = "";

$sQuery = sanitize_text($_GET['q']);
$sSort = 'session';
if (isset($_GET['s'])) { 
    $sSort = sanitize_text($_GET['s']); 
}
if ($sQuery == "") {
	$sHasError = "QueryError";
}
#elseif (preg_match('/(select|update|drop|union|or)/i', $sQuery ) ) {
elseif (preg_match('/(select|update|drop|union)/i', $sQuery ) ) {
	$sHasError = "QueryError";
}
else{
    if (!preg_match('/(session|count)/i', $sSort )) {
        $sSort = 'session';
    }
	
	// RecentConvertsロード
	$aQuerySearchAllTmp = aGetQuerySearchAll($mysql['prefix'], $sQuery, $sSort, 'desc');
	$iQuerySearchAllNum = count($aQuerySearchAllTmp);
	foreach ($aQuerySearchAllTmp as $tmp)
	{
		$template->setVariable("url",$tmp['url']);
		$template->setVariable("shorturl",sGetShortURL($tmp['url'], 20));
		$template->setVariable("title",$tmp['title']);
		$template->setVariable("session",$tmp['session']);
		$template->setVariable("categoryid",$tmp['categoryid']);
		$template->setVariable("inlink",$tmp['inlink']);
		$template->setVariable("filename",$tmp['filename']);
		$template->setVariable("fin_date",$tmp['fin_date']);
		$template->setVariable("count",$tmp['count']);
		
		$template->addBlock("QuerySearch");
	}
	
	
	
}

/** Print Pages **************************************************************/

if ($sHasError === "") {
	$template->setVariable("QueryNum",$iQuerySearchAllNum);
	$template->setVariable("Query",$sQuery);
    if ( strcmp($sSort,'count')) {
        $template->addBlock("Count");
    }
    else {
        $template->addBlock("Session");
    }
    $template->addBlock("Success");
}
else {
	$template->setVariable("Query","エラー");
	$template->setVariable("MetaAutoRefresh","<meta http-equiv=\"refresh\" content=\"30; url=index.php\">");
	$template->addBlock($sHasError);
}


$template->setVariable("footer",show_user_footer($software_signature));
$template->setVariable("pagename","検索");
$template->generateOutput();

?>
