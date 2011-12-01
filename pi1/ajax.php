<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kevin Renskers [Dualdot Internet] <info@dualdot.nl>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * This is the file all AJAX functions post to
 *
 * @author	Kevin Renskers [Dualdot Internet] <info@dualdot.nl>
 */

/*************************************************************
 * Define Paths for including files
 *************************************************************/
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE', 'FE');
define('PATH_piScript', str_replace('/typo3conf/ext/hoicommunity/pi1/ajax.php','',str_replace('//','/', str_replace('\\','/', php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ? $_SERVER['PATH_TRANSLATED']:$_SERVER['SCRIPT_FILENAME']))));
define('PATH_site', PATH_piScript.'/');
define('PATH_t3lib', PATH_site."t3lib/");

if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
	define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
} elseif (@is_dir(PATH_site.'tslib/')) {
	define('PATH_tslib', PATH_site.'tslib/');
} else {
	$configured_tslib_path = '';

	// example:
	// $configured_tslib_path = '/var/www/mysite/typo3/sysext/cms/tslib/';

	define('PATH_tslib', $configured_tslib_path);
}

if (PATH_tslib=='') {
	die('Cannot find tslib/. Please set path by defining $configured_tslib_path in '.basename(PATH_thisScript).'.');
}

define("PATH_typo3conf", PATH_site."typo3conf/");
define("TYPO3_mainDir", "typo3/");
if (!@is_dir(PATH_typo3conf))	die("Cannot find configuration. pi file is probably executed from the wrong location.");

/*************************************************************
 * Start Timetracking. tslib_fe needs pi so I can't skip it
 *************************************************************/
require_once(PATH_t3lib."class.t3lib_timetrack.php");
$TT = new t3lib_timeTrack;

/*************************************************************
 * Include needed files
 *************************************************************/
require_once(PATH_t3lib."class.t3lib_div.php");
require_once(PATH_t3lib."class.t3lib_extmgm.php");
require_once(PATH_t3lib."config_default.php");
require_once(PATH_tslib."class.tslib_fe.php");
require_once(PATH_tslib."class.tslib_content.php");
require_once(PATH_t3lib."class.t3lib_page.php");
require_once(PATH_t3lib."class.t3lib_userauth.php");
require_once(PATH_tslib."class.tslib_feuserauth.php");
require_once(PATH_t3lib."class.t3lib_tstemplate.php");
require_once(PATH_t3lib."class.t3lib_cs.php");
require_once(PATH_t3lib."class.t3lib_db.php");
require_once(PATH_tslib."class.tslib_pibase.php");

$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');

/*************************************************************
 * Create $TSFE object (TSFE = TypoScript Front End)
 * Connecting to database
 *************************************************************/
$temp_TSFEclassName=t3lib_div::makeInstanceClassName("tslib_fe");
$TSFE = new $temp_TSFEclassName($TYPO3_CONF_VARS,t3lib_div::GPvar("id"),t3lib_div::GPvar("type"), t3lib_div::GPvar("no_cache"),t3lib_div::GPvar("cHash"), t3lib_div::GPvar("jumpurl"),t3lib_div::GPvar("MP"),t3lib_div::GPvar("RDCT"));
$TSFE->connectToMySQL();
$TSFE->initFEuser();

/*************************************************************
 * Extend the tslib_pibase class so we can use its functions
 *************************************************************/
class tx_hoicommunity_pibase extends tslib_pibase {
	var $prefixId = 'tx_hoicommunity_pi1';
	var $scriptRelPath = 'pi1/ajax.php';
	var $extKey = 'hoicommunity';
	var $pi_checkCHash = true;
	var $fe_user = false;

	function tx_hoicommunity_pibase() {
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// Easy access to user data (name, email, etc)
		$this->fe_user = (object)$GLOBALS['TSFE']->fe_user->user;
	}

	function isEmailOk($Email) {
		// Why do so many people write their email as www.whatever@yahoo.com?? It doesn't work!
		if (strpos($Email, 'www.') === 0) {
			return false;
		}

		// Pretty cool regex for the email :)
		if (!preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $Email)) {
			return false;
		}

		// And last but not least, check if emailaddress is accepted
		// Used to also return false if it could not connect, but that returns too many false negatives
		// This is by no means a perfect check, but it can prevent a user to use a emailaddress that looks fine by regex but does not exist!
		$HTTP_HOST = $_SERVER['HTTP_HOST'];
		if (! $HTTP_HOST) {
			$HTTP_HOST = 'localhost';
		}

		list ($Username, $Domain) = split ("@",$Email);
		if (checkdnsrr ($Domain, "MX"))  {
			getmxrr ($Domain, $MXHost, $MXWeight);
			$ConnectAddress = $MXHost[0];
		} else {
			$ConnectAddress = $Domain;
		}
		$Connect = @fsockopen ($ConnectAddress, 25, $errno, $errstr, 10);

		if ($Connect) {
			if (ereg ("^220", $Out = fgets($Connect, 1024))) {

				stream_set_blocking($Connect, false);
				$Out = fgets($Connect, 1024);
				while (ereg("^220", $Out)) {
					$Out = fgets($Connect, 1024);
				}
				stream_set_blocking($Connect, true);

				fputs($Connect, "HELO $HTTP_HOST\r\n");
				$Out = fgets($Connect, 1024);

				fputs($Connect, "MAIL FROM: <{$Email}>\r\n");
				$From = fgets($Connect, 1024);

				fputs($Connect, "RCPT TO: <{$Email}>\r\n");
				$To = fgets($Connect, 1024);

				fputs($Connect, "QUIT\r\n");
				fclose($Connect);

				if (!ereg("^250", $From) || !ereg("^250", $To)) {
					return false;
				}
			}
		}

		return true;
	}
}

$pi = new tx_hoicommunity_pibase;

switch ($_POST['cmd']) {

	case 'registerCheck' :
		// Check for illegal chars in username
		if ( !preg_match("/^[a-z0-9][\w\s.~*-]*$/i", $_POST['username']) ) {
			$return[] = '- '.$pi->pi_getLL('usernameIllegalChars');
		} else {
			// Username already taken? (no need in checking if username is not valid anyways)
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_users','username="'.$GLOBALS['TYPO3_DB']->quoteStr($_POST['username'], 'fe_users').'"', '', '');
			if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$return[] = '- '.$pi->pi_getLL('usernameTaken');
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}

		// Better email check then done in javascript
		if (!$pi->isEmailOk($_POST['email'])) {
			$return[] = '- '.$pi->pi_getLL('emailFormatError');
		} else {
			// Emailaddress already used by someone? (no need in checking if email is not valid anyways)
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_users','email="'.$GLOBALS['TYPO3_DB']->quoteStr($_POST['email'], 'fe_users').'"', '', '');
			if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$return[] =  '- '.$pi->pi_getLL('emailTaken');
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}

		if ($return) {
			echo implode("\n", $return);
		}
	break;

	case 'deleteMessage' :
		// First we check if this message does really belong to the logged in user

		$select = 'tx_hoicommunity_messages.uid';
		$from = 	'tx_hoicommunity_messages, tx_hoicommunity_folders';
		$where = 	'tx_hoicommunity_folders.cruser_id = '.intval($pi->fe_user->uid).' AND
							 tx_hoicommunity_folders.uid = tx_hoicommunity_messages.folder_uid AND
							 tx_hoicommunity_messages.uid='.intval($_POST['messageId']);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);
		if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_messages','uid='.intval($_POST['messageId']));
			echo 'Effect.Fade("tx-hoicommunity-pi1-messagelist-message'.$_POST['messageId'].'")';
		} else {
			echo 'alert("Something went wrong, you probably tried to delete a message that does NOT belong to you!");';
		}
	break;

	case 'deleteBuddy' :
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_buddylist', 'cruser_id='.intval($pi->fe_user->uid).' AND fe_user_uid='.intval($_POST['buddyId']));
		echo 'Effect.Fade("tx-hoicommunity-pi1-buddylist-uid'.$_POST['buddyId'].'")';
	break;

	case 'deleteIgnoredUser' :
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_ignorelist', 'cruser_id='.intval($pi->fe_user->uid).' AND fe_user_uid='.intval($_POST['userId']));
		echo 'Effect.Fade("tx-hoicommunity-pi1-ignorelist-uid'.$_POST['userId'].'")';
	break;

	default:
	break;

}

?>