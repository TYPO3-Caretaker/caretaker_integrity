<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Tobias Liebig <liebig@networkteam.com>
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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'));

class tx_caretakerintegrity_CheckFolderIntegrityTestService extends tx_caretakerinstance_RemoteTestServiceBase {
	
	public function runTest() {
		$path = $this->getConfigValue('path');
		
		$fingerprint = $this->getLocalFingerprint();
		if (!$fingerprint || empty($fingerprint['checksum'])) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, 0, 'Can\'t get local fingerprint (configuration error)');
		}
		
		list($isSuccessful, $remoteFingerprint) = $this->getRemoteFingerprint($path);
		if (!$isSuccessful || empty($remoteFingerprint)) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, 0, $remoteFingerprint);
		}
		
		return $this->verifyFingerprint($path, $remoteFingerprint, $fingerprint);
	}
	
	protected function getLocalFingerprint() {
		$preset = $this->getConfigValue('checksum_preset');
		
		if (!empty($preset)) {
			$fingerprint = $this->getFingerprintPreset($preset);
			if (!$fingerprint) {
				return false;
			}
			return $fingerprint;

		} else {
			$expectedChecksum = $this->getConfigValue('checksum');
			return array('checksum' => $expectedChecksum);
		}
	}
	
	protected function getFingerprintPreset($preset) {
		
		// preset shoul be a .fingerprint file
		if (substr($preset,-12) !== '.fingerprint') {
			$preset = '';
		}
		
		$path = 'EXT:caretaker_integrity/res/fingerprints/' . $preset;
		$path = t3lib_div::getFileAbsFileName($path);
		if (!file_exists($path)) {
			return false;
		} else {
			$fingerprint = json_decode(file_get_contents($path), true);
		}
		return $fingerprint;
	}
	
	protected function getRemoteFingerprint($path, $getSingleChecksums = false) {
		$operations = array();
		$operations[] = array(
			'GetFilesystemChecksum', 
			array(
				'path' => $path,
				'getSingleChecksums' => (bool)$getSingleChecksums
			)
		);

		$commandResult = $this->executeRemoteOperations($operations);
		$results = $commandResult->getOperationResults();
		
		return array(
			$results[0]->isSuccessful(), 
			$results[0]->getValue()
		);
	}
	
	protected function verifyFingerprint($path, $remoteFingerprint, $expectedFingerprint) {
		if ($expectedFingerprint['checksum'] === $remoteFingerprint['checksum']) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0, '');
		} else {
			if (is_array($expectedFingerprint['singleChecksums'])) {
				return $this->verifySingleChecksums($path, $expectedFingerprint);
			}
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'Can\'t verify fingerprint (files differ)!');
		}
	}
	
	protected function verifySingleChecksums($path, $expectedFingerprint) {
		list($isSuccessful, $remoteFingerprint) = $this->getRemoteFingerprint($path, true);
		if (!$isSuccessful || empty($remoteFingerprint)) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, 0, $remoteFingerprint);
		}
		
		foreach ($remoteFingerprint['singleChecksums'] as $file => $checksum) {
			if ($checksum !== $expectedFingerprint['singleChecksums'][$file]) {
				$errornousFiles[] = $file;		
			}
		}
		
		if (count($errornousFiles) > 0) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, count($errornousFiles), 
				'Can\'t verify fingerprint (' . count($errornousFiles) . ' files differ) ' . chr(10) . 
				implode(chr(10) . ' - ', $errornousFiles)
			);
		} else {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'Can\'t verify fingerprint (files seems to be ok, but over-all check failed)!');
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_integrity/services/class.tx_caretakerintegrity_CheckFolderIntegrityTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_integrity/services/class.tx_caretakerintegrity_CheckFolderIntegrityTestService.php']);
}
?>