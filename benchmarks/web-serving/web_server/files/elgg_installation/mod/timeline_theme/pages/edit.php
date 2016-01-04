<?php

	gatekeeper();

	elgg_load_css("thickbox_css");
	elgg_load_css("farbtastic_css");

	elgg_load_js("thickbox_js");
	elgg_load_js("farbtastic_js");
	
	$group_guid = (int) get_input("group_guid");
	$group = get_entity($group_guid);

	if (!empty($group) && elgg_instanceof($group, "group")) {
		if(group_timeline_layout_allow($group) && $group->canEdit()) {
			// set context and page owner
			elgg_push_context("groups");
			elgg_set_page_owner_guid($group_guid);
			
			$title_text = elgg_echo("group_timeline_layout:edit:title");
	
			// make breadcrumb
			elgg_push_breadcrumb(elgg_echo("groups"), "groups/all");
			elgg_push_breadcrumb($group->name, $group->getURL());
			elgg_push_breadcrumb($title_text);
	
			$params = array(
				"filter" => "",
				"title" => $title_text
			);
	
			$layout = group_timeline_layout_get_layout($group);
	
			$params["content"] = elgg_view_form("group_timeline_layout/save",
									array("id" => "editForm", "enctype" => "multipart/form-data"),
									array("entity" => $group, "group_timeline_layout" => $layout)
								);
	
			$body = elgg_view_layout("content", $params);
	
			echo elgg_view_page($title_text, $body);
			
			// reset context
			elgg_pop_context();
		} else {
			forward(REFERER);
		}
	} else {
		forward(REFERER);
	}