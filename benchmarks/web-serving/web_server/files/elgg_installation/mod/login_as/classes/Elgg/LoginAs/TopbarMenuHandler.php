<?php

namespace Elgg\LoginAs;

use Elgg\Hook;
use ElggMenuItem;

class TopbarMenuHandler {

	/**
	 * Add a menu item to the topbar menu for logging out of an account
	 *
	 * @param $hook \Elgg\Hook Hook
	 * @return ElggMenuItem[]
	 */
	public function __invoke(Hook $hook) {

		$session = elgg_get_session();

		$original_user_guid = $session->get('login_as_original_user_guid');

		// short circuit view if not logged in as someone else.
		if (!$original_user_guid) {
			return;
		}

		$title = elgg_echo('login_as:return_to_user', [
			elgg_get_logged_in_user_entity()->username,
			get_entity($original_user_guid)->username
		]);

		$html = elgg_view('login_as/topbar_return', [
			'user_guid' => $original_user_guid,
		]);

		$menu = $hook->getValue();
		$menu[] = ElggMenuItem::factory([
			'name' => 'login_as_return',
			'text' => $html,
			'href' => 'action/logout_as',
			'is_action' => true,
			'title' => $title,
			'link_class' => 'login-as-topbar',
			'priority' => 700,
		]);

		return $menu;
	}

}
