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

if ($this->piVars['password']) {
	if (empty($this->piVars['password'])) {
		$this->content .= '<p>'. $this->pi_getLL('fieldsMissing') . '</p>';
	} else {
		// No errors
		$values['tstamp'] = time();
		$values['password'] = $this->piVars['password'];

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users','uid='.intval($this->fe_user->uid),$values);
		$this->content .= '<p>'.$this->pi_getLL('passwordUpdated').'</p>';
	}

} else {
	// Parse the form template

	$templateMarkers['###USERNAME_EXTRA###'] = 'id="tx-hoicommunity-pi1-register-username"';
	$templateMarkers['###PASSWORD_EXTRA###'] = 'onchange="evalPwd(this.value);" onkeyup="evalPwd(this.value);"';
	$templateMarkers['###PASSWORD_STATUS###'] = '<span id="tx-hoicommunity-pi1-register-password-status"><img alt="" src="/typo3conf/ext/hoicommunity/res/pwd_1.gif" width="400" height="13" /></span>';
	$templateMarkers['###SUBMIT_EXTRA###'] = 'id="tx-hoicommunity-pi1-register-submit" disabled="1"';

	$templateMarkers['###FORM_URL###'] = $this->pi_linkTP_keepPIvars_url();
	$templateMarkers['###FORM_ENCTYPE###'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'];
	$templateMarkers['###FORM_EXTRA###'] = 'id="tx-hoicommunity-pi1-register-formid"';

	$templateMarkers['###PASSWORD_LABEL###'] = $this->pi_getLL('password');
	$templateMarkers['###PASSWORD_FIELD###'] = 'tx_hoicommunity_pi1[password]';
	$templateMarkers['###SUBMIT_FIELD###'] = 'tx_hoicommunity_pi1[submit]';
	$templateMarkers['###SUBMIT_VALUE###'] = $this->pi_getLL('registerSubmit');

	$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['changePassword']),'###CHANGEPASSWORD###'),$templateMarkers, array(), $wrappedSubpartArray);
	$this->content .= '<script type="text/javascript">var username="'.$this->fe_user->username.'"; InitPasswordCheck();</script>';
}
?>