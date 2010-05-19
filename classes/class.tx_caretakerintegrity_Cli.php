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

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

require_once(PATH_t3lib.'class.t3lib_cli.php');

require_once(t3lib_extMgm::extPath('caretaker_instance') . 'classes/class.tx_caretakerinstance_Operation_GetFilesystemChecksum.php');


class tx_caretakerintegrity_Cli extends t3lib_cli {
	
	/**
	 * Constructor
	 */
    public function __construct () {

       		// Running parent class constructor
        parent::t3lib_cli();

       		// Setting help texts:
        $this->cli_help['name'] = 'Caretaker_integrity CLI';        
        $this->cli_help['synopsis'] = 'help|fingerprint path';
        $this->cli_help['description'] = 'tbd'; // TODO
        $this->cli_help['examples'] = '../cli_dispatch.phpsh caretaker_integrity fingerprint';
        $this->cli_help['author'] = 'Tobias Liebig, (c) 2009';
        
        // $this->cli_options[]=array('-r', 'Return status code');
    }
    
    /**
     * CLI engine
     *
     * @param    array        Command line arguments
     * @return    string
     */
	public function cli_main($argv) {
		$this->cli_validateArgs();
		
        
		$task = (string)$this->cli_args['_DEFAULT'][1];
		
        switch ($task) {
        	case 'fingerprint':
        		$path = (string)$this->cli_args['_DEFAULT'][2];
        		if (empty($path)) {
        			$this->cli_echo('path is missing');
        			break;
        		}
        		$fingerprint = $this->getFingerprint($path);
        		
        		echo $fingerprint;
        		break;
        	
        	default:
        	case 'help':
				
				$this->cli_help();
        		break;
        }
    }
    
    
    protected function getFingerprint($path) {
    	$operation = t3lib_div::makeinstance('tx_caretakerinstance_Operation_GetFilesystemChecksum');
 
    	// $operation = new tx_caretakerinstance_Operation_GetFilesystemChecksum();
		
		$result = $operation->execute(array('path' => $path, 'getSingleChecksums' => true));
		
		if ($result->isSuccessful()) {
			 $fingerprint = json_encode($result->getValue());
			 return $fingerprint;
		} else {
			$this->cli_echo('Can\'t create fingerprint for "' . $path . '": ' . $result->getValue());
			return false;
		}
    }
    
    
    /**
     * Get a spcific CLI Argument
     * 
     * @param string $name
     * @param string $alt_name
     * @return string
     */
    private function readArgument($name, $alt_name = FALSE) {
    	if ( $name &&  isset($this->cli_args[$name]) ) {
    		if ($this->cli_args[$name][0]) {
    			return $this->cli_args[$name][0];
    		} else {
    			return TRUE;
    		}
    	} else if  ($alt_name) {
    		return $this->readArgument($alt_name);
    	} else {
    		return FALSE;
    	}
    }
}

// Call the functionality
$sobe = t3lib_div::makeInstance('tx_caretakerintegrity_Cli');
$sobe->cli_main($_SERVER['argv']);


?>
