<?php
/**
 * Elgg fallback pageshell
 * Render a few things (like the installation process) in a fallback mode, text only with minimal use
 * of functions.
 *
 * @package Elgg
 * @subpackage Core
 * @author Curverider Ltd
 * @link http://elgg.org/
 *
 * @uses $vars['config'] The site configuration settings, imported
 * @uses $vars['title'] The page title
 * @uses $vars['body'] The main content of the page
 * @uses $vars['messages'] A 2d array of various message registers, passed from system_messages()
 */

// we won't trust server configuration but specify utf-8
header('Content-type: text/html; charset=utf-8');

// turn off browser caching
header('Pragma: public', TRUE);
header("Cache-Control: no-cache, must-revalidate", TRUE);
header('Expires: Fri, 05 Feb 1982 00:00:00 -0500', TRUE);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo $vars['title']; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="SHORTCUT ICON" href="<?php echo $vars['url']; ?>_graphics/favicon.ico" />
		<link rel="stylesheet" href="<?php echo $vars['url']; ?>install/install.css" type="text/css" />
	</head>
	<body>
	<div id="elgg_wrapper">
		<h1><?php echo $vars['title']; ?></h1>

		<!-- display any system messages -->
		<?php echo elgg_view('messages/list', array('object' => $vars['sysmessages'])); ?>

		<div id="elgg_sidebar">
			<?php echo elgg_view('install/sidebar', $vars); ?>
		</div>
		<div id="elgg_content">
			<?php echo $vars['body']; ?>
		</div>
		<div id="elgg_footer">
			@todo footer
		</div>
	</div>
	</body>
</html>
