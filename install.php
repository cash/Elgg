<?php
/**
 * Elgg install script
 *
 * @package Elgg
 * @subpackage Core
 * @link http://elgg.org/
 */

// If we're already installed, go back to the homepage
// @todo

require_once(dirname(__FILE__) . "/install/ElggInstaller.php");

$installer = new ElggInstaller();

$step = get_input('step', 'welcome');
$installer->run($step);
