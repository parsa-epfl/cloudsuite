<?php

namespace hypeJunction\Wall;

use ElggBatch;

$ia = elgg_set_ignore_access(true);

/**
 * Wall owner should become the container of the wall post
 * wall_owner relationship should go away
 */
$wall_posts = new ElggBatch('elgg_get_entities_from_relationship', array(
	'types' => 'object',
	'subtypes' => 'hjwall',
	'relationship' => 'wall_owner',
	'limit' => false
		));

foreach ($wall_posts as $wall_post) {
	$relationships = get_entity_relationships($wall_post->guid, true);
	foreach ($relationships as $relationship) {
		if ($relationship->relationship !== 'wall_owner') {
			continue;
		}
		if ($relationship->guid_one !== $wall_post->container_guid) {
			$wall_post->container_guid = $relationship->guid_one;
			if ($wall_post->save()) {
				$relationship->delete();
			}
		}
	}
}

/**
 * Convert attachment metadata to 'attached' relationship for entities
 * and 'html' metadata for the rest
 */
$wall_posts = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => 'object',
	'subtypes' => 'hjwall',
	'metadata_names' => 'attachment',
	'limit' => false
		));

foreach ($wall_posts as $wall_post) {
	$attachment = $wall_post->attachment;
	if (is_numeric($attachment) && ($attached_entity = get_entity($attachment))) {
		add_entity_relationship($attached_entity->guid, 'attached', $wall_post->guid);
	} else {
		$wall_post->html = $attachment;
	}
	unset($wall_post->attachment);
}

/**
 * Convert 'hjfile' to 'file'
 */
$subtypeIdFrom = add_subtype('object', 'hjfile');
$subtypeIdTo = add_subtype('object', 'file');

$dbprefix = elgg_get_config('dbprefix');
$query = "	UPDATE {$dbprefix}entities e
				JOIN {$dbprefix}metadata md ON md.entity_guid = e.guid
				JOIN {$dbprefix}metastrings msn ON msn.id = md.name_id
				JOIN {$dbprefix}metastrings msv ON msv.id = md.value_id
				SET e.subtype = $subtypeIdTo
				WHERE e.subtype = $subtypeIdFrom AND msn.string = 'handler' AND msv.string = 'hjwall' ";

$wall_files = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => 'object',
	'subtypes' => 'file',
	'metadata_name_value_pairs' => array(
		'name' => 'handler', 'value' => 'hjwall'
	),
	'limit' => false
		));

foreach ($wall_files as $file) {
	
	// Regenerate icons
	if ($file->simpletype == 'image') {
		$thumb_sizes = array(
			'tiny' => 16,
			'small' => 25,
			'medium' => 40,
			'large' => 100,
			'preview' => 250,
			'master' => 500,
			'full' => 1024,
		);

		foreach ($thumb_sizes as $ths => $dim) {
			$thumb = new ElggFile();
			$thumb->setFilenameOnFilestore("hjfile/{$file->getGUID()}{$ths}.jpg");
			unlink($thumb->getFilenameOnFilestore());
		}

		$file->icontime = time();

		$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
		if ($thumbnail) {
			$thumb = new ElggFile();
			$thumb->setFilename($prefix . "thumb" . $filestorename);
			$thumb->open("write");
			$thumb->write($thumbnail);
			$thumb->close();

			$file->thumbnail = $prefix . "thumb" . $filestorename;
			unset($thumbnail);
		}

		$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
		if ($thumbsmall) {
			$thumb->setFilename($prefix . "smallthumb" . $filestorename);
			$thumb->open("write");
			$thumb->write($thumbsmall);
			$thumb->close();
			$file->smallthumb = $prefix . "smallthumb" . $filestorename;
			unset($thumbsmall);
		}

		$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
		if ($thumblarge) {
			$thumb->setFilename($prefix . "largethumb" . $filestorename);
			$thumb->open("write");
			$thumb->write($thumblarge);
			$thumb->close();
			$file->largethumb = $prefix . "largethumb" . $filestorename;
			unset($thumblarge);
		}
	}
}

/**
 * Set file folder guids as plugin setting
 */
$folders = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => 'object',
	'subtypes' => 'hjfilefolder',
	'metadata_name_value_pairs' => array(
		'name' => 'handler', 'value' => 'hjwall'
	),
	'limit' => false
		));

foreach ($folders as $folder) {
	elgg_set_plugin_user_setting('wall_collection', $folder->guid, $folder->owner_guid, PLUGIN_ID);
}

/**
 * Convert 'hjfilefolder' to 'wall_collection'
 */
$subtypeIdFrom = add_subtype('object', 'hjfilefolder');
$subtypeIdTo = add_subtype('object', 'wallcollection');

$dbprefix = elgg_get_config('dbprefix');
$query = "	UPDATE {$dbprefix}entities e
				JOIN {$dbprefix}metadata md ON md.entity_guid = e.guid
				JOIN {$dbprefix}metastrings msn ON msn.id = md.name_id
				JOIN {$dbprefix}metastrings msv ON msv.id = md.value_id
				SET e.subtype = $subtypeIdTo
				WHERE e.subtype = $subtypeIdFrom AND msn.string = 'handler' AND msv.string = 'hjwall' ";


elgg_set_ignore_access($ia);
