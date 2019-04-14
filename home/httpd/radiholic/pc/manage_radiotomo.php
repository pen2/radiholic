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
$template->readFileIntoString("manage_header.html",$header);
$template->readFileIntoString("manage.html",$main);
$template->readFileIntoString("overall_footer.html",$footer);
$template->setTemplateString($header . $main . $footer);

// set the php self variable which is used to submit the form.
$template->setVariable("phpself",$_SERVER['PHP_SELF']);

/** Create Pages **************************************************************/

$sHasError = "";
$sManageKey = "7p3duwnzhw8fwa3b825pk85b";
$template->setVariable("key",$sManageKey);

$sKey = sanitize_text($_GET['key']);
$sMode = "";
if (isset($_POST['mode']))
{
    $sMode = sanitize_text($_POST['mode']);
}

$sQuery = "radiotomo.sakura.ne.jp"; // REWRITE
if ($sKey !== $sManageKey) {
	$sHasError = "QueryError";
}
elseif (preg_match('/(select|update|drop|union)/i', $sQuery ) ) {
	$sHasError = "QueryError";
}
else{
    // for MANAGE
    if ($sMode == 'delete' ) {
        bDeleteRadio($mysql['prefix'], sanitize_text($_POST['filename']));
        $template->addBlock("ManageSuccess");
    }
    elseif ($sMode == 'restore' ) {
        bRestoreRadio($mysql['prefix'], sanitize_text($_POST['filename']));
        $template->addBlock("ManageSuccess");
    }
    
	// QuerySearchロード
	$aQuerySearchAllTmp = aGetQuerySearchWithHidden($mysql['prefix'], $sQuery, 'session', 'desc');
	$iQuerySearchAllNum = count($aQuerySearchAllTmp);
	$iQuerySearchAllCount = 1;
	foreach ($aQuerySearchAllTmp as $tmp)
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
		$template->setVariable("filename",$tmp['filename']);
		$template->setVariable("radiholic_skyline","");
        if($tmp['state'] == "success")
        {
            $template->addBlock("QueryDoDelete");
        }
        if($tmp['state'] == "hidden")
        {
            $template->addBlock("QueryDoRestore");
        }
		
		$iQuerySearchAllCount++;
		$template->addBlock("QuerySearch");

	}
	
	
	
}

/** Print Pages **************************************************************/

if ($sHasError === "") {
	$template->setVariable("QueryNum",$iQuerySearchAllNum);
	$template->setVariable("Query",$sQuery);
	$template->addBlock("Success");
}
else {
	
	$template->setVariable("MetaAutoRefresh","<meta http-equiv=\"refresh\" content=\"30; url=index.php\">");
	$template->addBlock($sHasError);
}

$template->setVariable("footer",show_user_footer($software_signature));
$template->setVariable("pagename",$sQuery);
$template->generateOutput();



?>
