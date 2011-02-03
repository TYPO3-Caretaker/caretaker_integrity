<?php 

if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}
if (t3lib_extMgm::isLoaded('caretaker') ){
	include_once(t3lib_extMgm::extPath('caretaker') . 'classes/helpers/class.tx_caretaker_ServiceHelper.php');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerintegrity_CheckCoreIntegrity',  'Integrity -> Check TYPO3-core source integrity', 'Find changed files in remote TYPO3-Core');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerintegrity_CheckFolderIntegrity',  'Integrity -> Check integrity of a folder or file', 'Find changed files in remote folder');
}

?>