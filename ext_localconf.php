<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (t3lib_div::int_from_ver(TYPO3_version) >= 4001000) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['caretaker_integrity'] = array('EXT:caretaker_integrity/classes/class.tx_caretakerintegrity_Cli.php','_CLI_caretaker');
}

?>