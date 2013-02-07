<?php
/**
 * CKEditor upload result
 * 
 * CKEditor requires that a html page be returned with inline JavaScript. Haven't
 * figured out any other way to do this. 
 * 
 * @uses $vars['callback'] The callback ID for CKEditor
 * @uses $vars['url']      URL for the image
 * @uses $vars['msg']      Error message (optional)
 */

$callback = (int)elgg_extract('callback', $vars);
$url = (string)elgg_extract('url', $vars, '');
$msg = (string)elgg_extract('msg', $vars, '');

// no built in JavaScript escaper in Elgg yet - this escapes and 
// leaves double quotes around the string
$url = json_encode($url);
$msg = json_encode($msg);

echo <<<HTML
<html>
	<body>
		<script type="text/javascript">
			window.parent.CKEDITOR.tools.callFunction("$callback", $url, $msg);
		</script>
	</body>
</html>
HTML;
