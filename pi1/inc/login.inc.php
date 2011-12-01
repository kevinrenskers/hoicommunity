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
 * This is the login view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

$markerArray['###FORM_URL###'] = $this->pi_linkTP_keepPIvars_url(array(),0,0,$this->conf['pid.']['login']);
$markerArray['###FORM_ENCTYPE###'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'];
$markerArray['###SUBMIT_FIELD###'] = 'tx_hoicommunity_pi1[submit]';
$markerArray['###USERNAME_FIELD###'] = 'user';
$markerArray['###PASSWORD_FIELD###'] = 'pass';
$markerArray['###EMAIL_FIELD###'] = 'tx_hoicommunity_pi1[email]';

if ($this->fe_user) {
	// LOGOUT VIEW
	$markerArray['###HIDDEN_FIELDS###'] = '<input name="logintype" type="hidden" value="logout" /><input name="pid" type="hidden" value="' . $this->sysfolder . '" />';
	$markerArray['###STATUSTEXT###'] = $this->pi_getLL('loggedInAs');
	$markerArray['###USERNAME###'] = $this->fe_user->username;
	$markerArray['###SUBMIT_VALUE###'] = $this->pi_getLL('logoutSubmit');
	$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['login']),'###LOGOUT###'),$markerArray, array(), array());
} else {
	if ($this->piVars['action']=='forgotpassword') {
		// FORGOT PASSWORD
		if ($this->piVars['submit']) {
			// Send the password
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('email,username,password', 'fe_users', 'email="'.$GLOBALS['TYPO3_DB']->quoteStr($this->piVars['email'], 'fe_users').'"', '');
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$body  = 'Username: ' . $row['username'] . "\n";
				$body .= 'Password: ' . $row['password'];
				$this->sendEmail($row['email'], $body, $this->pi_getLL('forgotPasswordSubject'));
				$this->content .= '<p>' . $this->pi_getLL('passwordSent') . '</p>';
			} else {
				$this->content .= '<p>' . $this->pi_getLL('forgotPasswordError') . '</p>';
			}
		} else {
			// Show forgot password form
			$markerArray['###EMAIL_LABEL###'] = $this->pi_getLL('email');
			$markerArray['###SUBMIT_VALUE###'] = $this->pi_getLL('forgotPasswordSubmit');
			$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['login']),'###FORGOTPASSWORD###'),$markerArray, array(), array());
		}
	} else {
		// LOGIN VIEW
		$markerArray['###HIDDEN_FIELDS###'] = '<input name="logintype" type="hidden" value="login" /><input name="pid" type="hidden" value="' . $this->sysfolder . '" />';
		$markerArray['###SUBMIT_VALUE###'] = $this->pi_getLL('loginSubmit');
		$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['login']),'###LOGIN###'),$markerArray, array(), array());

		// Add a link to the register page
		$this->content .= '<p>'.$this->pi_linkToPage($this->pi_getLL('linkToForgotPassword'), $this->conf['pid.']['login'], '', array('tx_hoicommunity_pi1[action]'=>'forgotpassword')) . '<br />'. $this->pi_linkToPage($this->pi_getLL('linkToRegisterPage'), $this->conf['pid.']['register']).'</p>';
	}
}

?>