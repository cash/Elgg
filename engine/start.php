<?php
/**
 * Elgg engine bootstrapper
 * Loads the various elements of the Elgg engine
 *
 * @package Elgg
 * @subpackage Core
 * @author Curverider Ltd
 * @link http://elgg.org/
 */

/*
 * No settings means a fresh install
 */
if (!file_exists(dirname(__FILE__) . '/settings.php')) {
	header("Location: install.php");
	exit;
}

/*
 * Basic profiling
 */
global $START_MICROTIME;
$START_MICROTIME = microtime(true);

/*
 * Create global CONFIG object
 */
global $CONFIG;
if (!isset($CONFIG)) {
	$CONFIG = new stdClass;
}

$lib_dir = dirname(__FILE__) . '/lib/';

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

// Register the error handler
set_error_handler('__elgg_php_error_handler');
set_exception_handler('__elgg_php_exception_handler');

/**
 * Load the system settings
 */
if (!include_once(dirname(__FILE__) . "/settings.php")) {
	throw new InstallationException("Elgg could not load the settings file.");
}

// load the rest of the library files from engine/lib/
$lib_files = array(
	// these want to be loaded first apparently?
	'database.php', 'actions.php',

	'admin.php', 'annotations.php', 'api.php', 'cache.php',
	'calendar.php', 'configuration.php', 'cron.php', 'entities.php',
	'export.php', 'extender.php', 'filestore.php', 'group.php',
	'input.php', 'install.php', 'location.php', 'mb_wrapper.php',
	'memcache.php', 'metadata.php', 'metastrings.php', 'notification.php',
	'objects.php', 'opendd.php', 'pagehandler.php',
	'pageowner.php', 'pam.php', 'plugins.php', 'query.php',
	'relationships.php', 'river.php', 'sites.php', 'social.php',
	'statistics.php', 'system_log.php', 'tags.php', 'usersettings.php',
	'users.php', 'version.php', 'widgets.php', 'xml.php', 'xml-rpc.php'
);

foreach ($lib_files as $file) {
	$file = $lib_dir . $file;
	elgg_log("Loading $file...");
	if (!include_once($file)) {
		throw new InstallationException("Could not load {$file}");
	}
}

// check if the install was completed
// @todo move into function
$installed = FALSE;
try {
	$installed = is_installed();
} catch (DatabaseException $e) {}
if (!$installed) {
	header("Location: install.php");
	exit;
}

// Autodetect some default configuration settings
set_default_config();

// Trigger events
trigger_elgg_event('boot', 'system');

// Load the plugins that are active
load_plugins();
trigger_elgg_event('plugins_boot', 'system');

// Trigger system init event for plugins
trigger_elgg_event('init', 'system');

// Regenerate the simple cache if expired.
// Don't do it on upgrade because upgrade does it itself.
if (!defined('upgrading')) {
	$view = get_input('view', 'default');
	$lastupdate = datalist_get("simplecache_lastupdate_$view");
	$lastcached = datalist_get("simplecache_lastcached_$view");
	if ($lastupdate == 0 || $lastcached < $lastupdate) {
		elgg_view_regenerate_simplecache($view);
	}
	// needs to be set for links in html head
	$CONFIG->lastcache = $lastcached;
}
