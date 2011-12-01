<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
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
 * This is the register view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

if ($this->piVars['act']) {
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_users','disable=1 AND deleted=0 AND tx_hoicommunity_activation="'.$this->piVars['act'].'"');
	if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		// Activating user!
		$values['tstamp'] = time();
		$values['disable'] = 0;
		$values['tx_hoicommunity_activation'] = '';
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid='.$row['uid'], $values);

		// Write to log
		$this->logUser($row['uid']);
	}

	$this->content .= '<p>'.$this->pi_getLL('activated').'</p>';
} elseif ($this->piVars['username']) {
	// Form has been submitted, we check required fields and uniqueness of username and email
	// Yes we also do that with javascript and ajax, but that can be fooled or turned off...

	if (empty($this->piVars['username']) OR empty($this->piVars['password']) OR empty($this->piVars['first_name']) OR empty($this->piVars['last_name']) OR empty($this->piVars['email'])) {
		$return[] = '- '.$this->pi_getLL('fieldsMissing');
	}

	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_users','username="'.$GLOBALS['TYPO3_DB']->quoteStr($this->piVars['username'], 'fe_users').'"', '', '');
	if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		$return[] = '- '.$this->pi_getLL('usernameTaken');
	}
	$GLOBALS['TYPO3_DB']->sql_free_result($res);

	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','fe_users','email="'.$GLOBALS['TYPO3_DB']->quoteStr($this->piVars['email'], 'fe_users').'"', '', '');
	if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		$return[] =  '- '.$this->pi_getLL('emailTaken');
	}
	$GLOBALS['TYPO3_DB']->sql_free_result($res);

	if ($return) {
		$this->content .= '<p>'. implode('<br />', $return) . '</p>';
	} else {
		// No errors, insert user
		$values['pid'] = $this->sysfolder;
		$values['tstamp'] = time();
		$values['crdate'] = time();
		$values['username'] = strip_tags($this->piVars['username']);
		$values['usergroup'] = $this->conf['uid.']['memberGroup'];
		$values['disable'] = 1;
		$values['deleted'] = 0;
		$values['password'] = $this->piVars['password'];
		$values['first_name'] = strip_tags($this->piVars['first_name']);
		$values['last_name'] = strip_tags($this->piVars['last_name']);
		$values['email'] = strip_tags($this->piVars['email']);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users',$values);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		// Write to log
		$this->logUser($uid);

		// Send the activation email
		$this->activate($uid);

		// Create the inbox for the user
		$inbox = $this->inboxId($uid);

		// Send the user a default welcome message
		$msg_values['pid'] = $this->sysfolder;
		$msg_values['tstamp'] = time();
		$msg_values['crdate'] = time();
		$msg_values['cruser_id'] = $uid;
		$msg_values['folder_uid'] = $inbox;
		$msg_values['subject'] = $this->pi_getLL('welcomeMessageSubject');
		$msg_values['body'] = $this->pi_getLL('welcomeMessageBody');
		$msg_values['been_read'] = 0;
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_messages',$msg_values);

		$this->content .= '<p>'.$this->pi_getLL('registered').'</p>';
	}

} else {
	// Parse the form template

	$templateMarkers['###USERNAME_EXTRA###'] = 'id="tx-hoicommunity-pi1-register-username"';
	$templateMarkers['###PASSWORD_EXTRA###'] = 'onchange="evalPwd(this.value);" onkeyup="evalPwd(this.value);"';
	$templateMarkers['###PASSWORD_STATUS###'] = '<span id="tx-hoicommunity-pi1-register-password-status"><img alt="" src="/typo3conf/ext/hoicommunity/res/pwd_1.gif" width="400" height="13" /></span>';
	$templateMarkers['###SUBMIT_EXTRA###'] = 'id="tx-hoicommunity-pi1-register-submit" disabled="1"';

	$templateMarkers['###SUBMIT_ONCLICK###'] = 'validateRegister();';

	$templateMarkers['###FORM_URL###'] = $this->pi_getPageLink($this->conf['pid.']['register']);
	$templateMarkers['###FORM_ENCTYPE###'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'];
	$templateMarkers['###FORM_EXTRA###'] = 'id="tx-hoicommunity-pi1-register-formid"';

	$templateMarkers['###USERNAME_LABEL###'] = $this->pi_getLL('username');
	$templateMarkers['###PASSWORD_LABEL###'] = $this->pi_getLL('password');
	$templateMarkers['###NAME_LABEL###'] = $this->pi_getLL('name');
	$templateMarkers['###FIRSTNAME_LABEL###'] = $this->pi_getLL('firstName');
	$templateMarkers['###LASTNAME_LABEL###'] = $this->pi_getLL('lastName');
	$templateMarkers['###EMAIL_LABEL###'] = $this->pi_getLL('email');
	$templateMarkers['###REGISTER_HELP###'] = $this->pi_getLL('registerHelp');

	$templateMarkers['###USERNAME_FIELD###'] = 'tx_hoicommunity_pi1[username]';
	$templateMarkers['###PASSWORD_FIELD###'] = 'tx_hoicommunity_pi1[password]';
	$templateMarkers['###FIRSTNAME_FIELD###'] = 'tx_hoicommunity_pi1[first_name]';
	$templateMarkers['###LASTNAME_FIELD###'] = 'tx_hoicommunity_pi1[last_name]';
	$templateMarkers['###EMAIL_FIELD###'] = 'tx_hoicommunity_pi1[email]';

	$templateMarkers['###SUBMIT_FIELD###'] = 'tx_hoicommunity_pi1[submit]';
	$templateMarkers['###SUBMIT_VALUE###'] = $this->pi_getLL('registerSubmit');

	$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['register']),'###REGISTER###'),$templateMarkers, array(), $wrappedSubpartArray);
	$this->content .= '<script type="text/javascript">var username=""; InitPasswordCheck();</script>';

}
?>