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
 * This is the messages view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */

$subpartArray['###SUBJECT_LABEL###'] = $markerArray['###SUBJECT_LABEL###'] = $this->pi_getLL('subject');
$subpartArray['###SENDER_LABEL###'] = $markerArray['###SENDER_LABEL###'] = $this->pi_getLL('sender');
$subpartArray['###DATE_LABEL###'] = $markerArray['###DATE_LABEL###'] = $this->pi_getLL('date');
$subpartArray['###BODY_LABEL###'] = $markerArray['###BODY_LABEL###'] = $this->pi_getLL('body');
$subpartArray['###FOLDER_LABEL###'] = $markerArray['###FOLDER_LABEL###'] = $this->pi_getLL('folder');
$subpartArray['###RECEIVER_LABEL###'] = $markerArray['###RECEIVER_LABEL###'] = $this->pi_getLL('receiver');

if ($this->piVars['delete']) {
	// Remove a message
	$select = 'tx_hoicommunity_messages.uid';
	$from = 	'tx_hoicommunity_messages, tx_hoicommunity_folders';
	$where = 	'tx_hoicommunity_folders.cruser_id = '.intval($this->fe_user->uid).' AND
						 tx_hoicommunity_folders.uid = tx_hoicommunity_messages.folder_uid AND
						 tx_hoicommunity_messages.uid='.intval($this->piVars['delete']);

	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);
	if ($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_messages','uid='.intval($this->piVars['delete']));
	}
}

if ($this->piVars['reciever'] AND !$this->piVars['submit']) {
	// Show form to send message

	$markerArray['###FORM_URL###'] = $this->pi_getPageLink($this->conf['pid.']['messages'], '', array('tx_hoicommunity_pi1[reciever]'=>$this->piVars['reciever']));
	$markerArray['###FORM_ENCTYPE###'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'];
	$markerArray['###FORM_ONSUBMIT###'] = 'try { var myValidator = validateSendMessage; } catch(e) { return true; } return myValidator(this);';

	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('username', 'fe_users', 'uid='.intval($this->piVars['reciever']), '', '');
	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

	$markerArray['###RECEIVER_VALUE###'] = htmlspecialchars($row['username']);
	$markerArray['###SUBJECT_VALUE###'] = '';
	$markerArray['###BODY_VALUE###'] = '';
	if ($this->piVars['reply']) {
		// We got a message to quote!

		$select =	'tx_hoicommunity_messages.subject AS subject,
							 tx_hoicommunity_messages.body AS body';
		$from = 	'tx_hoicommunity_messages, tx_hoicommunity_folders';
		$where =	'tx_hoicommunity_messages.folder_uid = tx_hoicommunity_folders.uid AND
							 tx_hoicommunity_folders.cruser_id = '.intval($this->fe_user->uid).' AND
							 tx_hoicommunity_messages.uid = '.intval($this->piVars['reply']);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', '');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		// Quote the original message
		$markerArray['###BODY_VALUE###'] = '[quote]' . $row['body'] . '[/quote]' . "\n\n";

		// Add 'Re: ' to the subject if that isn't already in front of it
		if (strpos($row['subject'], 'Re: ') !== 0) {
			$markerArray['###SUBJECT_VALUE###'] = 'Re: ' . $row['subject'];
		} else {
			$markerArray['###SUBJECT_VALUE###'] = $row['subject'];
		}
	}

	$markerArray['###SUBJECT_FIELD###'] = 'tx_hoicommunity_pi1[subject]';
	$markerArray['###BODY_FIELD###'] = 'tx_hoicommunity_pi1[body]';
	$markerArray['###SUBMIT_FIELD###'] = 'tx_hoicommunity_pi1[submit]';
	$markerArray['###SUBMIT_VALUE###'] = $this->pi_getLL('sendMessageSubmit');

	$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['messages']),'###MESSAGE_SEND###'), $markerArray, array(), $wrappedSubpartArray);

} elseif ($this->piVars['message']) {
	// Single message view

	$select =	'fe_users.username AS username,
						 fe_users.uid AS sender_uid,
						 tx_hoicommunity_folders.name AS folder_name,
						 tx_hoicommunity_messages.been_read AS been_read,
						 tx_hoicommunity_messages.uid AS uid,
						 tx_hoicommunity_messages.crdate AS crdate,
						 tx_hoicommunity_messages.subject AS subject,
						 tx_hoicommunity_messages.body AS body';
	$from = 	'tx_hoicommunity_messages, fe_users, tx_hoicommunity_folders';
	$where =	'tx_hoicommunity_messages.folder_uid = tx_hoicommunity_folders.uid AND
						 tx_hoicommunity_folders.cruser_id = '.intval($this->fe_user->uid).' AND
						 tx_hoicommunity_messages.uid = '.intval($this->piVars['message']).' AND
						 tx_hoicommunity_messages.cruser_id = fe_users.uid';

	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', '');
	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

	// Update message: set been_read to 1
	if (!$row['been_read']) {
		$values['been_read'] = 1;
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_hoicommunity_messages', 'uid='.intval($this->piVars['message']), $values);
	}

	$markerArray['###DATE###'] = $this->cObj->stdWrap($row['crdate'], $this->conf['datetime_stdWrap.']);
	$markerArray['###USERNAME###'] = htmlspecialchars($row['username']);
	$markerArray['###SUBJECT###'] = htmlspecialchars($row['subject']);
	$markerArray['###FOLDER###'] = htmlspecialchars($row['folder_name']);
	$markerArray['###BODY###'] = nl2br($this->parseEmoticons($this->parseBBCodeQuotes($row['body'])));

	$wrappedSubpartArray['###LINKTOPROFILE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$row['sender_uid'])));
	$wrappedSubpartArray['###LINKTOREPLY###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['messages'], '', array('tx_hoicommunity_pi1[reciever]'=>$row['sender_uid'], 'tx_hoicommunity_pi1[reply]'=>$row['uid'])));
	$wrappedSubpartArray['###LINKTODELETE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['messages'], '', array('tx_hoicommunity_pi1[delete]'=>$row['uid'])));
	$wrappedSubpartArray['###LINKTOMESSAGES###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['messages']));

	$markerArray['###REPLY###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['messageReply'], array()) . ' ' . $this->pi_getLL('messageReply');
	$markerArray['###DELETE###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['messageDelete'], array()) . ' ' . $this->pi_getLL('messageDelete');
	$markerArray['###BACKTOLIST###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['messageRead'], array()) . ' ' . $this->pi_getLL('backToMessageList');

	$this->content .= $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['messages']),'###MESSAGE_SINGLE###'), $markerArray, array(), $wrappedSubpartArray);

} else {
	// Lists all messages

	if ($this->piVars['reciever'] AND $this->piVars['submit']) {
		if (!$this->isIgnored($this->piVars['reciever'])) {
			// Insert the message into the database
			$inboxid = $this->inboxId($this->piVars['reciever']);

			$values['pid'] = $this->sysfolder;
			$values['tstamp'] = time();
			$values['crdate'] = time();
			$values['cruser_id'] = $this->fe_user->uid;
			$values['folder_uid'] = $inboxid;
			$values['subject'] = strip_tags($this->piVars['subject']);
			$strings = array('<', '>', ' & ');
			$replace = array('&lt;', '&gt;', ' &amp; ');
			$values['body'] = str_replace($strings, $replace, $this->piVars['body']);
			$values['body'] = strip_tags($values['body']);
			$values['been_read'] = 0;
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_messages',$values);

			$this->content .= '<p>' . $this->pi_getLL('messageSent') . '</p>';
		} else {
			$this->content .= '<p>' . $this->pi_getLL('messageIgnored') . '</p>';
		}
	}

	$folder_uid = $this->piVars['folder'] ? $this->piVars['folder'] : $this->inboxId($this->fe_user->uid);

	$select =	'fe_users.uid AS sender_uid,
						 fe_users.username AS username,
						 tx_hoicommunity_messages.been_read AS been_read,
						 tx_hoicommunity_messages.uid AS uid,
						 tx_hoicommunity_messages.crdate AS crdate,
						 tx_hoicommunity_messages.subject AS subject';
	$from = 	'tx_hoicommunity_messages, fe_users, tx_hoicommunity_folders';
	$where =	'tx_hoicommunity_messages.folder_uid = '.intval($folder_uid).' AND
						 tx_hoicommunity_messages.folder_uid = tx_hoicommunity_folders.uid AND
						 tx_hoicommunity_folders.cruser_id = '.intval($this->fe_user->uid).' AND
						 tx_hoicommunity_messages.cruser_id = fe_users.uid';
	$sort = 	'tx_hoicommunity_messages.crdate DESC';

	// Only show $this->conf['maxMessagesPerFolder'] messages!
	if ($this->conf['maxMessagesPerFolder']) {
		$limit = $this->conf['maxMessagesPerFolder'];
	}

	$template['total'] = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['resource.']['template.']['messages']),'###MESSAGE_LIST###');
	$template['message'] = $this->cObj->getSubpart($template['total'],'###MESSAGE###');

	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', $sort, $limit);
	while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		$markerArray['###DATE###'] = $this->cObj->stdWrap($row['crdate'], $this->conf['datetime_stdWrap.']);
		$markerArray['###USERNAME###'] = htmlspecialchars($row['username']);
		$markerArray['###SUBJECT###'] = htmlspecialchars($row['subject']);

		if ($row['been_read']) {
			$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['messageRead'], array('altText'=>$this->pi_getLL('messageRead')));
		} else {
			$markerArray['###STATUSICON###'] = $this->cObj->cImage($this->conf['resource.']['icon.']['messageNew'], array('altText'=>$this->pi_getLL('messageNew')));
		}

		$markerArray['###ACTIONS###'] = '<img class="clickable" src="'.$GLOBALS['TSFE']->tmpl->getFileName($this->conf['resource.']['icon.']['messageDelete']).'" alt="'.$this->pi_getLL('messageDelete').'" title="'.$this->pi_getLL('messageDelete').'" onclick="deleteMessage('.$row['uid'].')" />';
		$markerArray['###ROWID###'] = 'tx-hoicommunity-pi1-messagelist-message'.$row['uid'];

		$wrappedSubpartArray['###LINKTOPROFILE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['profile'], '', array('tx_hoicommunity_pi1[uid]'=>$row['sender_uid'])));
		$wrappedSubpartArray['###LINKTOMESSAGE###'] = explode('|', $this->pi_linkToPage('|', $this->conf['pid.']['messages'], '', array('tx_hoicommunity_pi1[message]'=>$row['uid'])));
		$messages .= $this->cObj->substituteMarkerArrayCached($template['message'], $markerArray, array(), $wrappedSubpartArray);
	}
	$GLOBALS['TYPO3_DB']->sql_free_result($res);

	$subpartArray['###CONTENT###'] = $messages;

	$this->content .= $this->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray, array());

	// If user has too many messages show a messages telling him this
	if ($this->conf['maxMessagesPerFolder']) {
		$select =	'count(uid) AS pms';
		$from = 	'tx_hoicommunity_messages';
		$where =	'folder_uid='.intval($folder_uid);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, '', '');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row['pms'] > $this->conf['maxMessagesPerFolder']) {
			$this->content .= '<p>' . $this->pi_getLL('tooManyMessagesPre') . ' ' . $this->conf['maxMessagesPerFolder'] . ' ' . $this->pi_getLL('tooManyMessagesPost') . '</p>';
		}
	}

}
?>