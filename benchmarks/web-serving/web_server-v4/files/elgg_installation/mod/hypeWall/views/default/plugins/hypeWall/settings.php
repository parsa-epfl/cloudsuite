<?php

namespace hypeJunction\Wall;

$entity = elgg_extract('entity', $vars);

echo '<h3>' . elgg_echo('wall:settings:model') . '</h3>';

if (elgg_is_active_plugin('thewire')) {

	echo '<div>';
	echo '<label>' . elgg_echo('wall:settings:model:select') . '</label>';
	echo elgg_view('input/dropdown', array(
		'name' => 'params[model]',
		'value' => $entity->model,
		'options_values' => array(
			WALL_MODEL_WALL => elgg_echo('wall:settings:model:wall'),
			WALL_MODEL_WIRE => elgg_echo('wall:settings:model:wire'),
		)
	));
	echo '</div>';
}

echo '<div>';
echo '<label>' . elgg_echo('wall:settings:model:character_limit') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[character_limit]',
	'value' => $entity->character_limit,
));
echo '</div>';

echo '<h3>' . elgg_echo('wall:settings:form') . '</h3>';

echo '<div>';
echo '<label>' . elgg_echo('wall:settings:status') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[status]',
	'value' => $entity->status,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('wall:settings:url') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[url]',
	'value' => $entity->url,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('wall:settings:photo') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[photo]',
	'value' => $entity->photo,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

//
//echo '<div>';
//echo '<label>' . elgg_echo('wall:settings:file') . '</label>';
//echo elgg_view('input/dropdown', array(
//	'name' => 'params[file]',
//	'value' => $entity->file,
//	'options_values' => array(
//		0 => elgg_echo('option:no'),
//		1 => elgg_echo('option:yes'),
//	)
//));
//echo '</div>';

if (elgg_is_active_plugin('elgg_tokeninput')) {
	echo '<div>';
	echo '<label>' . elgg_echo('wall:settings:content') . '</label>';
	echo elgg_view('input/dropdown', array(
		'name' => 'params[content]',
		'value' => $entity->content,
		'options_values' => array(
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		)
	));
	echo '</div>';
}

echo '<div>';
echo '<label>' . elgg_echo('wall:settings:default_form') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[default_form]',
	'value' => $entity->default_form,
	'options_values' => array(
		'status' => elgg_echo('wall:settings:status'),
		'url' => elgg_echo('wall:settings:url'),
		'photo' => elgg_echo('wall:settings:photo'),
		'content' => (elgg_is_active_plugin('elgg_tokeninput')) ? elgg_echo('wall:settings:content') : null,
	)
));
echo '</div>';

echo '<h3>' . elgg_echo('wall:settings:features') . '</h3>';

if (elgg_is_active_plugin('elgg_tokeninput')) {
	echo '<div>';
	echo '<label>' . elgg_echo('wall:settings:geopositioning') . '</label>';
	echo elgg_view('input/dropdown', array(
		'name' => 'params[geopositioning]',
		'value' => $entity->geopositioning,
		'options_values' => array(
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		)
	));
	echo '</div>';

	echo '<div>';
	echo '<label>' . elgg_echo('wall:settings:tag_friends') . '</label>';
	echo elgg_view('input/dropdown', array(
		'name' => 'params[tag_friends]',
		'value' => $entity->tag_friends,
		'options_values' => array(
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		)
	));
	echo '</div>';
}

echo '<div>';
echo '<label>' . elgg_echo('wall:settings:third_party_wall') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[third_party_wall]',
	'value' => $entity->third_party_wall,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('wall:settings:status_input_type') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[status_input_type]',
	'value' => $entity->status_input_type,
	'options_values' => array(
		'plaintext' => elgg_echo('wall:settings:status_input_type:plaintext'),
		'text' => elgg_echo('wall:settings:status_input_type:text'),
		'longtext' => elgg_echo('wall:settings:status_input_type:longtext'),
	)
));
echo '</div>';
