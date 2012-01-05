<?php
/**
 *
 */

class ElggCodeSniffer {

	static public function process() {
		require_once dirname(dirname(__FILE__)) . '/vendors/PHP_CodeSniffer/CodeSniffer.php';
		
		$phpcs = new PHP_CodeSniffer();
	}
}
