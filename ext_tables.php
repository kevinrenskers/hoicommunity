<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_hoicommunity_messages"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_messages',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_hoicommunity_messages.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "folder_uid, subject, body, been_read",
	)
);

$TCA["tx_hoicommunity_folders"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_folders',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_hoicommunity_folders.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "name, is_inbox",
	)
);

$TCA["tx_hoicommunity_buddylist"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_buddylist',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_hoicommunity_buddylist.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "fe_user_uid",
	)
);

$TCA["tx_hoicommunity_ignorelist"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_ignorelist',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_hoicommunity_ignorelist.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "fe_user_uid",
	)
);

$TCA["tx_hoicommunity_emoticons"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_emoticons',
		'label' => 'string',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_hoicommunity_emoticons.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, string, file",
	)
);

$tempColumns = Array (
	"tx_hoicommunity_birthday" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:hoicommunity/locallang_db.xml:fe_users.tx_hoicommunity_birthday",
		"config" => Array (
			"type" => "input",
			"size" => "8",
			"max" => "20",
			"eval" => "date",
			"checkbox" => "0",
			"default" => "0"
		)
	),
	"tx_hoicommunity_information" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:hoicommunity/locallang_db.xml:fe_users.tx_hoicommunity_information",
		"config" => Array (
			"type" => "text",
			"cols" => "30",
			"rows" => "4",
		)
	),

	'first_name' => array (
		'label' => 'FIRST_NAME:',
		'exclude' => 0,
		'config' => array (
			'type' => 'input',
			'size' => '8',
			'max' => '50',
			'eval' => 'trim',
			'default' => ''
		)
	),

	'last_name' => array (
		'label' => 'LAST_NAME:',
		'exclude' => 0,
		'config' => array (
			'type' => 'input',
			'size' => '8',
			'max' => '50',
			'eval' => 'trim',
			'default' => ''
		)
	),

	'tx_hoicommunity_activation' => array (
		'label' => 'TX_HOICOMMUNITY_ACTIVATION:',
		'exclude' => 0,
		'config' => array (
			'type' => 'input',
			'size' => '8',
			'max' => '20',
			'eval' => 'trim',
			'default' => ''
		)
	),

);

$TCA['fe_users']['columns']['image']['config']['uploadfolder'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['uploadFolder'];
$TCA['fe_users']['columns']['image']['config']['max_size'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['imageMaxSize'];
$TCA['fe_users']['columns']['image']['config']['allowed'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['imageTypes'];

t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_hoicommunity_birthday;;;;1-1-1, tx_hoicommunity_information");
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPlugin(Array('LLL:EXT:hoicommunity/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","HOI Community default CSS");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:hoicommunity/flexform_ds.xml');

?>