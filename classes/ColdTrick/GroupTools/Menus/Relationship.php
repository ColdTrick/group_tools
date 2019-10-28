<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

class Relationship {

	/**
	 * Change the decline membership request button to a form with motivation for declining
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:relationship'
	 *
	 * @return void|MenuItems
	 */
	public static function groupDeclineMembershipReason(\Elgg\Hook $hook) {
		
		$relationship = $hook->getParam('relationship');
		if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'membership_request') {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		if (!$result->has('reject')) {
			return;
		}
		
		$user = get_entity($relationship->guid_one);
		$group = elgg_call(ELGG_IGNORE_ACCESS, function() use ($relationship) {
			return get_entity($relationship->guid_two);
		});
		if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if ($page_owner->guid !== $group->guid || !$group->canEdit()) {
			return;
		}
		
		/* @var $reject \ElggMenuItem */
		$reject = $result->get('reject');
		$reject->setHref(elgg_http_add_url_query_elements('ajax/form/groups/killrequest', [
			'relationship_id' => $relationship->id,
		]));
		$reject->setConfirmText(false);
		$reject->addLinkClass('elgg-lightbox');
		
		return $result;
	}
}
