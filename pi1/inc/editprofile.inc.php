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
 * This is the edit profile view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

if (!$editable) {
	// We don't have the right to edit this profile!
	$this->content .= $this->pi_getLL('noRights');
} else {
	// Parse the form template

	$markerArray['###FORM_URL###'] = $this->pi_getPageLink($this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$uid));
	$markerArray['###FORM_ENCTYPE###'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'];
	$markerArray['###FORM_ONSUBMIT###'] = 'try { var myValidator = validateEditProfile; } catch(e) { return true; } return myValidator(this);';

	$markerArray['###FIRSTNAME_FIELD###'] = 'tx_hoicommunity_pi1[first_name]';
	$markerArray['###FIRSTNAME_VALUE###'] = $row['first_name'];

	$markerArray['###LASTNAME_FIELD###'] = 'tx_hoicommunity_pi1[last_name]';
	$markerArray['###LASTNAME_VALUE###'] = $row['last_name'];

	$markerArray['###BIRTHDAY_FIELD###'] = 'tx_hoicommunity_pi1[tx_hoicommunity_birthday]';
	$markerArray['###BIRTHDAY_VALUE###'] = $this->pi_getLL('unknown');
	$markerArray['###BIRTHDAY_HIDDEN_VALUE###'] = '';
	if ($row['tx_hoicommunity_birthday']) {
		$markerArray['###BIRTHDAY_VALUE###'] = $this->cObj->stdWrap($row['tx_hoicommunity_birthday'], $this->conf['date_stdWrap.']);
		$markerArray['###BIRTHDAY_HIDDEN_VALUE###'] = date('Y/m/d', $row['tx_hoicommunity_birthday']);
	}
	$markerArray['###CALENDAR###'] = $GLOBALS['TSFE']->tmpl->getFileName($this->conf['resource.']['icon.']['calendar']);

	$markerArray['###CITY_FIELD###'] = 'tx_hoicommunity_pi1[city]';
	$markerArray['###CITY_VALUE###'] = $row['city'];

	$markerArray['###COUNTRY_FIELD###'] = 'tx_hoicommunity_pi1[country]';
	$markerArray['###COUNTRY_VALUE###'] = $row['country'];

	$markerArray['###WWW_FIELD###'] = 'tx_hoicommunity_pi1[www]';
	$markerArray['###WWW_VALUE###'] = $row['www'];

	$markerArray['###IMAGE_FIELD###'] = 'tx_hoicommunity_pi1_image';

	$markerArray['###EMAIL_FIELD###'] = 'tx_hoicommunity_pi1[email]';
	$markerArray['###EMAIL_VALUE###'] = $row['email'];

	$markerArray['###INFORMATION_FIELD###'] = 'tx_hoicommunity_pi1[tx_hoicommunity_information]';
	$markerArray['###INFORMATION_VALUE###'] = $row['tx_hoicommunity_information'];

	$markerArray['###SUBMIT_FIELD###'] = 'tx_hoicommunity_pi1[submit]';
	$markerArray['###SUBMIT_VALUE###'] = $this->pi_getLL('editProfileSubmit');

	$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['profile']),'###EDITPROFILE###'),$markerArray, array(), $wrappedSubpartArray);


	$this->content .= '<script type="text/javascript">
	  									Calendar.setup( {
	      								inputField  : "birthday_input",
	      								displayArea : "birthday_display",
												ifFormat    : "%Y/%m/%d",
												daFormat    : "'.$this->conf['date_stdWrap.']['strftime'].'",
												button      : "birthday_trigger",
												firstDay    : 1,
												weekNumbers : 0
											} );
										</script>';
}

?>