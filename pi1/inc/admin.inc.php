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
 * This is the administration view for the HOI Community extension
 *
 * @author	Kevin Renskers [DMM Development]  <kevin(at)dauphin-mm.nl>
 */


switch($this->piVars['action']) {
	case 'disable_inactive':
		// People who never logged in and created their account over 3 months ago
		$where = 'pid='.$this->sysfolder.' AND disable=0 AND deleted=0 AND lastlogin=0 AND is_online=0 AND crdate < '.strtotime('-3 months');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'fe_users', $where);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$uid[$row['uid']]=$row['uid'];
		}

		// People who did login at least once, but over 3 months ago
		$where = 'pid='.$this->sysfolder.' AND disable=0 AND deleted=0 AND lastlogin!=0 AND lastlogin < '.strtotime('-3 months');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'fe_users', $where);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$uid[$row['uid']]=$row['uid'];
		}

		if ($uid) {
			// Disable these users and send them an activation email. If they don't activate within 48 hours, they will be deleted
			foreach ($uid AS $id) {
				$this->activate($id, 2);
			}
			$this->content .= '<p>'.count($uid).' inactivate accounts have been disabled.</p>';
		} else {
			$this->content .= '<p>Nothing to do!</p>';
		}
		$this->content .= '<p>'.$this->pi_linkTP('Back to administration menu');
	break;

	case 'delete_unactivated':
		// People who never logged in and created their account over 3 months ago
		$where =	'pid='.$this->sysfolder.' AND disable=1 AND deleted=0 AND tx_hoicommunity_activation!="" AND tstamp < '.strtotime('-48 hours');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,image', 'fe_users', $where);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$uid[$row['uid']]=$row['uid'];
			if ($row['image']) {
				$images[$row['uid']]=$row['image'];
			}
		}

		if ($uid) {
			$imploded_uids = implode(',',$uid);

			// Delete the users (set deleted flag to 1)
			$where = 'uid IN ('.$imploded_uids.')';
			$fields_values['deleted'] = 1;
			$fields_values['tstamp'] = time();
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users',$where,$fields_values);

			// Delete their buddylists and ignorelists
			$where = 'cruser_id IN ('.$imploded_uids.')';
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_buddylist', $where);
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_ignorelist', $where);

			// Delete them from other people's buddylists and ignorelists
			$where = 'fe_user_uid IN ('.$imploded_uids.')';
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_buddylist', $where);
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_ignorelist', $where);

			// Find all folders that belonged to the users
			$where = 'cruser_id IN ('.$imploded_uids.')';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_hoicommunity_folders', $where);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$folders[$row['uid']]=$row['uid'];
			}

			// Delete all these folders and the messages that belonged to these folders
			$where = 'cruser_id IN ('.$imploded_uids.')';
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_folders', $where);

			$imploded_folder_uids = implode(',',$folders);
			$where = 'folder_uid IN ('.$imploded_folder_uids.')';
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_hoicommunity_messages', $where);

			if ($images) {
				// Delete the images from the server
				foreach($images AS $image) {
					@unlink($this->uploadDir.$image);
				}
			}
			$this->content .= '<p>'.count($uid).' unactivated accounts have been deleted.</p>';
		} else {
			$this->content .= '<p>Nothing to do!</p>';
		}
		$this->content .= '<p>'.$this->pi_linkTP('Back to administration menu');
	break;

	case 'update':
	  // Copying buddylists
	  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cwtcommunity_buddylist', 'deleted=0 AND pid='.$this->sysfolder);
	  while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	      $values['pid'] = $row['pid'];
	      $values['tstamp'] = $row['tstamp'];
	      $values['crdate'] = $row['crdate'];
	      $values['cruser_id'] = $row['fe_users_uid'];
	      $values['fe_user_uid'] = $row['buddy_uid'];
	      $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_buddylist',$values);
	  }

	  $values=array();

	  // Copying messages
	  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cwtcommunity_message', 'deleted=0 AND pid='.$this->sysfolder);
	  while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	      $values['pid'] = $row['pid'];
	      $values['tstamp'] = $row['tstamp'];
	      $values['crdate'] = $row['crdate'];
	      $values['cruser_id'] = $row['cruser_id'];
	      $values['folder_uid'] = $this->inboxId($row['fe_users_uid']);
	      $values['subject'] = $row['subject'];
	      $values['body'] = $row['body'];
	      $values['been_read'] = $row['status'];
	      $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_messages',$values);
	  }

	  $values=array();

	  // Copying emoticons
	  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cwtcommunity_icons', 'deleted=0 AND pid='.$this->conf['pid.']['emoticons']);
	  while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	      $values['pid'] = $row['pid'];
	      $values['tstamp'] = $row['tstamp'];
	      $values['crdate'] = $row['crdate'];
	      $values['cruser_id'] = $row['cruser_id'];
	      $values['string'] = $row['string'];
	      $values['file'] = $row['icon'];
	      $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_hoicommunity_emoticons',$values);
	  }

	  // Copying files
	  $this->content .= '<p>You have to copy some files, and the best way is to do it by hand for now:</p>';
	  $this->content .= '<p>uploads/tx_cwtcommunity/icons -> uploads/tx_hoicommunity/emoticons</p>';
	  $this->content .= '<p>uploads/tx_cwtcommunity -> uploads/tx_hoicommunity</p>';
	break;

	default:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(uid) AS count', 'fe_users', 'disable=0 AND deleted=0');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$active = $row['count'];

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(uid) AS count', 'fe_users', 'disable=1 AND deleted=0');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$disabled = $row['count'];

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(uid) AS count', 'fe_users', 'deleted=1');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$deleted = $row['count'];

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(uid) AS count', 'fe_users', '');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$total = $row['count'];

		$this->content .= '<b>Account stats</b>';
		$this->content .= '<table cellspacing="0"><tr>';
		$this->content .= '<td style="padding-right: 30px;">active</td>';
		$this->content .= '<td style="text-align:right">'.$active.'</td>';
		$this->content .= '</tr><tr>';
		$this->content .= '<td>disabled</td>';
		$this->content .= '<td style="text-align:right">'.$disabled.'</td>';
		$this->content .= '</tr><tr>';
		$this->content .= '<td>deleted</td>';
		$this->content .= '<td style="text-align:right">'.$deleted.'</td>';
		$this->content .= '</tr><tr>';
		$this->content .= '<td>total</td>';
		$this->content .= '<td style="text-align:right">'.$total.'</td>';
		$this->content .= '</tr></table>';

		$this->content .= '<p>'.$this->pi_linkTP('Disable inactive accounts', array('tx_hoicommunity_pi1[action]'=>'disable_inactive')).'<br /><i>Accounts that have not been logged into for more then 3 months will be disabled, the user will get a re-activation email</i></p>';
		$this->content .= '<p>'.$this->pi_linkTP('Delete unactivated accounts', array('tx_hoicommunity_pi1[action]'=>'delete_unactivated')).'<br /><i>Accounts that have not been (re)activated within 48 hours will be deleted from the database, along with their messages, buddies and so on</i></p>';
		$this->content .= '<p>'.$this->pi_linkTP('Update from CWT Community', array('tx_hoicommunity_pi1[action]'=>'update')).'<br /><i>Convert your community from cwt to hoi - BETA!</i></p>';
	break;
}

?>