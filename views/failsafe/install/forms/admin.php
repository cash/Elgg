<?php


$variables = $vars['variables'];

$form_body = '';
foreach ($variables as $field => $params) {
	$label = elgg_echo("installation:admin:label:$field");
	$help = elgg_echo("installation:admin:help:$field");
	$params['internalname'] = $field;

	$form_body .= '<p>';
	$form_body .= "<label>$label</label>";
	$form_body .= elgg_view("input/{$params['type']}", $params);
	$form_body .= "<span class=\"install_help\">$help</span>";
	$form_body .= '</p>';
}

$form_body .= elgg_view('input/submit', array('value' => 'Next'));

// @todo bug in current_page_url() with :8080 sites
//$url = current_page_url();
$url = '/install.php?step=admin';

$params = array(
	'body' => $form_body,
	'action' => $url,
	'disable_security' => TRUE,
);
echo elgg_view('input/form', $params);
