<?php
/**
 * Install requirements checking page
 *
 * @uses $vars['num_failures] Number of requirements failures
 */

echo elgg_echo('install:requirements:instructions');

$report = $vars['report'];
foreach ($report as $category => $checks) {
	$title = elgg_echo("install:require:$category");
	echo "<h3>$title</h3>";
	echo "<ul>";
	foreach ($checks as $check) {
		echo "<li class=\"{$check['severity']}\">";
		echo $check['message'];
		echo "</li>";
	}
	echo "</ul>";
}

$vars['refresh'] = TRUE;

// cannot advance to next step with a failure
if ($vars['num_failures'] != 0) {
	$vars['advance'] = FALSE;
}

echo elgg_view('install/nav', $vars);
