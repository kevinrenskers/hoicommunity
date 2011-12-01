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
 * This is the buddlist view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

if (!$this->piVars['letter']) {
	$this->piVars['letter'] = 'a';
}

// View the buddylist
$select =	'username, uid, is_online';
$from = 	'fe_users';
$where =	'pid='.$this->sysfolder.' AND disable=0 AND deleted=0 AND username LIKE "'.$GLOBALS['TYPO3_DB']->quoteStr($this->piVars['letter'], 'fe_users').'%"';
$sort = 	'username';

$template['total'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['userlist']),'###USERLIST###');
$template['user'] = $this->cObj->getSubpart($template['total'],'###USER###');

$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', $sort);
while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

	$markerArray['###USERNAME###'] = htmlspecialchars($row['username']);
	$url = "window.location='" . t3lib_div::locationHeaderUrl($this->pi_getPageLink($this->conf['pid.']['messages'], '', array('tx_hoicommunity_pi1[reciever]'=>$row['uid']))) . "'";
	$markerArray['###ACTIONS###'] =  '<img class="clickable" src="'.$GLOBALS['TSFE']->tmpl->getFileName($this->conf['resource.']['icon.']['messageWrite']).'" alt="'.$this->pi_getLL('sendMessage').'" title="'.$this->pi_getLL('sendMessage').'" onclick="'.$url.'" />';

	if ($row['is_online']>=strtotime('-5 minutes')) {
		$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberOnline'], array('altText'=>$this->pi_getLL('memberOnline')));
		$markerArray['###STATUSTEXT###'] = $this->pi_getLL('memberOnline');
	} else {
		$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberOffline'], array('altText'=>$this->pi_getLL('memberOffline')));
		$markerArray['###STATUSTEXT###'] = $this->pi_getLL('memberOffline');
	}

	$wrappedSubpartArray['###LINKTOPROFILE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'])));
	$user .= $this->cObj->substituteMarkerArrayCached($template['user'], $markerArray, array(), $wrappedSubpartArray);
}
$GLOBALS['TYPO3_DB']->sql_free_result($res);

$subpartArray['###CONTENT###'] = $user;
$subpartArray['###USERNAME_LABEL###'] = $this->pi_getLL('username');

foreach (range('a', 'z') as $letter) {
	$letters[] = $this->pi_linkTP($letter, array('tx_hoicommunity_pi1[letter]'=>$letter));
}

$subpartArray['###LETTERS###'] = implode(' ', $letters);

$this->content .= $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray, array());

?>