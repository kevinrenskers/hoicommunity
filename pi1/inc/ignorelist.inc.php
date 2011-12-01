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
 * This is the ignorelist view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

if ($this->piVars['uid'] && $this->piVars['action'] == 'add') {
	// Insert new user, only if he is not already on the list
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_hoicommunity_ignorelist', 'cruser_id='.intval($this->fe_user->uid).' AND fe_user_uid='.intval($this->piVars['uid']), '', '');
	if (!$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		$values['pid'] = $this->sysfolder;
		$values['tstamp'] = time();
		$values['crdate'] = time();
		$values['cruser_id'] = $this->fe_user->uid;
		$values['fe_user_uid'] = $this->piVars['uid'];
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_ignorelist',$values);
	}
}

if ($this->piVars['uid'] && $this->piVars['action'] == 'delete') {
	// Delete user
	$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_ignorelist', 'cruser_id='.intval($this->fe_user->uid).' AND fe_user_uid='.intval($this->piVars['uid']));
}

// View the ignorelist
$select =	'fe_users.username AS username,
					 fe_users.uid AS uid';
$from = 	'tx_hoicommunity_ignorelist, fe_users';
$where =	'tx_hoicommunity_ignorelist.cruser_id = '.intval($this->fe_user->uid).' AND
					 tx_hoicommunity_ignorelist.fe_user_uid = fe_users.uid AND
					 fe_users.disable=0 AND fe_users.deleted=0';
$sort = 	'fe_users.username';

$template['total'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['ignorelist']),'###IGNORELIST###');
$template['user'] = $this->cObj->getSubpart($template['total'],'###USER###');

$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', $sort);
while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	$markerArray['###USERNAME###'] = htmlspecialchars($row['username']);
	$markerArray['###ROWID###'] = 'tx-hoicommunity-pi1-ignorelist-uid'.$row['uid'];

	$url = "window.location='" . t3lib_div::locationHeaderUrl($this->pi_getPageLink($this->conf['pid.']['messages'], '', array('tx_hoicommunity_pi1[reciever]'=>$row['uid']))) . "'";
	$markerArray['###ACTIONS###'] =  '<img class="clickable" src="'.$GLOBALS['TSFE']->tmpl->getFileName($this->conf['resource.']['icon.']['messageWrite']).'" alt="'.$this->pi_getLL('sendMessage').'" title="'.$this->pi_getLL('sendMessage').'" onclick="'.$url.'" />'
																	.' <img class="clickable" src="'.$GLOBALS['TSFE']->tmpl->getFileName($this->conf['resource.']['icon.']['buddyDelete']).'" alt="'.$this->pi_getLL('deleteIgnoredUser').'" title="'.$this->pi_getLL('deleteIgnoredUser').'" onclick="deleteIgnoredUser('.$row['uid'].')" />';

	if ($this->isOnline($row['uid'])) {
		$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberOnline'], array('altText'=>$this->pi_getLL('memberOnline')));
		$markerArray['###STATUSTEXT###'] = $this->pi_getLL('memberOnline');
	} else {
		$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberOffline'], array('altText'=>$this->pi_getLL('memberOffline')));
		$markerArray['###STATUSTEXT###'] = $this->pi_getLL('memberOffline');
	}

	$wrappedSubpartArray['###LINKTOPROFILE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'])));
	$buddy .= $this->cObj->substituteMarkerArrayCached($template['user'], $markerArray, array(), $wrappedSubpartArray);
}
$GLOBALS['TYPO3_DB']->sql_free_result($res);

$subpartArray['###CONTENT###'] = $buddy;
$subpartArray['###USERNAME_LABEL###'] = $this->pi_getLL('username');

$this->content .= $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray, array());

?>