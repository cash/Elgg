<?php

$current_step = $vars['step'];
$steps = $vars['steps'];

echo '<ol>';
foreach ($steps as $step) {
	echo "<li>$step</li>";
}
echo '</ol>';
