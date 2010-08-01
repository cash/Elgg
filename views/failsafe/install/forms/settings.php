<?php

$variables = $vars['variables'];

$form_body = '';
foreach ($variables as $variable) {
	$label = elgg_echo("installation:$variable");
	$form_body .= "<label>$label</label>";
	$form_body .= elgg_view('input/text', array('internalname' => $variable));
}

$form_body .= elgg_view('input/submit', array('value' => 'Next'));

// @todo bug in current_page_url() with :8080 sites
//$url = current_page_url();
$url = '/install.php?step=settings';

$params = array(
	'body' => $form_body,
	'action' => $url,
	'disable_security' => TRUE,
);
echo elgg_view('input/form', $params);
