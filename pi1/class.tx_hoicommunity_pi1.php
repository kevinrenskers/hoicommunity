<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
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
 * Plugin 'HOI Community' for the 'hoicommunity' extension.
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_basicfilefunc.php');

class tx_hoicommunity_pi1 extends tslib_pibase {
	var $prefixId = 'tx_hoicommunity_pi1';
	var $scriptRelPath = 'pi1/class.tx_hoicommunity_pi1.php';
	var $extKey = 'hoicommunity';
	var $pi_checkCHash = true;
	var $sysfolder = false;
	var $uploadDir = 'uploads/tx_hoicommunity/';
	var $flexform = false;
	var $view = 'WELCOME';
	var $content = '';
	var $fe_user = false;
	var $conf = false;
	var $extraFeatures = false;
	var $adminRights = false;
	var $site_url = '';
	var $extraFeatures_groups = '';
	var $adminRights_groups = '';
	var $fileFunc = '';

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array			$conf: The PlugIn configuration
	 * @return string		The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->site_url = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		$this->fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');

		// Disable caching
		$this->pi_USER_INT_obj = 1;
		$GLOBALS['TSFE']->set_no_cache();

		// Easy access to user data (name, email, etc)
		if ($GLOBALS['TSFE']->loginUser) {
			$this->fe_user = (object)$GLOBALS['TSFE']->fe_user->user;

			$fe_groups = t3lib_div::trimExplode(',', $this->fe_user->usergroup);
			$this->extraFeatures_groups = t3lib_div::trimExplode(',', $this->conf['uid.']['extraFeatures']);
			$this->adminRights_groups = t3lib_div::trimExplode(',', $this->conf['uid.']['adminRights']);

			// Is the logged-in user a member of the extraFeatures and/or adminRights groups?
			$this->extraFeatures = $this->isInGroup($this->extraFeatures_groups, $fe_groups);
			$this->adminRights = $this->isInGroup($this->adminRights_groups, $fe_groups);
		}

		// Get the flexform config (or TS config) to see what kind of output we are going to generate, we call that our view
		if ($this->conf['view']) {
			$this->view = $this->conf['view'];
		} else {
			$this->pi_initPIflexForm();
			$this->flexform = $this->cObj->data['pi_flexform'];
			$this->view = $this->pi_getFFvalue($this->flexform, 'field_code');
		}

		// Get the PID of the sysfolder where everything will be stored
		// First look for 'startingpoint' config in the plugin
		if ($this->cObj->data['pages'] != null) {
			$this->sysfolder = $this->cObj->data['pages'];
		}
		// No startingpoint given, is there a storagepid given?
		elseif ($storagePid = $GLOBALS['TSFE']->getStorageSiterootPids()) {
			$this->sysfolder = $storagePid['_STORAGE_PID'];
		}
		// If no starting point and no storagepid are given, then take the pid of the plugin page
		else {
			$this->sysfolder = $GLOBALS['TSFE']->id;
		}

		// The prototype.js library, see http://www.sergiopereira.com/articles/prototype.js.html
		$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-proto'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/prototype.js"></script>';

		// Our own javascript functions
		$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-pre'] = '<script type="text/javascript">
			var pathToAjax="'.t3lib_extMgm::siteRelPath($this->extKey).'pi1/ajax.php";
			var pleaseWait="'.$this->pi_getLL('formPleaseWait').'";
			var formIncorrect="'.$this->pi_getLL('formFormIncorrect').'";
			var formErrors="'.$this->pi_getLL('formFormErrors').'";
			var enterNickname="'.$this->pi_getLL('formEnterNickname').'";
			var enterPassword="'.$this->pi_getLL('formEnterPassword').'";
			var enterFirstName="'.$this->pi_getLL('formEnterFirstName').'";
			var enterLastName="'.$this->pi_getLL('formEnterLastName').'";
			var enterEmail="'.$this->pi_getLL('formEnterEmail').'";
			var wrongEmail="'.$this->pi_getLL('formWrongEmail').'";
			var enterSubject="'.$this->pi_getLL('formEnterSubject').'";
			var enterMessage="'.$this->pi_getLL('formEnterMessage').'";
			</script>';
		$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-hoi'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/hoicommunity.js"></script>';

		// Call the function that is going to create the view and thus filling the $this->content var
		switch ($this->view) {
			case 'REGISTER':
				$this->makeRegisterView();
			break;

			case 'LOGIN':
				$this->makeLoginView();
			break;

			case 'ONLINEUSERS':
				$this->makeOnlineusersView();
			break;

			case 'MESSAGES':
				$this->makeMessagesView();
			break;

			case 'BUDDYLIST':
				$this->makeBuddylistView();
			break;

			case 'IGNORELIST':
				$this->makeIgnorelistView();
			break;

			case 'USERLIST':
				$this->makeUserlistView();
			break;

			case 'PROFILE':
				$this->makeProfileView();
			break;

			case 'CHANGEPASSWORD':
				$this->makeChangePasswordView();
			break;

			case 'SEARCH':
				$this->searchUsers();
			break;

			case 'ADMIN':
				$this->administration();
			break;

			case 'WELCOME':
			default:
				$this->makeWelcomeView();
			break;
		}

		return $this->pi_wrapInBaseClass($this->content);
	}


	##########################################
	##  HELPER FUNCTIONS                    ##
	##########################################

	/**
	 * Is the logged in user a member of the extraFeatures and/or adminRights groups?
	 * At least 1 of $needle should be in $haystack
	 *
	 * @param	array		$needle: the user should be member of one of these groups
	 * @param	array		$haystack: the groups the user is a member of
	 * @return bool		Is the logged in user a member of the extraFeatures and/or adminRights groups?
	 */
	function isInGroup($needle=array(), $haystack=array()) {
		return (bool)array_intersect($needle, $haystack);
	}


	/**
	 * Transforms the emailaddress so that it uses the spam protection setting.
	 * Does not create an actual link, only the text!
	 *
	 * @param	string		$mail Emailaddress to protect
	 * @return string		Spamprotected emailaddress, for example info(at)dualdot.nl
	 */
	function spamProtect($email) {
		$array = $this->cObj->getMailTo('', $email);
		return $array[1];
	}


	/**
	 * Was user online in the past 5 minutes?
	 *
	 * @param	int				$uid User id (uid)
	 * @return bool			Was user online in the past 5 minutes?
	 */
	function isOnline($uid) {
		$bool=false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_users','uid='.intval($uid).' AND is_online>='.strtotime('-5 minutes'), '', '');
		if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$bool=true;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $bool;
	}


	/**
	 * Is user a buddy of the logged in user?
	 *
	 * @param	int				$uid User id (uid)
	 * @return bool			Is user a buddy of the logged in user?
	 */
	function isBuddy($uid) {
		$bool=false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_hoicommunity_buddylist','cruser_id='.intval($this->fe_user->uid).' AND fe_user_uid='.intval($uid), '', '');
		if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$bool=true;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $bool;
	}


	/**
	 * Is user an ignored user of the logged in user?
	 *
	 * @param	int				$uid User id (uid)
	 * @return bool			Is user an ignored user of the logged in user?
	 */
	function isIgnoredUser($uid) {
		$bool=false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_hoicommunity_ignorelist','cruser_id='.intval($this->fe_user->uid).' AND fe_user_uid='.intval($uid), '', '');
		if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$bool=true;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $bool;
	}


	/**
	 * Is user an ignored user of the logged in user?
	 *
	 * @param	int				$uid User id (uid)
	 * @return bool			Is user an ignored user of the logged in user?
	 */
	function isIgnored($uid) {
		$bool=false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_hoicommunity_ignorelist','cruser_id='.intval($uid).' AND fe_user_uid='.intval($this->fe_user->uid), '', '');
		if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$bool=true;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $bool;
	}


	/**
	 * Parse text, replacing strings with emoticons
	 *
	 * @param	string		$text Text to parse
	 * @return string		Text that has been parsed (or not, if $this->conf['emoticons'] is off)
	 */
	function parseEmoticons($text){
		if ($this->conf['emoticons']){
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_hoicommunity_emoticons','pid='.$this->conf['pid.']['emoticons'].' AND NOT deleted=1 AND NOT hidden=1', '', '');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$strings[] = ' '.$row['string'];
				$files[] = ' '.$this->cObj->cImage($this->uploadDir.'emoticons/'.$row['file'], array('alttext' => $row['string']));
			}

			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			$text = str_replace($strings,$files,$text);
		}
		return $text;
	}


	/**
	 * Parse text, replacing BBCode tags with html tags
	 *
	 * @param	string		$text Text to parse
	 * @return string		Text that has been parsed
	 */
	function parseBBCode($text) {
		$strings = array(
		                   "'\[b\](.*?)\[/b\]'is",
		                   "'\[url\](.*?)\[/url\]'i",
		                   "'\[url=(.*?)\](.*?)\[/url\]'i"
		                );

		$replace = array(
		                   "<b>\\1</b>",
		                   "<a href=\"\\1\">\\1</a>",
		                   "<a href=\"\\1\">\\2</a>"
		                );

		$output = preg_replace($strings,$replace,$text);

		return $output;
	}

	/**
	 * Parse text, replacing BBCode quote tags with html tags
	 *
	 * @param	string		$text Text to parse
	 * @return string		Text that has been parsed
	 */
	function parseBBCodeQuotes($text) {
		$open = '<span class="tx-hoicommunity-pi1-messages-quote">';
		$close = '</span>';

		// How often is the open tag?
		preg_match_all ('/\[quote\]/i', $text, $matches);
		$opentags = count($matches['0']);

		// How often is the close tag?
		preg_match_all ('/\[\/quote\]/i', $text, $matches);
		$closetags = count($matches['0']);

		// Check how many tags have been unclosed
		// And add the unclosing tag at the end of the message
		$unclosed = $opentags - $closetags;
		for ($i = 0; $i < $unclosed; $i++) {
			$text .= $close;
		}

		// Do replacement
		$text = str_replace ('[quote]', $open, $text);
		$text = str_replace ('[/quote]', $close, $text);

		return $text;
	}


	/**
	 * Send activation email to user
	 *
	 * @param	int		$uid UID of the frontend user
	 * @param	int		$mode: 0 is normal activation, 1 is re-activation after changing emailaddress, 2 is reactivation after being inactive for 3 months
	 * @return void parse the email template and send it to the sendEmail() function
	 */
	function activate($uid, $mode=0) {
		// Make the activation code and save it into the database
		$act = $this->makeActivationCode();
		$values['tstamp'] = time();
		$values['disable'] = 1;
		$values['tx_hoicommunity_activation'] = $act;
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid='.intval($uid), $values);

		// Get the user info from the database
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','uid='.intval($uid));
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		$markerArray['###SALUTATION###'] = $this->pi_getLL('salutation');
		$markerArray['###NAME###'] = htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']);
		$markerArray['###ACTIVATIONLINK###'] = $this->site_url . $this->cObj->getTypoLink_URL($this->conf['pid.']['register'], array( 'tx_hoicommunity_pi1[act]' => $act ));

		switch ($mode) {
			case 0:
				$markerArray['###EMAIL_HEADER###'] = $this->pi_getLL('activationEmailHeader');
				$markerArray['###EMAIL_FOOTER###'] = $this->pi_getLL('activationEmailFooter');
				$subject = $this->pi_getLL('activationEmailSubject');
			break;
			case 1:
				$markerArray['###EMAIL_HEADER###'] = $this->pi_getLL('emailChangedReActivationEmailHeader');
				$markerArray['###EMAIL_FOOTER###'] = $this->pi_getLL('emailChangedReActivationEmailFooter');
				$subject = $this->pi_getLL('emailChangedReActivationEmailSubject');
			break;
			case 2:
				$markerArray['###EMAIL_HEADER###'] = $this->pi_getLL('inactiveAccountReActivationEmailHeader');
				$markerArray['###EMAIL_FOOTER###'] = $this->pi_getLL('inactiveAccountReActivationEmailFooter');
				$subject = $this->pi_getLL('inactiveAccountReActivationEmailSubject');
			break;
		}

		$text = $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['email']),'###ACTIVATION###'),$markerArray, array(), array());
		$this->sendEmail($row['email'], $text, $subject);
	}


	/**
	 * Sends out an email
	 *
	 * @return void sends out an email
	 */
	function sendEmail($to, $body, $subject) {
		$this->cObj->sendNotifyEmail($subject."\n".$body, $to, '', $this->conf['fromEmail'], $this->conf['fromName']);
	}


	/**
	 * Make an activation code and make sure it is unique in the fe_users table
	 *
	 * @return string unique activation code
	 */
	function makeActivationCode() {
		$code = $this->randomString(20);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'fe_users', 'tx_hoicommunity_activation="'.$code.'"');
		if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->makeActivationCode();
		} else {
			return $code;
		}
	}


	/**
	 * Generates a random string, can be used for password or activation code
	 *
	 * @param	int		$plength length of the string to generate, max 32
	 * @param	bool	$include_letters should the string contain letters
	 * @param	bool	$include_capitals should the string contain capitals
	 * @param	bool	$include_numbers should the string contain numbers
	 * @return string the random string
	 */
	function randomString($plength=8,$include_letters=true,$include_capitals=true,$include_numbers=true) {
		if(!is_numeric($plength) || $plength <= 0) {
			$plength = 8;
    }
    if($plength > 32) {
			$plength = 32;
    }

    $chars = '';

    if ($include_letters == true) { $chars .= 'abcdefghijklmnopqrstuvwxyz'; }
    if ($include_capitals == true) { $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; }
    if ($include_numbers == true) { $chars .= '0123456789'; }

    // If nothing selected just display 0's
    if ($include_letters == false AND $include_capitals == false AND $include_numbers == false) {
			$chars .= '0';
    }

    // This is important:  we need to seed the random number generator
    mt_srand(microtime() * 1000000);

    // Now we simply generate a random string based on the length that was requested in the function argument
    for($i = 0; $i < $plength; $i++) {
	    $key = mt_rand(0,strlen($chars)-1);
	    $pwd = $pwd . $chars{$key};
    }

    // Finally to make it a bit more random, we switch some characters around
    for($i = 0; $i < $plength; $i++) {
	    $key1 = mt_rand(0,strlen($pwd)-1);
	    $key2 = mt_rand(0,strlen($pwd)-1);

	    $tmp = $pwd{$key1};
	    $pwd{$key1} = $pwd{$key2};
	    $pwd{$key2} = $tmp;
    }

    return $pwd;
	}


	/**
	 * Get the folder UID that acts as the inbox for this user. Will create one if needed.
	 *
	 * @param	int		$uid uid of the user
	 * @return int 	uid of the inbox-folder
	 */
	function inboxId($uid) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_hoicommunity_folders', 'cruser_id='.intval($uid).' AND is_inbox=1');
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			return $row['uid'];
		} else {
			// Need to create an inbox for this user
			$values['pid'] = $this->sysfolder;
			$values['tstamp'] = time();
			$values['crdate'] = time();
			$values['cruser_id'] = $uid;
			$values['name'] = $this->pi_getLL('inbox');
			$values['is_inbox'] = 1;
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_folders',$values);
			$folder_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
			return $folder_uid;
		}
	}


	/**
	 * Get the names of the groups the user is in
	 *
	 * @param	string	$groups comma seperated string of group uid's
	 * @return string	names of the groups
	 */
	function userGroups($groups) {
		$groups = t3lib_div::trimExplode(',', $groups);
		foreach ($groups AS $group) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title', 'fe_groups', 'uid='.$group);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$titles[] = $row['title'];
		}
		return implode(', ', $titles);
	}


	/**
	* Checks the error value from the upload $_FILES array.
	*
	* @param string  $error_code: the error code
	* @return boolean  true if ok
	*/
	function evalFileError($error_code) {
		if ($error_code == "0") {
			return true; // File upload okay
		} elseif ($error_code == '1') {
			return false; // filesize exceeds upload_max_filesize in php.ini
		} elseif ($error_code == '3') {
			return false; // The file was uploaded partially
		} elseif ($error_code == '4') {
			return true; // No file was uploaded
		} else {
			return true;
		}
	}


	/**
	* Processes uploaded files
	*
	* @param string  $theField: the name of the field
	* @return void
	*/
	function processFiles($image) {
		if ($this->uploadDir && is_array($image) && $this->evalFileError($image['error']) && t3lib_div::verifyFilenameAgainstDenyPattern($image['name']) ) {
			$fI = pathinfo($image['name']);
			if (t3lib_div::inList('gif,jpg,jpeg,png,GIF,JPG,JPEG,PNG', $fI['extension'])) {
				$tmpFilename = $this->fe_user->uid . '_' . t3lib_div::shortmd5(uniqid($image['name'])) . '.' . $fI['extension'];
				$theDestFile = $this->fileFunc->getUniqueName($this->fileFunc->cleanFileName($tmpFilename), PATH_site.$this->uploadDir);
				if (t3lib_div::upload_copy_move($image['tmp_name'], $theDestFile)) {
					return $tmpFilename;
				}
			}
		}
	}


	/**
	* Write to logtable
	*
	* @param int  $uid: the uid of the user
	* @return void
	*/
	function logUser($cruser_id='') {
		if ($cruser_id) {
			$log['cruser_id'] = $cruser_id;
			$log['ip'] = $_SERVER['REMOTE_ADDR'];

			// If this user already has his current IP address in the database, we update the value with 1, and else insert it as a new entry
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,nr','tx_hoicommunity_log','cruser_id='.$log['cruser_id'].' AND ip="'.$log['ip'].'"');
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_hoicommunity_log', 'uid='.$row['uid'], array('nr'=>$row['nr']+1));
			} else {
				$log['nr'] = 1;
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_log',$log);
			}
		}
	}


	##########################################
	##  CREATING THE VIEWS                  ##
	##########################################

	/**
	 * This function creates the Welcome view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeWelcomeView() {
		if ($this->fe_user) {

			// Write to logfile
			$this->logUser($this->fe_user->uid);

			$select = 'count(tx_hoicommunity_messages.uid) AS count';

			$from = 'tx_hoicommunity_messages, tx_hoicommunity_folders';

			$where = 'tx_hoicommunity_folders.cruser_id='.intval($this->fe_user->uid).' AND
								tx_hoicommunity_folders.is_inbox = 1 AND
								tx_hoicommunity_folders.uid = tx_hoicommunity_messages.folder_uid AND
								tx_hoicommunity_messages.been_read = 0';

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

			$markerArray['###NEWMESSAGES_PRE###'] = $this->pi_getLL('newMessagesPre');
			$markerArray['###NEWMESSAGES###'] = $row['count'];
			$markerArray['###NEWMESSAGES_POST###'] = $this->pi_getLL('newMessagesPost');

			if ($row['count']) {
				$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['welcomeNewMessages'], array('altText'=>$this->pi_getLL('newMessages')));
				$markerArray['###STATUSTEXT###'] = $this->pi_getLL('newMessages');
			} else {
				$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['welcomeNoNewMessages'], array('altText'=>$this->pi_getLL('noNewMessages')));
				$markerArray['###STATUSTEXT###'] = $this->pi_getLL('noNewMessages');
			}

			$wrappedSubpartArray['###LINKTOMESSAGES###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['messages'], '', array()));
			$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['welcome']),'###WELCOME###'),$markerArray, array(), $wrappedSubpartArray);
		}
	}


	/**
	 * This function creates the Register view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeRegisterView() {
		if ($this->fe_user) {
			$this->content .= '<p>'.$this->pi_getLL('alreadyLoggedIn').'</p>';
		} else {
			require_once('inc/register.inc.php');
		}
	}


	/**
	 * This function creates the Login view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeLoginView() {
		require_once('inc/login.inc.php');
	}


	/**
	 * This function creates the Online users view and fills $this->content accordingly
	 * Shows all users who were online in the past 5 minutes
	 *
	 * @return void  sets $this->content
	 */
	function makeOnlineusersView() {
		if ($this->fe_user) {
			// Get the parts out of the template
			$template['total'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['onlineUsers']),'###ONLINEUSERS###');
			$template['user'] = $this->cObj->getSubpart($template['total'],'###USER###');

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, username, usergroup','fe_users','pid='.$this->sysfolder.' AND is_online>='.strtotime('-5 minutes'), '', 'username');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$markerArray['###USERNAME###'] = $row['username'];

				$groupclasses = array();
				$groupclasses[] = 'group-member';

				if ($this->isInGroup($this->extraFeatures_groups, t3lib_div::trimExplode(',', $row['usergroup']))) {
					$groupclasses[] = 'group-vip';
				}
				if ($this->adminRights = $this->isInGroup($this->adminRights_groups, t3lib_div::trimExplode(',', $row['usergroup']))) {
					$groupclasses[] = 'group-admin';
				}

				if ($this->isBuddy($row['uid'])) {
					$groupclasses[] = 'user-buddy';
				}

				$groupclasses = implode(' ', $groupclasses);

				$wrappedSubpartArray['###LINKTOPROFILE###'] = explode('|', $this->pi_linkToPage('<span class="'.$groupclasses.'">|</span>', $this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'])));
				$users .= $this->cObj->substituteMarkerArrayCached($template['user'], $markerArray, array(), $wrappedSubpartArray);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			$subpartArray['###CONTENT###'] = $users;
			$this->content .= $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray, array());
		}
	}


	/**
	 * This function creates the Messages view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeMessagesView() {
		if ($this->fe_user) {
			$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-effects'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/scriptaculous.js?load=effects"></script>';
			require_once('inc/messages.inc.php');
		}
	}


	/**
	 * This function creates the Buddylist view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeBuddylistView() {
		$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-effects'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/scriptaculous.js?load=effects"></script>';
		require_once('inc/buddylist.inc.php');
	}


	/**
	 * This function creates the Ignorelist view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeIgnorelistView() {
		$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-effects'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/scriptaculous.js?load=effects"></script>';
		require_once('inc/ignorelist.inc.php');
	}


	/**
	 * This function creates the Userlist view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeUserlistView() {
		//$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users','email="dark_klown_13@yahoo.com"',array('usergroup'=>6));
		require_once('inc/userlist.inc.php');
	}


	/**
	 * This function creates the Profile view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeProfileView() {
		if ($this->fe_user) {

			// Are we viewing/editing someone else's profile, or our own?
			$uid = $this->piVars['uid'] ? $this->piVars['uid'] : $this->fe_user->uid;

			// Can the logged in user edit this profile? True if it is his own profile or is he he in the adminRights group
			if ($uid==$this->fe_user->uid || $this->adminRights) {
				$editable=true;

				// Handling post data, only if we can actually edit this profile
				if ($this->piVars['submit']) {
					$values['tstamp'] = time();
					$values['first_name'] = strip_tags($this->piVars['first_name']);
					$values['last_name'] = strip_tags($this->piVars['last_name']);
					$values['city'] = strip_tags($this->piVars['city']);
					$values['country'] = strip_tags($this->piVars['country']);
					$values['www'] = strip_tags($this->piVars['www']);
					$values['email'] = strip_tags($this->piVars['email']);

					// First we replace the < and > characters... just using strip_tags could end in people's profiles cur short
					// Some people use those characters in their text, like <3 and stuff like that...
					$strings = array('<', '>', ' & ');
					$replace = array('&lt;', '&gt;', ' &amp; ');
					$values['tx_hoicommunity_information'] = str_replace($strings, $replace, $this->piVars['tx_hoicommunity_information']);
					$values['tx_hoicommunity_information'] = strip_tags($values['tx_hoicommunity_information']);

					$values['tx_hoicommunity_birthday'] = '';
					if ($this->piVars['tx_hoicommunity_birthday']) {
						// Birthday should be in timestamp format
						$values['tx_hoicommunity_birthday'] = strtotime($this->piVars['tx_hoicommunity_birthday']);
					}

					// Upload new picture
					if ($_FILES) {
						if ($filename = $this->processFiles($_FILES['tx_hoicommunity_pi1_image'])) {
							$values['image'] = $filename;
						}
					}

					// Get the original emailaddress before updating the database
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('email','fe_users','uid='.intval($uid));
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

					// Update the fe_users table!
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users','uid='.intval($uid),$values);

					// Emailaddress has been changed
					if ($values['email'] != $row['email']) {
						$this->activate($uid, 1);
						$GLOBALS["TSFE"]->fe_user->logoff();
						$this->content .= '<p>'.$this->pi_getLL('reActivate').'</p>';
						return;
					}

					$GLOBALS['TYPO3_DB']->sql_free_result($res);
				}
			}

			// Get the user info from the database
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','uid='.intval($uid));
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

			// Parse the markers that are used by both edit and view modes
			$markerArray['###ID_LABEL###'] = $this->pi_getLL('id');
			$markerArray['###USERNAME_LABEL###'] = $this->pi_getLL('username');
			$markerArray['###NAME_LABEL###'] = $this->pi_getLL('name');
			$markerArray['###FIRSTNAME_LABEL###'] = $this->pi_getLL('firstName');
			$markerArray['###LASTNAME_LABEL###'] = $this->pi_getLL('lastName');
			$markerArray['###COUNTRY_LABEL###'] = $this->pi_getLL('country');
			$markerArray['###CITY_LABEL###'] = $this->pi_getLL('city');
			$markerArray['###WWW_LABEL###'] = $this->pi_getLL('www');
			$markerArray['###EMAIL_LABEL###'] = $this->pi_getLL('email');
			$markerArray['###BIRTHDAY_LABEL###'] = $this->pi_getLL('birthday');
			$markerArray['###INFORMATION_LABEL###'] = $this->pi_getLL('information');
			$markerArray['###IMAGE_LABEL###'] = $this->pi_getLL('image');
			$markerArray['###STATUS_LABEL###'] = $this->pi_getLL('memberStatus');
			$markerArray['###GROUP_LABEL###'] = $this->pi_getLL('memberGroup');
			$markerArray['###MEMBERSINCE_LABEL###'] = $this->pi_getLL('memberSince');

			if ($this->piVars['action']==='edit') {
				// Profile EDIT view

				// Files for the Javascript calendar, see http://www.dynarch.com/projects/calendar/
				$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-cal1'] = '<style type="text/css">@import url('.t3lib_extMgm::siteRelPath($this->extKey).'res/js/aqua/theme.css);</style>';
				$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-cal2'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/calendar_stripped.js"></script>';
				$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-cal3'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/lang/calendar-en.js"></script>';
				$GLOBALS['TSFE']->additionalHeaderData['hoicommunity-js-cal4'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/js/calendar-setup_stripped.js"></script>';

				require_once('inc/editprofile.inc.php');
			} else {
				// Profile VIEW view
				require_once('inc/viewprofile.inc.php');
			}

			// Free results
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
	}


	/**
	 * This function creates the Change password view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function makeChangePasswordView() {
		if ($this->fe_user) {
			require_once('inc/changepassword.inc.php');
		}
	}


	/**
	 * This function creates the Search users view and fills $this->content accordingly
	 *
	 * @return void  sets $this->content
	 */
	function searchUsers() {
		if ($this->fe_user) {
			require_once('inc/search.inc.php');
		}
	}


	/**
	 * Upgrade from cwt_community to hoicommunity
	 *
	 * @return void  sets $this->content
	 */
	function administration() {
		if ($this->fe_user) {
			require_once('inc/admin.inc.php');
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hoicommunity/pi1/class.tx_hoicommunity_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hoicommunity/pi1/class.tx_hoicommunity_pi1.php']);
}

?>