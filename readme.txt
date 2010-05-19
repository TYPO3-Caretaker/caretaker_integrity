*create new timestamps*

unlink typo3_src
ln -s /usr/local/share/typo3/typo3_src-4.3.1 typo3_src
htdocs/typo3/cli_dispatch.phpsh caretaker_integrity fingerprint typo3_src > htdocs/typo3conf/ext/caretaker_integrity/res/fingerprints/typo3_src-4.3.1.fingerprint 

