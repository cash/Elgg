<?php

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

echo elgg_view('install/nav', $vars);
