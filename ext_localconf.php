<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_hoicommunity_pi1 = < plugin.tx_hoicommunity_pi1.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_hoicommunity_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_hoicommunity_pi1.php','_pi1','list_type',0);

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['uploadFolder'] = $_EXTCONF['uploadFolder'] ? $_EXTCONF['uploadFolder'] : 'uploads/tx_hoicommunity';

?>