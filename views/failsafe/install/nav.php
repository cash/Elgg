<?php
/**
 * Navigation for installation pages
 *
 * @uses $vars['url']
 * @uses $vars['next_step']
 * @uses $vars['refresh']
 * @uses $vars['advance']
 */


// has a refresh button been requested
$refresh = '';
if (isset($vars['refresh']) && $vars['refresh']) {
	$refresh = "<a href=\"\">Refresh</a>";
}

// create next button and selectively disable
$next_link = "{$vars['url']}install.php?step={$vars['next_step']}";
$next = "<a href=\"$next_link\" disable=\"disable\">Next</a>";
if (isset($vars['advance']) && !$vars['advance']) {
	// disable the next button
	$next = "<a class=\"disabled\">Next</a>";
}


echo <<<___END
<div class="install_nav">
	$next
	$refresh
</div>

___END;
