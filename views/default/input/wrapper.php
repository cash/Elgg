<?php
/**
 * Creates a wrapper for an input field.
 * Wrapper, label, and input field are created by this view. Using it helps
 * to create consistent mark-up across forms. The view also uses the for
 * attribute for the label tag for accessibility.
 *
 * @uses $vars['type']        Input type: input/<type>
 * @uses $vars['label']       Label text
 * @uses $vars['label_first'] Is the label displayed before the input field
 *                            Default: true
 * @uses $vars['wrap']        Wrap the label and input field. Default: true
 * @uses $vars['wrap_class']  CSS class for the wrap element. Default: none
 *
 * All other options are passed to the input/<type> view
 */

$type = elgg_extract('type', $vars);
$label = (string)elgg_extract('label', $vars);
$label_first = elgg_extract('label_first', $vars, true);
$wrap = elgg_extract('wrap', $vars, true);
$wrap_class = elgg_extract('wrap_class', $vars);

unset($vars['type']);
unset($vars['label']);
unset($vars['label_first']);
unset($vars['wrap']);
unset($vars['wrap_class']);

// determine for/id pair
global $__elgg_input_counter;
if (isset($vars['id'])) {
	$for = $vars['id'];
} else {
	if (!isset($__elgg_input_counter)) {
		$__elgg_input_counter = 1;
	}

	$for = "elgg-input-$__elgg_input_counter";
	$vars['id'] = $for;
	$__elgg_input_counter++;
}

if ($label) {
	$label = "<label for=\"$for\">$label</label>";
}

$wrap_begin = $wrap_end = '';
if ($wrap) {
	$wrap_class = $wrap_class ? " class=\"$wrap_class\"" : "";
	$wrap_begin = "<div$wrap_class>";
	$wrap_end = '</div>';
}

echo $wrap_begin;
if ($label_first) {
	echo $label;
}
echo elgg_view("input/$type", $vars);
if (!$label_first) {
	echo $label;
}
echo $wrap_end;
