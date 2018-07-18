<?php

if (!isset($vars['entity']->num_display)) {
	$vars['entity']->num_display = 4;
}

if (!isset($vars['entity']->show_add_form)) {
	$vars['entity']->show_add_form = false;
}

$params = array(
	'name' => 'params[num_display]',
	'value' => $vars['entity']->num_display,
	'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
);
$dropdown = elgg_view('input/dropdown', $params);
?>
<div>
	<?php echo elgg_echo('wall:numbertodisplay'); ?>:
	<?php echo $dropdown; ?>
</div>
<div>
	<?php echo elgg_echo('wall:widget:showaddform'); ?>:
	<?php
	echo elgg_view('input/dropdown', array(
		'name' => 'params[show_add_form]',
		'value' => $vars['entity']->show_add_form,
		'options_values' => array(
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes')
		)
	));
	?>
</div>