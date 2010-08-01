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

		session_name('Elgg');
		session_start();
	}

	public function run($step) {
		$params = $this->getPostVariables();
		if (in_array($step, $this->getSteps())) {
			$this->$step($params);
		} else {
			throw new InstallationException("$step is an unknown installation step.");
		}
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
			'user' => array(
				'type' => 'text',
				'value' => '',
				'required' => TRUE,
				),
			'password' => array(
				'type' => 'password',
				'value' => '',
				'required' => TRUE,
				),
			'dbname' => array(
				'type' => 'text',
				'value' => '',
				'required' => TRUE,
				),
			'host' => array(
				'type' => 'text',
				'value' => 'localhost',
				'required' => TRUE,
				),
			'prefix' => array(
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

				//$this->createSettingsFile($params);

				//$this->bootstrapDatabaseSettings();

				//$this->loadLibrary('database');

				//$this->installDatabase($params);

				$this->continueToNextStep('database');
			} while(FALSE);
		}

		$params = array(
			'variables' => $formVars,
		);

		$this->render('database', $params);
	}

	protected function settings($vars) {
		if ($this->isAction) {
			// save system settings

			$this->continueToNextStep('settings');
		}

		global $CONFIG;
		//$languages = get_installed_translations();
		$variables = array(
			'sitename'   => array('type' => 'text', 'value' => 'New Elgg site'),
			'siteemail'  => array('type' => 'text', 'value' => ''),
			'wwwroot'    => array('type' => 'text', 'value' => $CONFIG->wwwroot),
			'path'       => array('type' => 'text', 'value' => $CONFIG->path),
			'dataroot'   => array('type' => 'text', 'value' => ''),
			//'language' => array('type' => 'pulldown', 'value' => 'en', 'options_values' => $languages),
			//'siteaccess' => array('type' => 'access', 'value' =>  ACCESS_PUBLIC,),
		);

		$params = array(
			'variables' => $variables,
		);

		$this->render('settings', $params);
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
		
		// attempt to connect to database

		return $params;
	}

	protected function createSettingsFile($params) {
		global $CONFIG;

		$templateFile = "{$CONFIG->path}engine/settings.example.php";
		$template = file_get_contents($templateFile);
		if (!$template) {
			// throw exception
		}

		foreach ($params as $k => $v) {
			$template = str_replace("{{".$k."}}", $v, $template);
		}

		$settingsFilename = "{$CONFIG->path}engine/settings.php";
		$result = file_put_contents($settingsFilename, $template);
		if (!$result) {
			// throw exception
		}
	}

	protected function bootstrapDatabaseSettings() {
		global $CONFIG;

		if (!include_once("{$CONFIG->path}engine/settings.php")) {
			throw new InstallationException("Elgg could not load the settings file.");
		}
	}

	protected function installDatabase($params) {
		global $CONFIG;
		
		run_sql_script("{$CONFIG->path}engine/schema/mysql.sql");
	}

}
