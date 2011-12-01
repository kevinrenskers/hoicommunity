<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_hoicommunity_messages"] = Array (
	"ctrl" => $TCA["tx_hoicommunity_messages"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "folder_uid,subject,body,been_read"
	),
	"feInterface" => $TCA["tx_hoicommunity_messages"]["feInterface"],
	"columns" => Array (
		"folder_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_messages.folder_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "tx_hoicommunity_folders",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"subject" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_messages.subject",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"max" => "255",
				"eval" => "required,trim",
			)
		),
		"body" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_messages.body",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "4",
			)
		),
		"been_read" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_messages.been_read",
			"config" => Array (
				"type" => "check",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => ";;;1-1-1, folder_uid, subject, body, been_read")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_hoicommunity_folders"] = Array (
	"ctrl" => $TCA["tx_hoicommunity_folders"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "name,is_inbox"
	),
	"feInterface" => $TCA["tx_hoicommunity_folders"]["feInterface"],
	"columns" => Array (
		"name" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_folders.name",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"max" => "255",
				"eval" => "required,trim",
			)
		),
		"is_inbox" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_folders.is_inbox",
			"config" => Array (
				"type" => "check",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "name;;;;1-1-1, is_inbox")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_hoicommunity_buddylist"] = Array (
	"ctrl" => $TCA["tx_hoicommunity_buddylist"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "fe_user_uid"
	),
	"feInterface" => $TCA["tx_hoicommunity_buddylist"]["feInterface"],
	"columns" => Array (
		"fe_user_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_buddylist.fe_user_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "fe_users",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => ";1;;1-1-1, fe_user_uid")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_hoicommunity_ignorelist"] = Array (
	"ctrl" => $TCA["tx_hoicommunity_ignorelist"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "fe_user_uid"
	),
	"feInterface" => $TCA["tx_hoicommunity_ignorelist"]["feInterface"],
	"columns" => Array (
		"fe_user_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_ignorelist.fe_user_uid",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "fe_users",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => ";1;;1-1-1, fe_user_uid")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_hoicommunity_emoticons"] = Array (
	"ctrl" => $TCA["tx_hoicommunity_emoticons"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,string,file"
	),
	"feInterface" => $TCA["tx_hoicommunity_emoticons"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"string" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_emoticons.string",
			"config" => Array (
				"type" => "input",
				"size" => "5",
				"max" => "10",
				"eval" => "trim",
			)
		),
		"file" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:hoicommunity/locallang_db.xml:tx_hoicommunity_emoticons.file",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",
				"max_size" => 25,
				"uploadfolder" => "uploads/tx_hoicommunity/emoticons",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, string, file")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>