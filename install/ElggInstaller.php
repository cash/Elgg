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

	public function __construct() {
	}

	public function getSteps() {
		return $this->steps;
	}

	function welcome() {
		$this->render('welcome');
	}

	function requirements() {

		// attempt to create .htaccess file

		// check PHP parameters

		// check permissions on engine directory

		// check rewrite module

		$this->render('requirements', $params);
	}

	function database() {
		// somehow determine whether this is an action or page request
		if ($action) {
			// create database and tables

			$this->continueToNextStep('database');
		}

		$this->render('database', $params);
	}

	function settings() {
		if ($action) {
			// save system settings

			$this->continueToNextStep('settings');
		}

		$this->render('settings', $params);
	}

	function admin() {
		if ($action) {
			// create admin account

			$this->continueToNextStep('admin');
		}

		$this->render('admin', $params);
	}

	function complete() {
		$this->render('complete');
	}

	protected function render($step, $vars = array()) {
		$title = elgg_echo("install:$step");
		$body = elgg_view("install/$step", $vars);
		page_draw($title, $body, 'page_shells/default', array('step' => $step));
		exit;
	}

	protected function continueToNextStep($currentStep) {
		$nextStep = $this->steps[1 + array_search($currentStep, $this->steps)];
		$this->$nextStep();
	}
}
