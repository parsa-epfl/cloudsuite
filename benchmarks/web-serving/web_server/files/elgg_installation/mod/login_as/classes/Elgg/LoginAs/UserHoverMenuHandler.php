<?php

namespace Elgg\LoginAs;

use Elgg\Hook;
use ElggMenuItem;
use ElggUser;

class UserHoverMenuHandler {

	/**
	 * Add Login As to user hover menu for admins
	 *
	 * @param $hook \Elgg\Hook $hook Hook
	 * @return ElggMenuItem[]
	 */
	public function __invoke(Hook $hook) {

		$user = $hook->getEntityParam();
		$logged_in_user = elgg_get_logged_in_user_entity();

		if (!$user instanceof ElggUser) {
			return;
		}

		if ($user->isBanned()) {
			// banned users are unable to login
			return;
		}

		if (!$logged_in_user || !$logged_in_user->isAdmin()) {
			return;
		}

		// Don't show menu on self.
		if ($logged_in_user == $user) {
			return;
		}

		$menu = $hook->getValue();
		$menu[] = ElggMenuItem::factory([
			'name' => 'login_as',
			'icon' => 'sign-in',
			'text' => elgg_echo('login_as:login_as'),
			'href' => elgg_http_add_url_query_elements('action/login_as', [
				'user_guid' => $user->guid,
			]),
			'is_action' => true,
			'section' => 'admin',
		]);

		return $menu;
	}

}
