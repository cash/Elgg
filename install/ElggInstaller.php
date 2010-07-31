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
		if ($this->isAction) {
			// create database and tables

			$this->continueToNextStep('database');
		}

		$this->render('database', $params);
	}

	function settings() {
		if ($this->isAction) {
			// save system settings

			$this->continueToNextStep('settings');
		}

		$this->render('settings', $params);
	}

	function admin() {
		if ($this->isAction) {
			// create admin account

			$this->continueToNextStep('admin');
		}

		$this->render('admin', $params);
	}

	function complete() {
		$this->render('complete');
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

	protected function continueToNextStep($currentStep) {
		$this->isAction = FALSE;
		//$nextStep = $this->getNextStep($currentStep);
		forward($this->getNextStepUrl($currentStep));
	}

	protected function getNextStep($currentStep) {
		return $this->steps[1 + array_search($currentStep, $this->steps)];
	}

	protected function getNextStepUrl($currentStep) {
		$nextStep = $this->steps[1 + array_search($currentStep, $this->steps)];
		return "/install.php?step=$nextStep";
	}
}
