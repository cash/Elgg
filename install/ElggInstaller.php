<?php
/**
 * Elgg Installer.
 * Controller for installing Elgg.
 *
 * @package Elgg
 * @subpackage Installer
 * @author Curverider Ltd
 * @link http://elgg.org/
 */

class ElggInstaller {

	protected $steps = array(
		'welcome',
		'requirements',
		'database',
		'settings',
		'admin',
		'complete',
		);

	protected $isAction;

	public function __construct() {
		$this->isAction = $_SERVER['REQUEST_METHOD'] === 'POST';

		$this->bootstrapConfig();
		
		$this->bootstrapEngine();

		elgg_set_viewtype('failsafe');

		set_error_handler('__elgg_php_error_handler');
		set_exception_handler('__elgg_php_exception_handler');
	}

	public function run($step) {

		if (!in_array($step, $this->getSteps())) {
			throw new InstallationException("$step is an unknown installation step.");
		}

		$this->finishBootstraping($step);

		$params = $this->getPostVariables();
		$this->$step($params);
	}

	protected function render($step, $vars = array()) {

		$vars['next_step'] = $this->getNextStep($step);

		$title = elgg_echo("install:$step");
		$body = elgg_view("install/pages/$step", $vars);
		page_draw(
				$title,
				$body,
				'page_shells/install',
				array(
					'step' => $step,
					'steps' => $this->getSteps(),
					)
				);
		exit;
	}

	/**
	 * Step controllers
	 */

	protected function welcome($vars) {
		$this->render('welcome');
	}

	protected function requirements($vars) {

		// attempt to create .htaccess file

		// check PHP parameters

		// check permissions on engine directory

		// check rewrite module

		$this->render('requirements', $params);
	}

	protected function database($submissionVars) {

		$formVars = array(
			'dbuser' => array(
				'type' => 'text',
				'value' => '',
				'required' => TRUE,
				),
			'dbpassword' => array(
				'type' => 'password',
				'value' => '',
				'required' => TRUE,
				),
			'dbname' => array(
				'type' => 'text',
				'value' => '',
				'required' => TRUE,
				),
			'dbhost' => array(
				'type' => 'text',
				'value' => 'localhost',
				'required' => TRUE,
				),
			'dbprefix' => array(
				'type' => 'text',
				'value' => 'elgg_',
				'required' => TRUE,
				),
		);

		if ($this->isAction) {
			do {
				if (!$this->validateDatabaseVars($submissionVars, $formVars)) {
					// error so we break out of action and serve same page
					break;
				}

				if (!$this->createSettingsFile($submissionVars)) {
					break;
				}

				if (!$this->connectToDatabase()) {
					break;
				}

				if (!$this->installDatabase()) {
					break;
				}

				system_message('Database has been installed.');

				$this->continueToNextStep('database');
			} while (FALSE);  // PHP doesn't support breaking out of if statements
		}

		$formVars = $this->makeFormSticky($formVars, $submissionVars);

		$this->render('database', array('variables' => $formVars));
	}

	protected function settings($submissionVars) {
		global $CONFIG;
		
		$languages = get_installed_translations();
		$formVars = array(
			'sitename' => array(
				'type' => 'text',
				'value' => 'New Elgg site',
				'required' => TRUE,
				),
			'siteemail' => array(
				'type' => 'text',
				'value' => '',
				'required' => FALSE,
				),
			'wwwroot' => array(
				'type' => 'text',
				'value' => $CONFIG->wwwroot,
				'required' => TRUE,
				),
			'path' => array(
				'type' => 'text',
				'value' => $CONFIG->path,
				'required' => TRUE,
				),
			'dataroot' => array(
				'type' => 'text',
				'value' => '',
				'required' => TRUE,
				),
			'language' => array(
				'type' => 'pulldown',
				'value' => 'en',
				'options_values' => $languages,
				'required' => TRUE,
				),
			'siteaccess' => array(
				'type' => 'access',
				'value' =>  ACCESS_PUBLIC,
				'required' => TRUE,
				),
		);
		
		if ($this->isAction) {
			do {
				if (!$this->validateSettingsVars($submissionVars, $formVars)) {
					break;
				}

				if (!$this->saveSiteSettings($submissionVars)) {
					break;
				}
				
				system_message('Site settings have been saved.');

				$this->continueToNextStep('settings');

			} while (FALSE);  // PHP doesn't support breaking out of if statements
		}
		
		$formVars = $this->makeFormSticky($formVars, $submissionVars);

		$this->render('settings', array('variables' => $formVars));
	}

	protected function admin($vars) {
		if ($this->isAction) {
			// create admin account

			$this->continueToNextStep('admin');
		}

		$variables = array('displayname', 'username', 'password1', 'password2', 'email');
		$variables = array(
			'displayname' => array('type' => 'text', 'value' => '', ),
			'email'       => array('type' => 'text', 'value' => ''),
			'username'    => array('type' => 'text', 'value' => ''),
			'password1'   => array('type' => 'password', 'value' => ''),
			'password2'   => array('type' => 'password', 'value' => ''),
		);
		$params = array(
			'variables' => $variables,
		);
		$this->render('admin', $params);
	}

	protected function complete($vars) {
		$this->render('complete');
	}

	/**
	 * Step management
	 */

	protected function getSteps() {
		return $this->steps;
	}

	protected function continueToNextStep($currentStep) {
		$this->isAction = FALSE;
		forward($this->getNextStepUrl($currentStep));
	}

	protected function getNextStep($currentStep) {
		return $this->steps[1 + array_search($currentStep, $this->steps)];
	}

	protected function getNextStepUrl($currentStep) {
		global $CONFIG;
		$nextStep = $this->getNextStep($currentStep);
		return "{$CONFIG->wwwroot}install.php?step=$nextStep";
	}

	/**
	 * Bootstraping
	 */
	
	protected function bootstrapEngine() {
		global $CONFIG;

		$lib_dir = $CONFIG->path . 'engine/lib/';

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
	}

	protected function finishBootstraping($step) {
		$dbIndex = array_search('database', $this->getSteps());
		$stepIndex = array_search($step, $this->getSteps());

		if ($stepIndex <= $dbIndex) {
			session_name('Elgg');
			session_start();
		} else {
			global $CONFIG;
			$lib_dir = $CONFIG->path . 'engine/lib/';

			if (!include_once("{$CONFIG->path}engine/settings.php")) {
				throw new InstallationException("Elgg could not load the settings file.");
			}
			
			$lib_files = array(
				// these want to be loaded first apparently?
				'database.php', 'actions.php',

				'admin.php', 'annotations.php', 'api.php',
				'calendar.php', 'configuration.php', 'cron.php', 'entities.php',
				'extender.php', 'filestore.php', 'group.php',
				'location.php', 'mb_wrapper.php',
				'memcache.php', 'metadata.php', 'metastrings.php', 'notification.php',
				'objects.php', 'opendd.php', 'pagehandler.php',
				'pageowner.php', 'pam.php', 'plugins.php', 'query.php',
				'relationships.php', 'river.php', 'sites.php', 'social.php',
				'statistics.php', 'tags.php', 'usersettings.php',
				'users.php', 'version.php', 'widgets.php', 'xml.php', 'xml-rpc.php'
 			);
			
			foreach ($lib_files as $file) {
				$path = $lib_dir . $file;
				if (!include_once($path)) {
					throw new InstallationException("Could not load {$file}");
				}
			}

			set_default_config();

			trigger_elgg_event('boot', 'system');
			trigger_elgg_event('init', 'system');
		}
	}

	protected function bootstrapConfig() {
		global $CONFIG;
		if (!isset($CONFIG)) {
			$CONFIG = new stdClass;
		}

		$CONFIG->wwwroot = $this->getBaseUrl();
		$CONFIG->url = $CONFIG->wwwroot;
		$CONFIG->path = dirname(dirname(__FILE__)) . '/';
	}

	protected function getBaseUrl() {
		$protocol = 'http';
		if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			$protocol = 'https';
		}
		$port = ':' . $_SERVER["SERVER_PORT"];
		if ($port == ':80' || $port == ':443') {
			$port = '';
		}
		$uri = $_SERVER['REQUEST_URI'];
		$cutoff = strpos($uri, 'install.php');
		$uri = substr($uri, 0, $cutoff);

		$url = "$protocol://{$_SERVER['SERVER_NAME']}$port{$uri}";
		return $url;
	}

	/**
	 * Action handling methods
	 */
	
	protected function getPostVariables() {
		$vars = array();
		foreach ($_POST as $k => $v) {
			$vars[$k] = $v;
		}
		return $vars;
	}

	protected function makeFormSticky($formVars, $submissionVars) {
		foreach ($submissionVars as $field => $value) {
			$formVars[$field]['value'] = $value;
		}
		return $formVars;
	}

	/**
	 * Database support methods
	 */
	protected function validateDatabaseVars($submissionVars, $formVars) {

		foreach ($formVars as $field => $info) {
			if ($info['required'] == TRUE && !$submissionVars[$field]) {
				$name = elgg_echo("install:$field");
				register_error("$name is required");
				return FALSE;
			}
		}
		
		return $this->checkDatabaseSettings(
					$submissionVars['dbuser'],
					$submissionVars['dbpassword'],
					$submissionVars['dbname'],
					$submissionVars['dbhost']
				);
	}

	/**
	 * Confirm the settings for the database
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $dbname
	 * @param string $host
	 * @return bool
	 */
	function checkDatabaseSettings($user, $password, $dbname, $host) {
		$mysql_dblink = mysql_connect($host, $user, $password, true);
		if ($mysql_dblink == FALSE) {
			register_error('Unable to connect to the database with these settings.');
			return $FALSE;
		}

		$result = mysql_select_db($dbname, $mysql_dblink);

		mysql_close($mysql_dblink);

		if (!$result) {
			register_error("Unable to use database $dbname");
		}

		return $result;
	}

	protected function createSettingsFile($params) {
		global $CONFIG;

		$templateFile = "{$CONFIG->path}engine/settings.example.php";
		$template = file_get_contents($templateFile);
		if (!$template) {
			register_error('Unable to read engine/settings.example.php');
			return FALSE;
		}

		foreach ($params as $k => $v) {
			$template = str_replace("{{".$k."}}", $v, $template);
		}

		$settingsFilename = "{$CONFIG->path}engine/settings.php";
		$result = file_put_contents($settingsFilename, $template);
		if (!$result) {
			register_error('Unable to write engine/settings.php');
			return FALSE;
		}

		return TRUE;
	}

	protected function connectToDatabase() {
		global $CONFIG;

		if (!include_once("{$CONFIG->path}engine/settings.php")) {
			register_error("Elgg could not load the settings file.");
			return FALSE;
		}

		if (!include_once("{$CONFIG->path}engine/lib/database.php")) {
			register_error("Elgg could not load the database library.");
			return FALSE;
		}

		setup_db_connections();

		// check version

		return TRUE;
	}

	protected function installDatabase() {
		global $CONFIG;

		try {
			run_sql_script("{$CONFIG->path}engine/schema/mysql.sql");
		} catch (Exception $e) {
			register_error($e->getMessage());
			return FALSE;
		}
		
		return TRUE;
	}

	/**
	 * Site settings support methods
	 */
	protected function validateSettingsVars($submissionVars, $formVars) {

		foreach ($formVars as $field => $info) {
			if ($info['required'] == TRUE && !$submissionVars[$field]) {
				$name = elgg_echo("install:$field");
				register_error("$name is required");
				return FALSE;
			}
		}

		// check that data root is writable
		if (!is_writable($submissionVars['dataroot'])) {
			register_error("Your data directory {$submissionVars['dataroot']} is not writable by the web server.");
			return FALSE;
		}

		// check that data root is not subdirectory of Elgg root
		if (stripos($submissionVars['dataroot'], $submissionVars['path']) !== FALSE) {
			register_error("Your data directory {$submissionVars['dataroot']} must be outside of your install path for security.");
			return FALSE;
		}

		// @todo move is_email_address to a better library than users.php
		// check that email address is email address
		//if ($submissionVars['siteemail'] && !is_email_address($submissionVars['siteemail'])) {
		//	register_error("{$submissionVars['']} is not a valid email address.");
		//	return FALSE;
		//}

		// check that url is a url


		return TRUE;
	}

	protected function saveSiteSettings($submissionVars) {

		// ensure that file path, data path, and www root end in /
		$submissionVars['path'] = sanitise_filepath($submissionVars['path']);
		$submissionVars['dataroot'] = sanitise_filepath($submissionVars['dataroot']);
		$submissionVars['wwwroot'] = sanitise_filepath($submissionVars['wwwroot']);

		$site = new ElggSite();
		$site->name = $submissionVars['sitename'];
		$site->url = $submissionVars['wwwroot'];
		$site->access_id = ACCESS_PUBLIC;
		$site->email = $submissionVars['siteemail'];
		$guid = $site->save();

		if (!$guid) {
			register_error("Unable to create the site.");
			return FALSE;
		}

		// bootstrap site info
		$CONFIG->site_guid = $guid;
		$CONFIG->site = $site;

		
		//datalist_set('installed',time());
		datalist_set('path', $submissionVars['path']);
		datalist_set('dataroot', $submissionVars['dataroot']);
		datalist_set('default_site', $site->getGUID());
		datalist_set('version', get_version());

		set_config('view', 'default', $site->getGUID());
		set_config('language', $submissionVars['language'], $site->getGUID());
		set_config('default_access', $submissionVars['siteaccess'], $site->getGUID());
		set_config('allow_registration', TRUE, $site->getGUID());
		set_config('walled_garden', FALSE, $site->getGUID());

		// activate some plugins by default
		// activate plugins with manifest.xml: elgg_install_state = enabled
		$plugins = get_plugin_list();
		var_dump($plugins);
		foreach ($plugins as $plugin) {
			if ($manifest = load_plugin_manifest($plugin)) {
				if (isset($manifest['elgg_install_state']) && $manifest['elgg_install_state'] == 'enabled') {
					enable_plugin($plugin);
				}
			}
		}

		// reset the views path in case of installing over an old data dir.
		$dataroot = datalist_get('dataroot');
		$cache = new ElggFileCache($dataroot);
		$cache->delete('view_paths');

		return TRUE;
	}
}
