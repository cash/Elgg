<?php
/**
 * Elgg settings
 *
 * Elgg manages most of its configuration from the admin panel. However, we need you to
 * include your database settings below.
 *
 * @todo Turn this into something we handle more automatically.
 */

global $CONFIG;
if (!isset($CONFIG)) {
	$CONFIG = new stdClass;
}

/*
 * Standard configuration
 *
 * You will use the same database connection for reads and writes.
 * This is the easiest configuration, and will suit 99.99% of setups. However, if you're
 * running a really popular site, you'll probably want to spread out your database connections
 * and implement database replication.  That's beyond the scope of this configuration file
 * to explain, but if you know you need it, skip past this section.
 */

// Database username
$CONFIG->dbuser = '{{user}}';

// Database password
$CONFIG->dbpass = '{{password}}';

// Database name
$CONFIG->dbname = '{{dbname}}';

// Database server
// (For most configurations, you can leave this as 'localhost')
$CONFIG->dbhost = '{{host}}';

// Database table prefix
// If you're sharing a database with other applications, you will want to use this
// to differentiate Elgg's tables.
$CONFIG->dbprefix = '{{prefix}}';


/**
 * Memcache setup (optional)
 * This is where you may optionally set up memcache.
 *
 * Requirements:
 * 	1) One or more memcache servers (http://www.danga.com/memcached/)
 *  2) PHP memcache wrapper (http://uk.php.net/manual/en/memcache.setup.php)
 *
 * Note: Multiple server support is only available on server 1.2.1 or higher with PECL library > 2.0.0
 */
//$CONFIG->memcache = true;
//
//$CONFIG->memcache_servers = array (
//	array('server1', 11211),
//	array('server2', 11211)
//);


/**
 * Some work-around flags.
 */

// Try uncommenting the below if your notification emails are not being sent
// $CONFIG->broken_mta = true;
