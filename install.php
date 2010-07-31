<?php
/**
 * Elgg install script
 *
 * @package Elgg
 * @subpackage Core
 * @link http://elgg.org/
 */

global $CONFIG;
if (!isset($CONFIG)) {
	$CONFIG = new stdClass;
}

$lib_dir = dirname(__FILE__) . '/engine/lib/';

// bootstrapping with required files in a required order
$required_files = array(
	'exceptions.php', 'elgglib.php', 'views.php', 'access.php', 'system_log.php', 'export.php',
	'sessions.php', 'languages.php', 'input.php', 'install.php', 'cache.php', 'output.php'
);

foreach ($required_files as $file) {
	$path = $lib_dir . $file;
	if (!include($path)) {
		echo "Could not load file '$path'. "
		. 'Please check your Elgg installation for all required files.';
		exit;
	}
}

elgg_set_viewtype('failsafe');

// If we're already installed, go back to the homepage
// @todo

require_once(dirname(__FILE__) . "/install/ElggInstaller.php");

$installer = new ElggInstaller();

$step = get_input('step', 'welcome');
if (in_array($step, $installer->getSteps())) {
	$installer->$step();
} else {
	// throw exception
}
