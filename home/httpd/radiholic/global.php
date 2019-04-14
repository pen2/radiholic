<?php
/******************************************************************************
* Kazuya Shimanuki  *
******************************************************************************/

// add for japan. kazuya

//mb_language("uni");	// whats?
mb_language("Japanese");	// for mb_convert_encoding(..,'auto')
mb_internal_encoding("utf-8");
mb_http_input("auto");
mb_http_output("utf-8");




// if the user stops the script, it will continue to run. this is needed especially to generate a large htpasswd or to send bulk mail
ignore_user_abort(true);
// this is to prevent the script from timing out for the same reasons as above
@set_time_limit(0);

// str_ireplace() for php 4
require_once('func/str_ireplace.php');

// get the software version
require_once('version.php');

// UserAgent
require_once('lib/UserAgentParse.class.php');

// if magic quotes gpc is enabled, this will removed the slashes from certain variables
if (get_magic_quotes_gpc()) {
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST, &$_FILES);
	while (list($key, $val) = each($process)) {
		foreach ($val as $k => $v) {
			unset($process[$key][$k]);
			if (is_array($v)) {
				$process[$key][($key < 5 ? $k : stripslashes($k))] = $v;
				$process[] =& $process[$key][($key < 5 ? $k : stripslashes($k))];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
}

// blowser check
function isiPhone(){

	$br = new Browser;
	$br->browser($_SERVER["HTTP_USER_AGENT"]);
	// echo "$br->Platform, $br->Name version $br->Version";
	
	if($br->Platform == 'iPhone' || $br->Platform == 'iPad' || $br->Platform == 'Android'){
		return true;
	} else {
		return false;
	}
}


// Turn debugging on or off
function debug_mode($setting){
	if($setting){
		ini_set('display_errors','On');
	} else {
		ini_set('display_errors','Off');
	}
}


// Encrypt a password for a .htpasswd file.
function enc_pass($pass,$digest=false,$digestuser=null,$digestrealm=null)
{
	if($digest){
		$pass = md5($digestuser.':'.$digestrealm.':'.$pass);
		return $pass;
	} else {
		if (CRYPT_STD_DES == 1) {
			$pass = crypt(trim($pass), random_string(2,1,0,0));
			return $pass;
		}
	}
}

// This function generates a menu of countries and allows you to have one selected.
function country_menu ($selected){
/*
	$countries = array("Not Selected",
	"Afghanistan",
	"Albania",
	"Algeria",
	"American Samoa",
	"Andorra",
	"Angola",
	"Anguilla",
	"Antarctica",
	"Antigua and Barbuda",
	"Argentina",
	"Armenia",
	"Aruba",
	"Australia",
	"Austria",
	"Azerbaijan",
	"Bahamas",
	"Bahrain",
	"Bangladesh",
	"Barbados",
	"Belarus",
	"Belgium",
	"Belize",
	"Benin",
	"Bermuda",
	"Bhutan",
	"Bolivia",
	"Bosnia and Herzegovina",
	"Botswana",
	"Bouvet Island",
	"Brazil",
	"British Indian Ocean Terr.",
	"Brunei Darussalam",
	"Bulgaria",
	"Burkina Faso",
	"Burundi",
	"Cambodia",
	"Cameroon",
	"Canada",
	"Cape Verde",
	"Cayman Islands",
	"Central African Republic",
	"Chad",
	"Chile",
	"China",
	"Christmas Island",
	"Cocos (Keeling) Islands",
	"Colombia",
	"Comoros",
	"Congo",
	"Cook Islands",
	"Costa Rica",
	"Cote d'Ivoire",
	"Croatia (Hrvatska)",
	"Cuba",
	"Cyprus",
	"Czech Republic",
	"Denmark",
	"Djibouti",
	"Dominica",
	"Dominican Republic",
	"East Timor",
	"Ecuador",
	"Egypt",
	"El Salvador",
	"Equatorial Guinea",
	"Eritrea",
	"Estonia",
	"Ethiopia",
	"Falkland Islands/Malvinas",
	"Faroe Islands",
	"Fiji",
	"Finland",
	"France",
	"France, Metropolitan",
	"French Guiana",
	"French Polynesia",
	"French Southern Terr.",
	"Gabon",
	"Gambia",
	"Georgia",
	"Germany",
	"Ghana",
	"Gibraltar",
	"Greece",
	"Greenland",
	"Grenada",
	"Guadeloupe",
	"Guam",
	"Guatemala",
	"Guinea",
	"Guinea-Bissau",
	"Guyana",
	"Haiti",
	"Heard & McDonald Is.",
	"Honduras",
	"Hong Kong",
	"Hungary",
	"Iceland",
	"India",
	"Indonesia",
	"Iran",
	"Iraq",
	"Ireland",
	"Israel",
	"Italy",
	"Jamaica",
	"Japan",
	"Jordan",
	"Kazakhstan",
	"Kenya",
	"Kiribati",
	"Korea, North",
	"Korea, South",
	"Kuwait",
	"Kyrgyzstan",
	"Lao People's Dem. Rep.",
	"Latvia",
	"Lebanon",
	"Lesotho",
	"Liberia",
	"Libyan Arab Jamahiriya",
	"Liechtenstein",
	"Lithuania",
	"Luxembourg",
	"Macau",
	"Macedonia",
	"Madagascar",
	"Malawi",
	"Malaysia",
	"Maldives",
	"Mali",
	"Malta",
	"Marshall Islands",
	"Martinique",
	"Mauritania",
	"Mauritius",
	"Mayotte",
	"Mexico",
	"Micronesia",
	"Moldova",
	"Monaco",
	"Mongolia",
	"Montserrat",
	"Morocco",
	"Mozambique",
	"Myanmar",
	"Namibia",
	"Nauru",
	"Nepal",
	"Netherlands",
	"Netherlands Antilles",
	"New Caledonia",
	"New Zealand",
	"Nicaragua",
	"Niger",
	"Nigeria",
	"Niue",
	"Norfolk Island",
	"Northern Mariana Is.",
	"Norway",
	"Oman",
	"Pakistan",
	"Palau",
	"Panama",
	"Papua New Guinea",
	"Paraguay",
	"Peru",
	"Philippines",
	"Pitcairn",
	"Poland",
	"Portugal",
	"Puerto Rico",
	"Qatar",
	"Reunion",
	"Romania",
	"Russian Federation",
	"Rwanda",
	"S.Georgia & S.Sandwich Is.",
	"Saint Kitts and Nevis",
	"Saint Lucia",
	"Samoa",
	"San Marino",
	"Sao Tome & Principe",
	"Saudi Arabia",
	"Senegal",
	"Seychelles",
	"Sierra Leone",
	"Singapore",
	"Slovakia (Slovak Republic)",
	"Slovenia",
	"Solomon Islands",
	"Somalia",
	"South Africa",
	"Spain",
	"Sri Lanka",
	"St. Helena",
	"St. Pierre & Miquelon",
	"St. Vincent & Grenadines",
	"Sudan",
	"Suriname",
	"Svalbard & Jan Mayen Is.",
	"Swaziland",
	"Sweden",
	"Switzerland",
	"Syrian Arab Republic",
	"Taiwan",
	"Tajikistan",
	"Tanzania",
	"Thailand",
	"Togo",
	"Tokelau",
	"Tonga",
	"Trinidad and Tobago",
	"Tunisia",
	"Turkey",
	"Turkmenistan",
	"Turks & Caicos Islands",
	"Tuvalu",
	"U.S. Minor Outlying Is.",
	"Uganda",
	"Ukraine",
	"United Arab Emirates",
	"United Kingdom",
	"United States",
	"Uruguay",
	"Uzbekistan",
	"Vanuatu",
	"Vatican (Holy See)",
	"Venezuela",
	"Vietnam",
	"Virgin Islands (British)",
	"Virgin Islands (U.S.)",
	"Wallis & Futuna Is.",
	"Western Sahara",
	"Yemen",
	"Yugoslavia",
	"Zaire",
	"Zambia",
	"Zimbabwe");
	*/
	$countries = array("(未選択)",
	"北海道", 
	"青森県", 
	"岩手県", 
	"宮城県", 
	"秋田県", 
	"山形県", 
	"福島県", 
	"茨城県", 
	"栃木県", 
	"群馬県", 
	"埼玉県", 
	"千葉県", 
	"東京都", 
	"神奈川県", 
	"新潟県", 
	"富山県", 
	"石川県", 
	"福井県", 
	"山梨県", 
	"長野県", 
	"岐阜県", 
	"静岡県", 
	"愛知県", 
	"三重県", 
	"滋賀県", 
	"京都府", 
	"大阪府", 
	"兵庫県", 
	"奈良県", 
	"和歌山県", 
	"鳥取県", 
	"島根県", 
	"岡山県", 
	"広島県", 
	"山口県", 
	"徳島県", 
	"香川県", 
	"愛媛県", 
	"高知県", 
	"福岡県", 
	"佐賀県", 
	"長崎県", 
	"熊本県", 
	"大分県", 
	"宮崎県", 
	"鹿児島県", 
	"沖縄県");

	$menu_code = '<select name="country">'."\n";
	foreach ($countries as $country){
		if($selected == $country) $select_text = ' selected="selected"'; else $select_text = NULL;
		$menu_code .= '<option value="'.$country.'"'.$select_text.'>'.$country.'</option>'."\n";
	}
	$menu_code .= '</select>';
	return $menu_code;
}

// A mail function which makes it easier to provide a from address.
function sendmail ($to,$from,$subject,$message,$html=false){
	$headers = "From: {$from}\r\n";
	if($html){
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=ISO-2022-JP' . "\r\n";
	}
	
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");
	//$message = mb_convert_encoding( $message, "ISO-2022-JP");
	//$subject = mb_convert_encoding( $subject, "ISO-2022-JP");
	//$subject = mb_encode_mimeheader( $subject );
	/*
	if(mail($to,$subject,$message,$headers)){
	*/
	if(mb_send_mail($to,$subject,$message,$headers)){
		return true;
	} else {
		return false;
	}
}


// random string generator
function random_string($len,$lett=1,$num=1,$cap=1) {
	srand(date("s"));
	$possible="";
	if($lett){
		$possible.="abcdefghijklmnopqrstuvwxyz";
		if($cap){
			$possible.="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		}
	}
	if($num){
		$possible.="1234567890";
	}
	$str="";
	while(strlen($str)<$len) {
		$str.=substr($possible,(rand()%(strlen($possible))),1);
	}
	return($str);
}

// This generates a string of x number of *s
function password_filler($password){
	$len = strlen($password);
	$string = '';
	for($i=0; $i<$len; $i++){
		$string .= '*';
	}
	return $string;
}

// Check to see if a user exists in the database
// Return true if the user exists
function check_user_exists($username,$prefix){
	$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE username="'.mysql_escape_string($username).'"');
	if(@mysql_num_rows($result) == 0){
		return false;
	} else {
		return true;
	}
}

// Check to see if a verification code is correct for a certain user
// Return true if it is correct
function check_verification_code($username,$code,$prefix){
	$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE `username`="'.mysql_escape_string($username).'" and `email_verify_code`="'.mysql_escape_string($code).'"');
	if(@mysql_num_rows($result) == 0){
		return false;
	} else {
		return true;
	}
}

// this is to authenticate a user. this checks to see if a username and password combo exist in the database.
// returns true if the combo exists.
function check_login_info($username,$password,$prefix){
	$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE `username`="'.mysql_escape_string($username).'" and `password`="'.mysql_escape_string($password).'"');
	if(@mysql_num_rows($result) == 0){
		return false;
	} else {
		return true;
	}
}

// Update a user's status
function UpdateUserStatus($username,$newstatus,$prefix){
	$sql = 'UPDATE '.$prefix.'users SET `status`="'.$newstatus.'" WHERE `username`="'.$username.'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
}

// get a user's current status
function GetCurrentStatus($username,$prefix){
	$sql = 'SELECT status FROM '.$prefix.'users WHERE `username`="'.mysql_escape_string($username).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_row($result);
	return $row[0];
}

/**********************************************************/
/*                                                        */
/*                                                        */
/* Original functions */
/*                                                        */
/*                                                        */
/**********************************************************/


function sGetDomainPart($url){
	// get host name from URL
	preg_match('@^(?:http://)?([^/]+)@i', $url, $matches);
	$host = $matches[1];
	
	// get last two segments of host name
	preg_match('/[^.]+\.[^.]+$/', $host, $matches);
	return $matches[0];
}

function sGetFilenamePart($url) {
    preg_match('/^http.+\/(.+)$/i', $url, $matches);
    return $matches[1];
}

function sGetParentPart($url) {
    preg_match('/^(http.+\/).+$/i', $url, $matches);
    return $matches[1];
}



function aGetPageMetaInfo($page_url, $orig_url) {
    
    $response = file_get_contents($page_url);
    $response = mb_convert_encoding($response, "UTF-8", 'auto');
    //$response = mb_convert_encoding($response, "UTF-8", "Shift-JIS");
	
	if ($orig_url != ''){
	
		$result["page_url"] = $page_url;
		
		$filename = sGetFilenamePart($orig_url);
		
		preg_match("/<a href=\".+?$filename\"\s*?>(.*?)</i" , $response, $array);
		
		// add anchor text
		if ( !empty($array[1]) ) {
			$result["anchor"] = $array[1];
		}
		else{
			// Re-search
			// img alt
			if(preg_match("/<a .*href=\".+?$filename\"\s*?>.+alt=\"(.+?)\"/i" , $response, $re_array)){
				$result["anchor_img_alt"] = $re_array[1];
			}
			// title
			if(preg_match("/<a title=\"(.+?)\" .*href=\".+?$filename\"\s*?>/i" , $response, $re_array)){
				$result["anchor_title"] = $re_array[1];
			}
			// JP pre match
			if(preg_match("/>([一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[a-zA-Z0-9]+|[ａ-ｚＡ-Ｚ０-９]+)<.*?<a href=\".+?$filename\"\s*?>/i" , $response, $re_array)){
				$result["anchor_pre_jp"] = $re_array[1];
			}
			// JP post match
			if(preg_match("/<a href=\".+?$filename\"\s*?>.*?([一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[a-zA-Z0-9]+|[ａ-ｚＡ-Ｚ０-９]+)/i" , $response, $re_array)){
				$result["anchor_post_jp"] = $re_array[1];
			}
		}
		
		preg_match("/<title>(.*?)</i" , $response, $array);
		// add page title
		if ( !empty($array[1]) ) {
			$result["title"] = $array[1];
		}
	
		preg_match("/<h1>(.*?)</i" , $response, $array);
		// add h1
		if ( !empty($array[1]) ) {
			$result["h1"] = $array[1];
		}
		
		preg_match("/<h2>(.*?)</i" , $response, $array);
		// add h2
		if ( !empty($array[1]) ) {
			$result["h2"] = $array[1];
		}
		
		preg_match("/<h3>(.*?)</i" , $response, $array);
		// add h3
		if ( !empty($array[1]) ) {
			$result["h3"] = $array[1];
		}
	}
	
	
	$prefix = "";
	$parentPart = sGetParentPart($orig_url);
	$aRetroArrayTmp = aGetRetrospectiveRequests($prefix, $parentPart);
	
	foreach ($aRetroArrayTmp as $tmp)
	{
		if ( !empty($tmp['title']) ) {
			$result["title_retro1"] = $tmp['title'];
		}
		if ( !empty($tmp['name']) ) {
			$result["title_retro2"] = $tmp['name'];
		}
		if ( !empty($tmp['author']) ) {
			$result["title_retro3"] = $tmp['author'];
		}
	}
	
	// dump
	/*
	echo '<pre>';
	print_r($result);
	echo '</pre>';
	*/
	
	// check result
	if ( empty( $result ) ) {
		// Not Found in data
		return '';
	}
	return $result;
}

function aGetInlinkAddress($orig_url){
    // return sGetInlinkAddressAPI($orig_url);
    // return sGetInlinkAddressTSV($orig_url);
    
    // $inlink_url = sGetInlinkAddressTSV($orig_url);
    // Yahoo! site explorer closed...
    $inlink_url = '';
    return aGetPageMetaInfo($inlink_url, $orig_url);

}

function sGetInlinkAddressTSV($orig_url){

	$service = 'http://siteexplorer.search.yahoo.com/advtsv';
    $request = $service.'?p='.$orig_url.'&bwm=i&bwmf=&bwmo=';
    $response = file_get_contents($request);
    $data = split("\n", $response);
	
	// Define url array
	$url_stack=array();
    
	foreach ($data as $key => $value) {
		// Dump print
		//print "key = $key : value = $value<br>\n";
		
		// Skip header lines
		if($key < 2){
			continue;
		}
		
		if(preg_match("/^(.+?)\t(http.+?)\t.+$/i", $value, $array)){
			array_push($url_stack, $array[2]);
		}
	}
	
	// check result
	if ( empty($url_stack ) ) {
		// Request failed
		return '';
	}
	
	// Search best url
	$guess_parent_url = sGetParentPart($orig_url);
	$hit = array_search($guess_parent_url, $url_stack);
	if ($hit !== FALSE){
		// Return best url
	    $inlink_url = $url_stack[$hit];
	}
	else{
		// Return first result
    	$inlink_url = $url_stack[0];
	}

    // check result
	if ( $inlink_url == '' ) {
		// Request failed
		return '';
	}

    // success
    return $inlink_url;
}

function sGetInlinkAddressAPI($orig_url){
	// Parsing Yahoo! REST Web Service results using
	// unserialize. PHP4/PHP5
	// Author: Jason Levitt
	// February 1, 2006
	
	//error_reporting(E_ALL);
	
	$appid = '';
	$service = 'http://search.yahooapis.com/SiteExplorerService/V1/inlinkData';
	
	// output=php means that the request will return serialized PHP
	$request = $service.'?appid='.$appid.'&query='.$orig_url.'&results=1&start=1&output=php&omit_inlinks=none';
	
	$response = file_get_contents($request);
	
	if ($response === false) {
		// Request failed
		return '';
	}
	
	$phpobj = unserialize($response);
	
	echo '<pre>';
	print $request;
	print_r($phpobj);
	echo '</pre>';
	
	// check result
	if ( empty($phpobj["ResultSet"]["Result"]["Url"]) ) {
		// Request failed
		return '';
	}
	
	$link_url = $phpobj["ResultSet"]["Result"]["Url"];
	echo "$link_url <br>";
	$orig_url_domain = sGetDomainPart($orig_url);
	echo "$orig_url_domain <br>";
	
	// check inlink
	if (!preg_match('/.+?'.$orig_url_domain.'.+$/', $link_url ) ) {
		echo 'NO INCLUDE';
	}

}

/**********************************************************/
/*                                                        */
/*                                                        */
/* Data Access API */
/*                                                        */
/*                                                        */
/**********************************************************/


function downloadFile($path_file){
    /* ファイルの存在確認 */
    if (!file_exists($path_file)) {
        die("Error: File(".$path_file.") does not exist");
    }

    /* オープンできるか確認 */
    if (!($fp = fopen($path_file, "r"))) {
        die("Error: Cannot open the file(".$path_file.")");
    }
    fclose($fp);

    /* ファイルサイズの確認 */
    if (($content_length = filesize($path_file)) == 0) {
        die("Error: File size is 0.(".$path_file.")");
    }

    /* ダウンロード用のHTTPヘッダ送信 */
    header("Content-Disposition: inline; filename=\"".basename($path_file)."\"");
    header("Content-Length: ".$content_length);
    header("Content-Type: application/octet-stream");

    /* ファイルを読んで出力 */
    if (!readfile($path_file)) {
        die("Cannot read the file(".$path_file.")");
    }
}



function iInsertRequest($url, $branch, $title, $session, $categoryid, $inlink, $ip, $prefix){

	$brstr = 'main';
	if($branch){
		$today = date("Ymd");
		$brstr = "branch".$today;
	}

	$sql = 'INSERT INTO '.$prefix.'entry (`url`, `branch`, `title`, `session`, `categoryid`, `inlink`, `req_date`, `ip`, `state`) VALUES ("'.mysql_escape_string($url).'", "'.mysql_escape_string($brstr).'", "'.mysql_escape_string($title).'", "'.mysql_escape_string($session).'", "'.mysql_escape_string($categoryid).'", "'.mysql_escape_string($inlink).'", NOW(), "'.mysql_escape_string($ip).'", "new" )';
	
	if(mysql_query($sql)){
		return 200;
	} else {
		if( preg_match('/^Duplicate entry.+/',mysql_error()) ){
			return 400;
		}
		else{
			die(mysql_error());
			return 500;
		}
	}
}

function iInsertReport($filename, $mailaddr, $report, $ip, $prefix){
	$sql = 'INSERT INTO '.$prefix.'report (`filename`, `mailaddr`, `report`, `ip`, `rep_date`) VALUES ("'.mysql_escape_string($filename).'", "'.mysql_escape_string($mailaddr).'", "'.mysql_escape_string($report).'", "'.mysql_escape_string($ip).'", NOW() )';
	
	if(mysql_query($sql)){
		return 200;
	} else {
		die(mysql_error());
		return 500;
	}
}

function aGetEntrys($prefix,$category){
	$sql = 'SELECT * FROM '.$prefix.'entry \
	WHERE `category`="'.mysql_escape_string($category).'" order by name desc';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}
function aGetEntryById($prefix,$id){
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE `id`="'.mysql_escape_string($id).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_array($result);
	return $row;
}

function aGetRetrospectiveRequests($prefix,$url){
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE `url` LIKE "'.$url.'%" order by `req_date` desc limit 2';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function aGetRecentRequests($prefix,$number){
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE (`state`= "new") OR (`state`= "converting") order by `req_date` desc limit '.$number;
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function aGetRecentConverts($prefix,$number){
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE (`state`= "success") order by `req_date` desc limit '.$number;
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function aGetTopPlays($prefix,$number){
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE (`state`= "success") order by `count` desc limit '.$number;
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

/*
RecentTopPlays

SELECT * FROM entry WHERE (`state`= "success") and fin_date between date_add(date(now()), interval -6 day) and date_format(now(), '%Y.%m.%d') order by `count`  desc limit 10
*/

function sGetADKeyword(){
	$sql = 'SELECT * FROM `adkey` ORDER BY rand() limit 1';
	
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	$sKeyword = 0;
	while($row = mysql_fetch_array($result)) {
		$sKeyword = $row[1];
	}
	return $sKeyword;
}

function aGetCategoryLists($prefix){
	$sql = 'SELECT * FROM '.$prefix.'category';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function aGetQuerySearchAll($prefix,$query,$skey,$sorder){

	$safe_query = mysqlEscapeSmart($query);
	$safe_query = "%".$safe_query."%";
	
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE (`state`= "success") AND (`url` LIKE "'.$safe_query.'" OR `title` LIKE "'.$safe_query.'" OR `session` LIKE "'.$safe_query.'") order by `'.$skey.'` '.$sorder;
	
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}


function aGetQuerySearchWithHidden($prefix,$query,$skey,$sorder){

	$safe_query = mysqlEscapeSmart($query);
	$safe_query = "%".$safe_query."%";
	
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE (`state`= "success" OR `state`= "hidden") AND (`url` LIKE "'.$safe_query.'" OR `title` LIKE "'.$safe_query.'" OR `session` LIKE "'.$safe_query.'") order by `'.$skey.'` '.$sorder;
	
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function aGetFilenameSearchAll($prefix,$query){

	$safe_query = mysqlEscapeSmart($query);
	//$safe_query = "%".$safe_query."%";
	
	$sql = 'SELECT * FROM '.$prefix.'entry WHERE `filename` = "'.$safe_query.'"';
	
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function bUpdateFilenameCount($prefix,$query){
	$safe_query = mysqlEscapeSmart($query);
	$sql = 'UPDATE '.$prefix.'entry SET count = count + 1 WHERE `filename` = "'.$safe_query.'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function bDeleteRadio($prefix,$query){
	$safe_query = mysqlEscapeSmart($query);
	$sql = 'UPDATE '.$prefix.'entry SET state = "hidden" WHERE `filename` = "'.$safe_query.'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}
function bRestoreRadio($prefix,$query){
	$safe_query = mysqlEscapeSmart($query);
	$sql = 'UPDATE '.$prefix.'entry SET state = "success" WHERE `filename` = "'.$safe_query.'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function iGetTotalCount($prefix){

	$sql = 'select sum(`count`) from `entry`';
	
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	$iCount = 0;
	while($row = mysql_fetch_array($result)) {
		$iCount = $row[0];
	}
	return $iCount;
}

function iGetTotalRadio($prefix){

	$sql = 'SELECT count(*) FROM '.$prefix.'entry WHERE (`state`= "success")';
	
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	$iCount = 0;
	while($row = mysql_fetch_array($result)) {
		$iCount = $row[0];
	}
	return $iCount;
}


function aGetTitleStats($prefix){
	
	$sql = 'SELECT title FROM '.$prefix.'entry WHERE (`state`= "success") order by `req_date` desc limit 1000';
	
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		if (!empty($row[0])){
			array_push($aTmp, $row[0]);
		}
	}
	
	$aTmp = array_count_values($aTmp);
	return $aTmp;
}

/* place */
function aGetPlaceLists($username,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'place WHERE `username`="'.mysql_escape_string($username).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}
/*
function aGetPlaceShortLists($username,$prefix){
	$sql = 'SELECT name FROM '.$prefix.'place WHERE `username`="'.mysql_escape_string($username).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_row($result)) {
		array_push($aTmp, $row[0]);
	}
	return $aTmp;
}
*/
function aGetPlaceListsById($username,$prefix,$id){
	$sql = 'SELECT * FROM '.$prefix.'place WHERE `username`="'.mysql_escape_string($username).'" and `placeid`="'.mysql_escape_string($id).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_array($result);
	return $row;
}

function bInsertPlace($username,$placename,$realname,$placeurl,$google,$prefix){
	$sql = 'INSERT INTO '.$prefix.'place (`username`, `placename`, `realname`, `placeurl`, `google`) VALUES ("'.mysql_escape_string($username).'", "'.mysql_escape_string($placename).'", "'.mysql_escape_string($realname).'", "'.mysql_escape_string($placeurl).'", "'.mysql_escape_string($google).'")';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function bUpdatePlace($id,$username,$placename,$realname,$placeurl,$google,$prefix){
	$sql = 'UPDATE '.$prefix.'place SET `placename`="'.mysql_escape_string($placename).'", `realname`="'.mysql_escape_string($realname).'", `placeurl`="'.mysql_escape_string($placeurl).'", `google`="'.mysql_escape_string($google).'" WHERE `placeid`="'.mysql_escape_string($id).'" AND `username`="'.mysql_escape_string($username).'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function bDeletePlace($id,$username,$prefix){
	$sql = 'DELETE FROM '.$prefix.'place WHERE `placeid`="'.mysql_escape_string($id).'" AND `username`="'.mysql_escape_string($username).'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}


/* state */
function aGetStateLists($username,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'state WHERE `username`="'.mysql_escape_string($username).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}


function aGetStateListsById($username,$prefix,$id){
	$sql = 'SELECT * FROM '.$prefix.'state WHERE `username`="'.mysql_escape_string($username).'" and `stateid`="'.mysql_escape_string($id).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_array($result);
	return $row;
}


function bInsertState($username,$statename,$email,$messenger,$telephone,$encounter,$prefix){
	$sql = 'INSERT INTO '.$prefix.'state (`username`, `statename`, `email`, `messenger`, `telephone`, `encounter`) VALUES ("'.mysql_escape_string($username).'", "'.mysql_escape_string($statename).'", "'.mysql_escape_string($email).'", "'.mysql_escape_string($messenger).'", "'.mysql_escape_string($telephone).'", "'.mysql_escape_string($encounter).'")';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function bUpdateState($id,$username,$statename,$email,$messenger,$telephone,$encounter,$prefix){
	$sql = 'UPDATE '.$prefix.'state SET `statename`="'.mysql_escape_string($statename).'", `messenger`="'.mysql_escape_string($messenger).'", `telephone`="'.mysql_escape_string($telephone).'", `encounter`="'.mysql_escape_string($encounter).'" WHERE `stateid`="'.mysql_escape_string($id).'" AND `username`="'.mysql_escape_string($username).'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function bDeleteState($id,$username,$prefix){
	$sql = 'DELETE FROM '.$prefix.'state WHERE `stateid`="'.mysql_escape_string($id).'" AND `username`="'.mysql_escape_string($username).'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}


/* presence */
function aGetPresenceLists($username,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'presence WHERE `username`="'.mysql_escape_string($username).'" order by date desc';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}
function aGetPresenceListsById($username,$prefix,$id){
	$sql = 'SELECT * FROM '.$prefix.'presence WHERE `username`="'.mysql_escape_string($username).'" and `presenceid`="'.mysql_escape_string($id).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_array($result);
	return $row;
}

function bInsertPresence($username,$presencename,$presenceurl,$placeid,$stateid,$comment,$prefix){
	$sql = 'INSERT INTO '.$prefix.'presence (`date`, `username`, `presencename`, `presenceurl`, `placeid`, `stateid`, `comment`) VALUES (NOW(), "'.mysql_escape_string($username).'", "'.mysql_escape_string($presencename).'", "'.mysql_escape_string($presenceurl).'", "'.mysql_escape_string($placeid).'", "'.mysql_escape_string($stateid).'", "'.mysql_escape_string($comment).'")';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function bDeletePresence($id,$username,$prefix){
	$sql = 'DELETE FROM '.$prefix.'presence WHERE `presenceid`="'.mysql_escape_string($id).'" AND `username`="'.mysql_escape_string($username).'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}


// 最近のプレゼンス取得(片思いマージ済み)
function aGetRecentPresenceLists($username,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'presence WHERE `username` ="'.mysql_escape_string($username).'" OR `username` = ANY (SELECT `friendname` FROM friend WHERE `username` = "'.mysql_escape_string($username).'") order by date desc limit 10';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

/* metadata */
function aGetMetaLists($username,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'meta WHERE `username`="'.mysql_escape_string($username).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}
function aGetMetaListsById($username,$prefix,$id){
	$sql = 'SELECT * FROM '.$prefix.'meta WHERE `username`="'.mysql_escape_string($username).'" and `metaid`="'.mysql_escape_string($id).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_array($result);
	return $row;
}
function aGetMetaListsByNewest($username,$prefix,$type){
	$sql = 'SELECT * FROM '.$prefix.'meta order by metaid desc WHERE `username`="'.mysql_escape_string($username).'" and `metatype`="'.mysql_escape_string($type).'" limit 1';
	if(!$result=mysql_query($sql)){
		//die('MySQL query failed. MySQL said: '.mysql_error());
		// no results
		return NULL;
	}
	$row = mysql_fetch_array($result);
	return $row;
}

function aGetPresenceByNewest($username,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'presence WHERE `username` = "'.mysql_escape_string($username).'" order by date desc limit 1';
	if(!$result=mysql_query($sql)){
		//die('MySQL query failed. MySQL said: '.mysql_error());
		// no results
		return NULL;
	}
	$row = mysql_fetch_array($result);
	return $row;
}

function aGetUserLists($username,$prefix){
	// 自分を除く場合
	//$sql = 'SELECT * FROM '.$prefix.'users WHERE `username` != "'.mysql_escape_string($username).'" AND status = 2 order by RAND()';
	// 自分も含める場合
	$sql = 'SELECT * FROM '.$prefix.'users WHERE status = 2 order by RAND()';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function aGetFriendLists($username,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'friend WHERE `username` = "'.mysql_escape_string($username).'" order by RAND()';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$aTmp = array();
	while($row = mysql_fetch_array($result)) {
		array_push($aTmp, $row);
	}
	return $aTmp;
}

function bCheckFriend($username,$friendname,$prefix){
	$sql = 'SELECT * FROM '.$prefix.'friend WHERE `username` = "'.mysql_escape_string($username).'" AND `friendname` = "'.mysql_escape_string($friendname).'"';
	if(!$result=mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	if(@mysql_num_rows($result) == 0){
		return false;
	} else {
		return true;
	}
}

function bInsertFriend($username,$friendname,$prefix){
	$sql = 'INSERT INTO '.$prefix.'friend (`username`, `friendname`) VALUES ("'.mysql_escape_string($username).'", "'.mysql_escape_string($friendname).'")';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}

function bDeleteFriend($username,$friendname,$prefix){
	$sql = 'DELETE FROM '.$prefix.'friend WHERE `username`="'.mysql_escape_string($username).'" AND `friendname`="'.mysql_escape_string($friendname).'"';
	if(mysql_query($sql)){
		return true;
	} else {
		die(mysql_error());
		return false;
	}
}


function sAddHyperLink($sStr,$sLink){
	if($sLink){
		return "<a href='".$sLink."' target='_blank'>".$sStr."</a>";
	}
	else{
		return $sStr;
	}
}

/* get date diff */
function sGetDateDiff($sql_date1,$sql_date2){
	$result = strtotime($sql_date2) - strtotime($sql_date1);
	//$result = intval( $result / ( 24 * 60 * 60))+1;
	$strDiff = "";
	if($result < 300){	$strDiff =  "5分まえ";}
	elseif($result < 1800){	$strDiff =  "15分まえ";}
	elseif($result < 3600){	$strDiff =  "30分まえ";}
	elseif($result < 86400)
	{
		$sTmp = intval($result / 3600);
		$strDiff = $sTmp."時間まえ";
	}
	elseif($result < 86400*31)
	{
		$sTmp = intval($result / 86400);
		$strDiff = $sTmp."日まえ";
	}
	elseif($result < 86400*365)
	{
		$sTmp = intval($result / (86400*31));
		$strDiff = $sTmp."ヶ月まえ";
	}
	else
	{
		$strDiff =  "結構まえ";
	}
	return "(".$strDiff.")";
}

/* get image and no cache! */

$sUniqCode = "?".time();
$sMetaPath = "/home/httpd/html/prezex/meta/";
function sGetMetaUserImageFile($username){
	// global
	global $sMetaPath;
	global $sUniqCode;
	
	if(file_exists($sMetaPath.$username.".jpg"))
	{
		return $username.".jpg".$sUniqCode;
	}
	/*
	elseif(file_exists($sMetaPath.$username.".gif"))
	{
		return $username.".gif".$sUniqCode;
	}
	elseif(file_exists($sMetaPath.$username.".png"))
	{
		return $username.".png".$sUniqCode;
	}
	elseif(file_exists($sMetaPath.$username.".bmp"))
	{
		return $username.".bmp".$sUniqCode;
	}
	*/
	else
	{
		return "noimage.gif".$sUniqCode;
	}
}

function sGetPlaceGoogleMap($sGoogleMapLocate,$sPath){
	global $sUniqCode;
	if($sGoogleMapLocate){
		if ( preg_match ( "/\((.+), (.+)\)/i", $sGoogleMapLocate, $match ) ) {
			$URL = "http://maps.anoncom.net/gmap/".$match[1].",".$match[2]."/W100H100/4/";
			return $URL;
		}
		return $sPath."nomaps.gif".$sUniqCode;
	}
	else{
		return $sPath."nomaps.gif".$sUniqCode;
	}
}

// 安全性を確保するために変数をクオートする
// http://phpspot.net/php/man/php/function.mysql-real-escape-string.html
function mysqlQuoteSmart($value)
{
    // Stripslashes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // 数値以外をクオートする
    if (!is_numeric($value)) {
        $value = "'" . mysql_real_escape_string($value) . "'";
    }
    return $value;
}
function mysqlEscapeSmart($value)
{
    // Stripslashes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // 数値以外をクオートする
    if (!is_numeric($value)) {
        $value = mysql_real_escape_string($value);
    }
    return $value;
}

function sanitize_text($text) {
	return htmlspecialchars(preg_replace("/^\n+|\n+$/", "", strtr($text, array ("\r\n" => "\n", "\r" => "\n"))));
}

function sanitize_number($num, $def = 0) {
	$num = intval($num);
	return (is_numeric($num)) ? $num : $def;
}

function sanitize_color($color, $def = "000000") {
	return "#" . (preg_match( "/[0-9A-Fa-f]{6}/i", $color, $match)) ? $match[0] : $def;
}

function sGetShortURL($url, $length) {
	if (strlen($url) > $length) {
		return preg_replace("/^(http:\/\/.+?\/).+(\/.+?\.\w\w\w)$/","\\1...\\2",$url);
	}
	return $url;
}

function sGetBranchIndicator($branch) {
	if ($branch == 'main'){
		return "(fixed)";
	}
	elseif(preg_match( "/branch([0-9]{4})([0-9]{2})([0-9]{2})/", $branch, $match)){
		return "(".$match[1]."/".$match[2]."/".$match[3].")";
	}
	else{
		return "(unknown)";
	}
}


/**********************************************************/

// Check to see if an email address exists in the database
// Return true if the user exists
// the $username argument is for the configuration page. if it is set, if the user that has the email specified is $username, the script will return false
function check_email_exists($email,$prefix,$username=''){

	// test mode
	return false;

	if(empty($username)){
		$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE email="'.mysql_escape_string($email).'"');
	} else {
		$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE email="'.mysql_escape_string($email).'" and `username`!="'.mysql_escape_string($username).'"');
	}
	if(@mysql_num_rows($result) == 0){
		return false;
	} else {
		return true;
	}
}

// Count the number of users in the database
function count_users ($prefix){
	$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE status=2');
	return @mysql_num_rows($result);
}

// count the number of users who have no verified their email
function count_inactive_users ($prefix){
	$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE status=0');
	return @mysql_num_rows($result);
}

function UpdateUserField($username,$field,$value,$prefix){
	$sql = 'UPDATE `'.$prefix.'users` SET `'.$field.'` = \''.$value.'\' WHERE `username` = \''.$username.'\'';
	if(!mysql_query($sql)){
		die('MySQL query failed. '.mysql_error());
	}
}

function FormatPhoneNumber($phone){
	if(strlen($phone) != 10){
		return($phone);
	}
	$area = substr($phone,0,3);
	$prefix = substr($phone,3,3);
	$number = substr($phone,6,4);
	// comment out
	//$phone = "(".$area.") ".$prefix." ".$number;
	return($phone);
}

function count_pending_users($prefix){
	$result = mysql_query('SELECT id FROM '.$prefix.'users WHERE status=1');
	return mysql_num_rows($result);
}

// This function gets the email body and subject from the text files in /emails
function get_email_subject ($prefix,$email_name){
	$sql = 'SELECT subject FROM '.$prefix.'emails WHERE `name`=\''.$email_name.'\'';
	if(!$result = mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_row($result);
	$output = $row[0];
	return $output;
}

function get_email_body ($firstname,$lastname,$email,$username,$password,$login_url,$deadlock_url,$admin_email,$prefix,$email_name){
	$sql = 'SELECT body FROM '.$prefix.'emails WHERE `name`=\''.$email_name.'\'';
	if(!$result = mysql_query($sql)){
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
	$row = mysql_fetch_row($result);
	$output = $row[0];
	$output = str_ireplace(
	array('<%FirstName%>','<%LastName%>','<%Email%>','<%Username%>','<%Password%>','<%LoginURL%>','<%AdminEmail%>','<%DeadlockURL%>'),
	array($firstname,$lastname,$email,$username,$password,$login_url,$admin_email,$deadlock_url), $output);
	return $output;
}

// gets the body of an email, and replaces codes with values from the database
// if the send veriable is set to true, not only will the script genertate the email, but it will send it
function get_email_body_sql($emailname,$username,$prefix,$send=false){
	$sql = 'SELECT body FROM '.$prefix.'emails WHERE `name`=\''.$emailname.'\'';
	if(!$result = mysql_query($sql)){
		die('MySQL query failed. MySQL said:'.mysql_error());
	}
	$row = mysql_fetch_row($result);
	$output = stripslashes($row[0]);

	$config = get_config($prefix);

	$sql = 'SELECT * FROM '.$prefix.'users WHERE `username`="'.mysql_escape_string($username).'"';
	if($result = mysql_query($sql)){
		while (($row = mysql_fetch_array($result)) != false) {
			$output = str_ireplace(
			array('<%FirstName%>','<%LastName%>','<%Email%>','<%Username%>','<%Password%>','<%LoginURL%>','<%AdminEmail%>','<%DeadlockURL%>'),
			array($row['firstname'],$row['lastname'],$row['email'],$row['username'],$row['password'],$config['protected_area_url'],$config['admin_email'],$config['deadlock_url']), $output);
			if($send){
				sendmail($row['email'],$config['admin_email'],get_email_subject($prefix,$emailname),$output);
			}
		}
	} else {
		die('MySQL query failed. MySQL said:'.mysql_error());
	}
	return $output;
}

// Adds a user to the database
// Must first connect to mysql database
// 4th argument specifies whether or not admin has to approve users first.
//Returns true if successful.
function add_dbuser($firstname,$lastname,$email,$phone,$country,$username,$password,$prefix,$approve=true,$admin=false){
	if(validate_name($firstname) && validate_name($lastname) && validate_email_address($email) && validate_password($password) && validate_username($username)){
		if(!$approve || $admin) $status=2; else $status=1;
		$sql = 'INSERT INTO `'.$prefix.'users` ( `id` , `firstname` , `lastname` , `email`, `phone` , `country` , `username` , `password` , `status` , `registration_timestamp` ) '
		. ' VALUES ( \'\', \''.mysql_escape_string($firstname).'\', \''.mysql_escape_string($lastname).'\', \''.mysql_escape_string($email).'\', \''.mysql_escape_string($phone).'\', \''.mysql_escape_string($country).'\', \''.mysql_escape_string($username).'\', \''.mysql_escape_string($password).'\', \''.$status.'\', \''.time().'\')';
		if(mysql_query($sql)){
			return true;
		} else {
			die(mysql_error());
			return false;
		}
	} else {
		die(mysql_error());
		return false;
	}
}
// same as above function except this is used on the user registration page
function user_add_dbuser($firstname,$lastname,$email,$phone,$country,$username,$password,$prefix,$status='0'){
	if(validate_name($firstname) && validate_name($lastname) && validate_email_address($email) && validate_password($password) && validate_username($username)){
		$sql = 'INSERT INTO `'.$prefix.'users` ( `id` , `firstname` , `lastname` , `email`, `phone` , `country` , `username` , `password` , `status` , `registration_timestamp` ) '
		. ' VALUES ( \'\', \''.mysql_escape_string($firstname).'\', \''.mysql_escape_string($lastname).'\', \''.mysql_escape_string($email).'\', \''.mysql_escape_string($phone).'\', \''.mysql_escape_string($country).'\', \''.mysql_escape_string($username).'\', \''.mysql_escape_string($password).'\', \''.$status.'\', \''.time().'\')';
		if(mysql_query($sql)){
			return true;
		} else {
			die(mysql_error());
			return false;
		}
	} else {
		die(mysql_error());
		return false;
	}
}

// this function adds the email verification code to the database
function AddEmailVerifyCode($username,$code,$prefix){
	$sql = 'UPDATE `'.$prefix.'users` SET `email_verify_code` = \''.mysql_escape_string($code).'\' WHERE `username` = \''.mysql_escape_string($username).'\' LIMIT 1;';
	if(!mysql_query($sql)){
		die('MySQL query failed. MySQL said:'.mysql_error());
	}
}

// This function removes a user from the mysql database
// $username- the user to remove,  $prefix- the prefix of the users table
function remove_user ($username,$table_prefix){
	$sql = 'DELETE FROM `'.$table_prefix.'users` WHERE `username` = \''.mysql_escape_string($username).'\''
	. ' ';
	if(mysql_query($sql)){
		return true;
	} else {
		return false;
	}
}

// Starts session
function admin_sessions ($expire){
	// set the session name so it does not conflict
	session_name('admin_sid');
	// Start the session
	session_start();

	// check to see if the current session has expired
	if(isset($_SESSION['start_time'])){
		if((time() - $_SESSION['start_time']) > $expire){
			session_destroy();
			redirect('./login.php');
		}
	}

	// if session has not expired, set session start time
	$_SESSION['start_time'] = time();
}

// log a failed login attempt for an ip address
function LogFailedLogin($prefix,$username){
	$sql = 'INSERT INTO `'.$prefix.'logins` (`type`, `username`, `timestamp`, `user_agent`, `ip`) VALUES (\'failed\', \''.mysql_escape_string($username).'\', \''.time().'\', \''.$_SERVER['HTTP_USER_AGENT'].'\', \''.$_SERVER['REMOTE_ADDR'].'\');';
	mysql_query($sql) or die('The MySQL query failed. MySQL said: '.mysql_error());
}

// return the number of failed logins for an ip address
function CheckFailedLogins($prefix,$ip){
	$sql = 'SELECT id FROM '.$prefix.'logins WHERE '.time().'-`timestamp` < 600 and `ip`=\''.$ip.'\' and `type`="failed" and `username`="admin"';
	$result = mysql_query($sql) or die('The MySQL query failed. MySQL said: '.mysql_error());
	return @mysql_num_rows($result);
}

// remove all users that have not verified their email address after 72 hours (259200 seconds)
function PruneInactiveUsers($prefix){
	$sql = 'DELETE FROM '.$prefix.'users WHERE `status`=1 and '.time().'-`registration_timestamp` > 259200';
	mysql_query($sql) or die('The MySQL query failed. MySQL said: '.mysql_error());
}

// This will generate an html dropdown menu of all users in the database.
// This is used on the email page to generate the menu.
function generate_user_menu ($prefix,$selected){
	if($result = mysql_query('SELECT * FROM '.$prefix.'users WHERE status=2 ORDER BY username')){
		if(@mysql_num_rows($result) > 0){
			while (($row = mysql_fetch_array($result)) != false) {
				$username[] = stripslashes($row['username']);
				$id[] = stripslashes($row['id']);
				$name[] = stripslashes($row['firstname']).' '.stripslashes($row['lastname']);
			}
			$code = null;
			for($i=0;$i<mysql_num_rows($result);$i++){
				if($selected == $username[$i]) $select = ' selected="selected"'; else $select=null;
				$code .= '<option value="'.$id[$i].'"'.$select.'>'.$name[$i]. ' - ' .$username[$i].'</option>'."\n";
			}
			return $code;
		} else {
			return null;
		}
	} else {
		die('There was an error querying MySQL.');
	}
}

// generate request list rows
function generate_request_list($prefix){
	if($result = mysql_query('SELECT * FROM '.$prefix.'users WHERE status=1 ORDER BY lastname')){
		if(@mysql_num_rows($result) > 0){
			while (($row = mysql_fetch_array($result)) != false) {
				$username[] = stripslashes($row['username']);
				$email[] = stripslashes($row['email']);
				$name[] = stripslashes($row['lastname']).', '.stripslashes($row['firstname']);
			}
			$code = "";
			for($i=0;$i < mysql_num_rows($result);$i++){
				$code .= '<tr class="style2"><td>'.$name[$i].'</td><td>'.$username[$i].'</td><td>'.$email[$i].'</td><td><a href="./userinfo.php?user='.$username[$i].'&ref=request"><img src="../images/info15px.gif" alt="Info" border="0" title="More Information" /></a> <a href="#" onclick="denyuser(\''.$username[$i].'\')"><img src="../images/delete15px.gif" alt="Deny" border="0" title="Deny" /></a> <a href="#" onclick="acceptuser(\''.$username[$i].'\')"><img src="../images/accept15px.gif" alt="Accept" border="0" title="Accept" /></a></tr>'."\n";
			}
			return $code;
		} else {
			return '<tr><td colspan="4"><span class="style11">There are currently no users pending approval.</span></td></tr>';
		}
	} else {
		die('The MySQL query failed. Please make sure your MySQL settings are correct.');
	}
}

// this function is used on the configuration page to check whether or not a checkbox field should be checked by default
function ConfigCheckboxCheck($Submitted,$PostField,$ConfigOption){
	if(isset($Submitted)){
		if(isset($PostField)){
			return 'checked="checked" ';
		} else {
			return '';
		}
	} else {
		if($ConfigOption == 'true'){
			return 'checked="checked" ';
		} else {
			return '';
		}
	}
}

// this function is used on the configuration page to print out the date format selection menu
function ConfigDateSelects($PostField,$ConfigOption){
	$date_formats = array('D d M, Y','D d M, Y g:i a','D d M, Y H:i','D M d, Y','D M d, Y g:i a','D M d, Y H:i','jS F Y','jS F Y, g:i a','jS F Y, H:i','F jS Y','F jS Y, g:i a','F jS Y, H:i','j/n/Y','j/n/Y, g:i a','j/n/Y, H:i','n/j/Y','n/j/Y, g:i a','n/j/Y, H:i','Y-m-d','Y-m-d, g:i a','Y-m-d, H:i');
	$current_time = time();
	$buffer = '';
	if(empty($PostField)){
		$selected_format = $ConfigOption;
	} else {
		$selected_format = $PostField;
	}
	foreach ($date_formats as $format) {
		if($format == $selected_format) $selected = ' selected="selected" '; else $selected=NULL;
		$buffer .= '<option value="'.$format.'"'.$selected.'>'.htmlentities(date($format,$current_time)).'</option>'."\n";
	}
	return $buffer;
}

// this function is used on the configuration page to print out the verification type selection menu
function ConfigVerificationSelects($PostField,$ConfigOptionVerifyEmail,$ConfigOptionRequireAdminAccept){
	$options = Array('None'=>'0','Email Confirmation'=>'1','Admin Approval'=>'2','Email and Admin'=>'3');
	$buffer = '';
	if(empty($PostField)){
		if($ConfigOptionVerifyEmail=='true' && $ConfigOptionRequireAdminAccept=='true'){
			$selected_validation = '3';
		} elseif($ConfigOptionVerifyEmail=='true' && $ConfigOptionRequireAdminAccept!='true') {
			$selected_validation = '1';
		} elseif($ConfigOptionVerifyEmail!='true' && $ConfigOptionRequireAdminAccept=='true'){
			$selected_validation = '2';
		} elseif($ConfigOptionVerifyEmail!='true' && $ConfigOptionRequireAdminAccept!='true'){
			$selected_validation = '0';
		}
	} else {
		$selected_validation = $PostField;
	}
	foreach ($options as $text => $value){
		if($selected_validation == $value) $selected = ' selected="selected" '; else $selected = NULL;
		$buffer .= '<option value="'.$value.'"'.$selected.'>'.$text.'</option>';
	}
	return $buffer;
}

// this function checks whther or not a radio button should be selected. this function is for the configuration page.
function ConfigRadioCheck($PostField,$ConfigOption,$Button){
	// which button are we checking, on or off?
	if($Button=='off'){
		if(!isset($PostField)){
			if($ConfigOption!='true'){
				return ' checked="checked"';
			}
		} elseif($PostField!='true') {
			return ' checked="checked"';
		}
	} else {
		if(!isset($PostField)){
			if($ConfigOption=='true'){
				return ' checked="checked"';
			}
		} elseif($PostField=='true') {
			return ' checked="checked"';
		}
	}
}

// this function gives text fields a default value. this function is for the configuration page.
function ConfigTextField($PostField,$ConfigOption){
	if(isset($PostField)){
		return $PostField;
	} else {
		return $ConfigOption;
	}
}

function ConfigAuthTypeSelects($PostField,$ConfigOption){
	if(!empty($PostField)){
		$selected = $PostField;
	} else {
		$selected = $ConfigOption;
	}
	$options = array('Basic'=>'false','Digest'=>'true');
	$buffer = '';
	foreach($options as $name => $value){
		if($value == $selected) $isselected = ' selected="selected"'; else $isselected = null;
		$buffer .= '<option value="'.$value.'"'.$isselected.'>'.$name.'</option>';
	}
	return $buffer;
}

// this function is to approve a user account
function accept_user_request($username,$prefix){
	$sql = 'UPDATE `'.$prefix.'users` SET `status` = \'2\' WHERE `username`=\''.$username.'\'';
	if(mysql_query($sql)){
		return true;
	} else {
		return false;
	}
}

// Take config options from database and put them in an array
function get_config($prefix){
	if($result = mysql_query('SELECT * FROM '.$prefix.'config')){
		while (($row = mysql_fetch_array($result)) != false) {
			$config[$row['option_name']] = $row['value'];
		}
		return $config;
	} else {
		die('MySQL query failed. MySQL said: '.mysql_error());
	}
}

// Connect to the mysql database
function db_connect ($mysql_user,$mysql_password,$mysql_database,$mysql_server){
	if(!@mysql_connect($mysql_server,$mysql_user,$mysql_password)){
		die('障害発生中につき復旧までお待ち下さい。 This script could not connect to MySQL and therefore has aborted.');
	}
	if(!@mysql_select_db($mysql_database)){
		die('障害発生中につき復旧までお待ち下さい。 This script could not select the MySQL database.');
	}
	mysql_query('SET NAMES utf8');
}

// This pulls all info from the database and rewrites the htpasswd.
function generate_htpasswd ($prefix){

	$config = get_config($prefix);

	$sql = 'SELECT `username`,`password` FROM '.$prefix.'users WHERE `status`=2';
	if($result=mysql_query($sql)){
		if(@mysql_num_rows($result) > 0){
			$buffer = '';
			for ($i=0;($row = mysql_fetch_array($result)) != false;$i++) {
				if($config['digest_auth']=='true'){
					$user_password = enc_pass($row['password'],true,$row['username'],$config['protected_area_name']);
					$buffer .= $row['username'].':'.$config['protected_area_name'].':'.$user_password;
				} else {
					$user_password = enc_pass($row['password']);
					$buffer .= $row['username'].':'.$user_password;
				}
				if(mysql_num_rows($result) != $i) $buffer .= "\n";
			}
		} else {
			$buffer = ' ';
		}
	} else {
		die("MySQL query failed. MySQL said: ".mysql_error());
	}

	$htpasswd_path = $_SERVER['DOCUMENT_ROOT'].$config['htpasswd_path'];
	$htaccess_path = $_SERVER['DOCUMENT_ROOT'].$config['htaccess_path'];

	$handle = fopen($htpasswd_path,'w') or die('Deadlock could not open the htpasswd file for writing. '.$htpasswd_path);
	fwrite($handle,$buffer) or die('Deadlock could not write the htpasswd file. '.$htpasswd_path);
	fclose($handle);
}

// generate the htaccess file
function generate_htaccess($prefix){
	$config = get_config($prefix);

	if($config['htpasswd_relative']=='true'){
		$htpasswd_path = $_SERVER['DOCUMENT_ROOT'].$config['htpasswd_path'];
		$htaccess_path = $_SERVER['DOCUMENT_ROOT'].$config['htaccess_path'];
	} else {
		$htpasswd_path = $config['htpasswd_path'];
		$htaccess_path = $config['htaccess_path'];
	}

	if($config['digest_auth']=='true'){
		$authtype = 'Digest';
		$authuserfile = 'AuthDigestFile "'.$htpasswd_path.'"';
	} else {
		$authtype = 'Basic';
		$authuserfile = 'AuthUserFile "'.$htpasswd_path.'"';
	}

	if(!empty($config['err_401_doc'])){
		$err_401_doc = $config['err_401_doc'];
	} else {
		$error_401_doc = null;
	}

	$buffer = "AuthName \"".$config['protected_area_name']."\"\nAuthType ".$authtype."\n".$authuserfile."\nrequire valid-user";
	if(!empty($config['err_401_doc'])){
		$buffer .= "\nErrorDocument 401 " . $config['err_401_doc'];
	}

	$handle = fopen($htaccess_path,'w') or die('Deadlock could not open the htaccess file for writing. '.$htaccess_path);
	fwrite($handle,$buffer) or die('Deadlock could not write the htaccess file. '.$htaccess_path);
	fclose($handle);
}

// Update an option on the configuration page
function ConfigUpdateOption($Option,$OptionDisplayName,$Value,$prefix){
	$sql = 'UPDATE `'.$prefix.'config` SET `value` = \''.$Value.'\' WHERE `option_name` = \''.$Option.'\'';
	if(mysql_query($sql)){
		return true;
	} else {
		die('While attempting to update "'.$Option.'" the MySQL query failed. MySQL said: '.mysql_error());
	}
}

// this option updates any pending user's status to verified
function ConfigUpdateApprovalStatus($prefix){
	$sql = 'UPDATE `'.$prefix.'users` SET `status` = \'2\' WHERE `status` = \'1\'';
	if(mysql_query($sql)){
		return true;
	} else {
		die('While attempting to update all user\'s status to validated, the MySQL query failed. MySQL said: '.mysql_error());
	}
}

// same as above function except updates users who have not validated their email
function ConfigUpdateInactiveStatus($prefix,$newstatus){
	$sql = 'UPDATE `'.$prefix.'users` SET `status` = \''.$newstatus.'\' WHERE `status` = \'0\'';
	if(mysql_query($sql)){
		return true;
	} else {
		die('While attempting to update all user\'s status to validated, the MySQL query failed. MySQL said: '.mysql_error());
	}
}

//Check to make sure a password meets password requirements.
//4th argument specifies whether the password must have a number and letter in it, yes by default
// Returns true if the password is good, false if it is not.
function validate_password ($password,$minlength=6,$maxlength=10,$number=1){
	if(strlen($password) >= $minlength && strlen($password) <= $maxlength){
		if($number){
			if(preg_match("/[A-Za-z]/",$password)&& preg_match("/[0-9]/",$password)){
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	} else {
		return false;
	}
}

// This will simply make sure usernames are the correct length and are alphanumeric
// Returns true if the username is valid.
function validate_username ($username,$minlength=5,$maxlength=15){
	if(strlen($username) >= $minlength && strlen($username) <= $maxlength){
		if(ctype_alnum($username)){
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// This will simply make sure names are the correct length and are alphanumeric
// Returns true if the username is valid.
function validate_name ($name,$minlength=1,$maxlength=15){
	if(mb_strlen($name) >= $minlength && strlen($name) <= $maxlength){
	
		/*
		if(ctype_alnum(str_replace(array('-',' '),null,$name))){
		*/		
		if(str_replace(array('-',' '),null,$name)){
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// Function to validate an email address. This will return true if the email address is valid.
function validate_email_address($email) {
	// First, we check that there's one @ symbol, and that the lengths are right
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}
	// Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++) {
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
			return false;
		}
	}
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) {
			return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++) {
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
				return false;
			}
		}
	}
	return true;
}
/*
function show_footer($version) {
	print 'Powered by <a href="http://phpdeadlock.sourceforge.net/">Deadlock User Management System</a>';
}
function show_user_footer($version) {
	return 'Powered by <a href="http://phpdeadlock.sourceforge.net/">Deadlock User Management System</a>';
}
*/
function show_footer($version) {
	print show_user_footer(1.0);
}
function show_user_footer($version) {
	$sTmp = '登録・バグ等のお問い合わせは <a href="mailto:radiholic@radiholic.com">radiholic@radiholic.com</a> までお願いします';
	$sTmp .= '<br>';
	$sTmp .= '"radiholic" - Copyright (C) 2012 <a href="http://twitter.com/pen2">@pen2</a>. All Rights Reserved. ';
	return $sTmp;
}

function sGetADCode() {

	srand((double)microtime()*1000000);
	$number=round(rand(0,100));
	
	if($number > 30){
		return sGetADCodes("radiholic_optimize");
	}
	else {
		return sGetADCodes("radiholic_supersky");
	}

	return "";
}

function sGetADCodes($code) {
	$sTmp = '';
	
	if($code=="radiholic_supersky"){
	$sTmp = '<div><script type="text/javascript"><!--
google_ad_client = "pub-1344927303196186";
/* radiholic_supersky */
google_ad_slot = "7844305743";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script></div>';
	}
	
	if($code=="radiholic_optimize"){
	$sTmp = '<div><script type="text/javascript"><!--
amazon_ad_tag = "radiholic-22"; amazon_ad_width = "728"; amazon_ad_height = "90"; amazon_ad_logo = "hide"; amazon_ad_border = "hide"; amazon_color_border = "FFFFFF";//--></script>
<script type="text/javascript" src="http://www.assoc-amazon.jp/s/ads.js"></script></div>';
	}
	
	return $sTmp;
}

function sGetADContents($string) {
	$sTmp = '<iframe src="http://rcm-jp.amazon.co.jp/e/cm?t=radiholic-22&o=9&p=48&l=st1&mode=music-jp&search='.$string.'&fc1=000000&lt1=_blank&lc1=1944C7&bg1=FFFFFF&f=ifr" marginwidth="0" marginheight="0" width="728" height="90" border="0" frameborder="0" style="border:none;" scrolling="no"></iframe>';
	return $sTmp;
}

function show_bottom_nav ($admin_path){
	print '<a href="'.$admin_path.'index.php">Home</a> |
	User List | 
	Requests | 
	New User | 
	Email | 
	Stats | 
	Config | 
	<a href="'.$admin_path.'login.php?cmd=logout">Logout</a>';
}

// This is a function to use when checking fields that can be set to optional.
function validate_optional_fields($string,$setting){
	if($setting=="true"){
		if(empty($string)){
			return false;
		} else {
			return true;
		}
	} else {
		return true;
	}
}

// If the string equals the value then returns true
// This is for a field that can be set to optional.
function match_string($string,$value,$option=1){
	if($option=="true"){
		if($string == $value){
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// this checks to see if the phone number is numeric,
// but also allows you to disable the check
function validate_phone($phone,$required_digits,$option){
	if($option=="true" || !empty($phone)){
		if(is_numeric($phone) && strlen($phone) >= $required_digits){
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

/**
  * Chop a string into a smaller string.
  *
  * @author      Aidan Lister <aidan@php.net>
  * @version     1.1.0
  * @link        http://aidanlister.com/repos/v/function.str_chop.php
  * @param       mixed  $string   The string you want to shorten
  * @param       int    $length   The length you want to shorten the string to
  * @param       bool   $center   If true, chop in the middle of the string
  * @param       mixed  $append   String appended if it is shortened
  */
function str_chop($string, $length = 60, $center = false, $append = null)
{
	// Set the default append string
	if ($append === null) {
		$append = ($center === true) ? '..' : '..';
	}

	// Get some measurements
	$len_string = strlen($string);
	$len_append = strlen($append);

	// If the string is longer than the maximum length, we need to chop it
	if ($len_string > $length) {
		// Check if we want to chop it in half
		if ($center === true) {
			// Get the lengths of each segment
			$len_start = $length / 2;
			$len_end = $len_start - $len_append;

			// Get each segment
			$seg_start = substr($string, 0, $len_start);
			$seg_end = substr($string, $len_string - $len_end, $len_end);

			// Stick them together
			$string = $seg_start . $append . $seg_end;
		} else {
			// Otherwise, just chop the end off
			$string = substr($string, 0, $length - $len_append) . $append;
		}
	}

	return $string;
}

// Redirect function. This must be used before any data is sent to the browser.
function redirect ($location){
	header('Location: '.$location);
	exit;
}

// check to see if admin is logged in, if not, redirect them to the login page
function admin_auth_check ($session_var, $location){
	if(!isset($session_var)){
		redirect($location);
	}
}
?>
