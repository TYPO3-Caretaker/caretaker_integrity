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

class tx_caretakerintegrity_CheckCoreIntegrityTestService extends tx_caretakerinstance_RemoteTestServiceBase {
	
	public function runTest() {
		list($isSuccessful, $remoteFingerprint, $remoteTYPO3Version, $testResult) = $this->getRemoteChecksum();
		if (!$isSuccessful) {
			if ($testResult) {
				return $testResult;
			}
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_warning, 0, $remoteFingerprint . ' / ' . $remoteTYPO3Version );
		} else {
			return $this->verifyFingerprint($remoteFingerprint, $remoteTYPO3Version);
		}
	}
	
	
	protected function getRemoteChecksum() {
		$operations = array();
		$operations[] = array(
			'GetFilesystemChecksum', 
			array('path' => 'typo3_src')
		);
		$operations[] = array(
			'GetTYPO3Version'
		);

		$commandResult = $this->executeRemoteOperations($operations);
		if (!$this->isCommandResultSuccessful($commandResult)) {
                        return array(false, false, false, $this->getFailedCommandResultTestResult($commandResult));
                }

		$results = $commandResult->getOperationResults();
		$isSuccessful = count($results) == 2 && $results[0]->isSuccessful() && $results[1]->isSuccessful();

                if ($isSuccessful) {
			$remoteFingerprint = $results[0]->getValue();
			$remoteTYPO3Version = $results[1]->getValue();
                } else {
                        return array(false, false, false, $this->getFailedOperationResultTestResult($results[0]));
                }
		
		return array($isSuccessful, $remoteFingerprint, $remoteTYPO3Version, false);
	}
	
	
	protected function getRemoteSingleFileChecksums() {
		$operations = array();
		$operations[] = array(
			'GetFilesystemChecksum', 
			array('path' => 'typo3_src', 'getSingleChecksums' => TRUE)
		);

		$commandResult = $this->executeRemoteOperations($operations);
		$results = $commandResult->getOperationResults();
		$remoteChecksums = $results[0]->getValue();
		
		return $remoteChecksums;
	}
	
	
	protected function verifyFingerprint($remoteFingerprint, $remoteTYPO3Version) {
		// TODO get path from config
		$path = 'EXT:caretaker_integrity/res/fingerprints/typo3_src-' . $remoteTYPO3Version . '.fingerprint';
		$path = t3lib_div::getFileAbsFileName($path);
		if (!file_exists($path)) {
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_warning, 0, 'Can\'t find local fingerprint for typo3_src-' . $remoteTYPO3Version );
		
		} else {
			$fingerprint = json_decode(file_get_contents($path), true);
			if ($fingerprint['checksum'] === $remoteFingerprint['checksum']) {
				return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, '');
			} else {
				$remote = $this->getRemoteSingleFileChecksums();
				$errornousFiles = array();
				foreach ($remote['singleChecksums'] as $file => $checksum) {
					// TODO whitelist
					if ($checksum !== $fingerprint['singleChecksums'][$file]) {
						$errornousFiles[] = $file;		
					}
				}
				if (count($errornousFiles) > 0) {
					return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, count($errornousFiles), 
						'Can\'t verify fingerprint (' . count($errornousFiles) . ' files differ) ' . chr(10) . 
						implode(chr(10) . ' - ', $errornousFiles)
					);
				} else {
					return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, 'Can\'t verify fingerprint (files seems to be ok, but over-all check failed)!' . chr(10) . 
						$fingerprint['checksum'] . ' !== ' . $remoteFingerprint['checksum'] );
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_integrity/services/class.tx_caretakerintegrity_CheckCoreIntegrityTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_integrity/services/class.tx_caretakerintegrity_CheckCoreIntegrityTestService.php']);
}
?>
