<?php
/**
 * Invite users to join a group
 */

use Symfony\Component\HttpFoundation\File\UploadedFile;

$user_guids = (array) get_input('user_guid');
$user_guids = array_filter($user_guids);

$adding = false;
if (elgg_is_admin_logged_in()) {
	// add all users?
	if (get_input('all_users') === 'yes') {
		// this could take a while
		set_time_limit(0);
		
		// prepare a batch for memory saving
		$user_guids = elgg_get_entities([
			'type' => 'user',
			'limit' => false,
			'callback' => function ($row) {
				return (int) $row->guid;
			},
			'batch' => true,
		]);
	}
	
	// add users directly?
	$adding = (bool) get_input('submit');
}

$group_guid = (int) get_input('group_guid');
$text = get_input('comment', '');

$emails = (array) get_input('user_guid_email');
$emails = array_filter($emails);

$csv = elgg_get_uploaded_file('csv');

$resend = false;
if (get_input('resend') === 'yes') {
	$resend = true;
}

$group = get_entity($group_guid);
if (!$group instanceof \ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (empty($user_guids) && empty($emails) && empty($csv)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// counters
$already_invited = 0;
$invited = 0;
$member = 0;
$join = 0;

// show hidden (unvalidated) users
elgg_call(ELGG_SHOW_DISABLED_ENTITIES, function() use ($text, $group, $user_guids, $adding, $emails, $csv, $resend, &$already_invited, &$invited, &$member, &$join) {
	// invite existing users
	if (!empty($user_guids)) {
		if (!$adding) {
			// invite users
			foreach ($user_guids as $u_id) {
				$user = get_user((int) $u_id);
				if (empty($user)) {
					continue;
				}
				
				if ($group->isMember($user)) {
					$member++;
					continue;
				}
				
				if ($group->hasRelationship($user->guid, 'invited') && !$resend) {
					// user was already invited
					$already_invited++;
					continue;
				}
				
				if (group_tools_invite_user($group, $user, $text, $resend)) {
					$invited++;
				}
			}
		} else {
			// add users directly
			foreach ($user_guids as $u_id) {
				$user = get_user((int) $u_id);
				if (empty($user)) {
					continue;
				}
				
				if ($group->isMember($user)) {
					$member++;
					continue;
				}
				
				if (group_tools_add_user($group, $user, $text)) {
					$join++;
				}
			}
		}
	}
	
	// Invite member by e-mail address
	if (!empty($emails)) {
		foreach ($emails as $email) {
			$invite_result = group_tools_invite_email($group, $email, $text, $resend);
			if ($invite_result === true) {
				$invited++;
			} elseif ($invite_result === null) {
				$already_invited++;
			}
		}
	}
	
	// invite from csv
	if ($csv instanceof UploadedFile) {
		// this could take a while
		set_time_limit(0);
		
		$fh = $csv->openFile('r');
		while (!$fh->eof()) {
			/*
			 * data structure
			 * data[0] => e-mail address
			 */
			$data = $fh->fgetcsv(';');
			
			$email = '';
			if (isset($data[0])) {
				$email = trim($data[0]);
			}
			
			if (empty($email) || !elgg_is_valid_email($email)) {
				continue;
			}
			
			$user = elgg_get_user_by_email($email);
			if ($user instanceof \ElggUser) {
				// found a user with this email on the site, so invite (or add)
				if ($group->isMember($user)) {
					$member++;
					continue;
				}
				
				if ($adding) {
					if (group_tools_add_user($group, $user, $text)) {
						$join++;
					}
					
					continue;
				}
				
				if ($group->hasRelationship($user->guid, 'invited') && !$resend) {
					// user was already invited
					$already_invited++;
					continue;
				}
				
				// invite user
				if (group_tools_invite_user($group, $user, $text, $resend)) {
					$invited++;
				}
			} else {
				// user not found so invite based on email address
				$invite_result = group_tools_invite_email($group, $email, $text, $resend);
				if ($invite_result === true) {
					$invited++;
				} elseif ($invite_result === null) {
					$already_invited++;
				}
			}
		}
	}
});

// which message to show
if (!empty($invited) || !empty($join)) {
	if (!$adding) {
		return elgg_ok_response('', elgg_echo('group_tools:action:invite:success:invite', [$invited, $already_invited, $member]));
	} else {
		return elgg_ok_response('', elgg_echo('group_tools:action:invite:success:add', [$join, $already_invited, $member]));
	}
} else {
	if (!$adding) {
		return elgg_error_response(elgg_echo('group_tools:action:invite:error:invite', [$already_invited, $member]));
	} else {
		return elgg_error_response(elgg_echo('group_tools:action:invite:error:add', [$already_invited, $member]));
	}
}
