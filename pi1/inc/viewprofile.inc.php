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
 * This is the profile view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

$markerArray['###ID###'] = $row['uid'];
$markerArray['###USERNAME###'] = htmlspecialchars($row['username']);
$markerArray['###NAME###'] = htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']);
$markerArray['###CITY###'] = htmlspecialchars($row['city']);
$markerArray['###COUNTRY###'] = htmlspecialchars($row['country']);
$markerArray['###WWW###'] = $row['www'] ? htmlspecialchars($row['www']) : '&nbsp;';
$markerArray['###EMAIL###'] = $this->spamProtect($row['email']);
$info = nl2br($this->parseEmoticons($row['tx_hoicommunity_information']));

$fe_groups = t3lib_div::trimExplode(',', $row['usergroup']);
if ($this->isInGroup($this->extraFeatures_groups, $fe_groups)) {
	$info = $this->parseBBCode($info);
}

$markerArray['###INFORMATION###'] = $info;
$markerArray['###MEMBERSINCE###'] = $this->cObj->stdWrap($row['crdate'], $this->conf['date_stdWrap.']);
$markerArray['###POPUP###'] = '<div id="tx-hoicommunity-pi1-popup" style="display:none;"></div>';
$markerArray['###EDITPROFILE###'] = ''; // empty default!
$markerArray['###SENDMESSAGE###'] = ''; // empty default!
$markerArray['###ADDBUDDY###'] = ''; // empty default!
$markerArray['###ADDIGNOREDUSER###'] = ''; // empty default!
$markerArray['###GROUP###'] = $this->userGroups($row['usergroup']);

$wrappedSubpartArray['###LINKTOEDITPROFILE###'] = ''; // empty default!
$wrappedSubpartArray['###LINKTOEDITPROFILE###'] = ''; // empty default!
$wrappedSubpartArray['###LINKTOADDBUDDY###'] = ''; // empty default!
$wrappedSubpartArray['###LINKTOADDIGNOREDUSER###'] = ''; // empty default!
$wrappedSubpartArray['###LINKTOWWW##'] = $this->cObj->typolinkWrap(array('parameter'=>$row['www']));
$wrappedSubpartArray['###LINKTOEMAIL###'] = $this->cObj->typolinkWrap(array('parameter'=>$row['email']));

$markerArray['###BIRTHDAYICON###'] = '';
$markerArray['###BIRTHDAY###'] = '&nbsp;';
if ($row['tx_hoicommunity_birthday']) {
	$markerArray['###BIRTHDAY###'] = $this->cObj->stdWrap($row['tx_hoicommunity_birthday'], $this->conf['date_stdWrap.']);
	if (date('m-d', $row['tx_hoicommunity_birthday']) == date('m-d')) {
		// Birthday is today!
		$markerArray['###BIRTHDAYICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberBirthday'], array('altText'=>$this->pi_getLL('memberBirthday')));
	}
}

if ($row['image']) {
	$markerArray['###IMAGE###'] = $this->cObj->cImage($this->uploadDir.$row['image'], $this->conf['profileImage.']);
} else {
	$markerArray['###IMAGE###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberNoImage'], array('altText'=>$this->pi_getLL('memberNoImage')));
}

if ($this->isOnline($row['uid'])) {
	$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberOnline'], array('altText'=>$this->pi_getLL('memberOnline')));
	$markerArray['###STATUSTEXT###'] = $this->pi_getLL('memberOnline');
	$markerArray['###STATUSEXTRA###'] = $this->pi_getLL('nowOnline');
} else {
	$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberOffline'], array('altText'=>$this->pi_getLL('memberOffline')));
	$markerArray['###STATUSTEXT###'] = $this->pi_getLL('memberOffline');

	if ($row['is_online']) {
		$markerArray['###STATUSEXTRA###'] = $this->pi_getLL('lastOnline') . ' ' . $this->cObj->stdWrap($row['is_online'], $this->conf['datetime_stdWrap.']);
	} else {
		$markerArray['###STATUSEXTRA###'] = $this->pi_getLL('neverOnline');
	}
}

if ($row['uid']==$this->fe_user->uid) {
	// Looking at your own profile

	$markerArray['###EDITPROFILE###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberEdit'], array()) . ' ' . $this->pi_getLL('editYourProfile');
	$wrappedSubpartArray['###LINKTOEDITPROFILE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'], 'tx_hoicommunity_pi1[action]'=>'edit')));
} else {
	// looking at someone else's profile

	if ($this->adminRights) {
		// Logged in user has admin rights and this can edit this user's profile
		$markerArray['###EDITPROFILE###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['memberEdit'], array()) . ' ' . $this->pi_getLL('editThisProfile');
		$wrappedSubpartArray['###LINKTOEDITPROFILE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'], 'tx_hoicommunity_pi1[action]'=>'edit')));
	}

	$markerArray['###SENDMESSAGE###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['messageWrite'], array()) . ' ' . $this->pi_getLL('sendMessage');
	$wrappedSubpartArray['###LINKTOSENDMESSAGE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['messages'], '', array('tx_hoicommunity_pi1[reciever]'=>$row['uid'])));

	// Is this user already a buddy?
	if ($this->isBuddy($row['uid'])) {
		$markerArray['###ADDBUDDY###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['buddyDelete'], array()) . ' ' . $this->pi_getLL('deleteBuddy');
		$wrappedSubpartArray['###LINKTOADDBUDDY###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['buddylist'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'], 'tx_hoicommunity_pi1[action]'=>'delete')));
	} else {
		$markerArray['###ADDBUDDY###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['buddyAdd'], array()) . ' ' . $this->pi_getLL('addBuddy');
		$wrappedSubpartArray['###LINKTOADDBUDDY###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['buddylist'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'], 'tx_hoicommunity_pi1[action]'=>'add')));
	}

	// Is this user already an ignored user?
	if ($this->isIgnoredUser($row['uid'])) {
		$markerArray['###ADDIGNOREDUSER###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['buddyDelete'], array()) . ' ' . $this->pi_getLL('deleteIgnoredUser');
		$wrappedSubpartArray['###LINKTOADDIGNOREDUSER###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['ignorelist'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'], 'tx_hoicommunity_pi1[action]'=>'delete')));
	} else {
		$markerArray['###ADDIGNOREDUSER###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['buddyAdd'], array()) . ' ' . $this->pi_getLL('addIgnoredUser');
		$wrappedSubpartArray['###LINKTOADDIGNOREDUSER###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['ignorelist'], '', array('tx_hoicommunity_pi1[uid]'=>$row['uid'], 'tx_hoicommunity_pi1[action]'=>'add')));
	}
}

$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['profile']),'###VIEWPROFILE###'),$markerArray, array(), $wrappedSubpartArray);

?>