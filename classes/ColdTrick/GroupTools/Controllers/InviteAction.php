<?php

namespace ColdTrick\GroupTools\Controllers;

use Elgg\Controllers\GenericAction;
use Elgg\Exceptions\Http\BadRequestException;
use Elgg\Exceptions\Http\EntityPermissionsException;
use Elgg\Exceptions\HttpException;
use Elgg\Http\OkResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle group invites
 */
class InviteAction extends GenericAction {
	
	protected bool $adding = false;
	
	protected bool $all_users = false;
	
	protected ?string $additional_comment = null;
	
	protected ?UploadedFile $csv = null;
	
	protected array $emails = [];
	
	protected ?\ElggGroup $group = null;
	
	protected ?\GroupInvite $group_invite = null;
	
	protected bool $resend = false;
	
	protected ?array $user_guids = null;
	
	protected ?array $result = null;
	
	/**
	 * {@inheritdoc}
	 */
	protected function validate(): void {
		$group_guid = (int) get_input('group_guid');
		$group = get_entity($group_guid);
		if (!$group instanceof \ElggGroup || !$group->canEdit()) {
			throw new EntityPermissionsException(elgg_echo('actionunauthorized'));
		}
		
		$this->group = $group;
		
		$this->user_guids = array_filter((array) get_input('user_guid'));
		$this->emails = array_filter((array) get_input('user_guid_email'));
		$this->csv = elgg_get_uploaded_file('csv');
		
		if (elgg_is_admin_logged_in()) {
			if (get_input('all_users') === 'yes') {
				$this->all_users = true;
				$this->user_guids = null;
			}
			
			$this->adding = (bool) get_input('submit');
		}
		
		if (empty($this->user_guids) && empty($this->emails) && empty($this->csv) && !$this->all_users) {
			throw new BadRequestException(elgg_echo('error:missing_data'));
		}
		
		$this->resend = (bool) get_input('resend');
		$this->additional_comment = (string) get_input('comment');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function executeBefore(): void {
		$invite = new \GroupInvite();
		$invite->container_guid = $this->group->guid;
		
		$invite->add_users = $this->adding;
		$invite->all_users = $this->all_users;
		$invite->users = $this->user_guids;
		$invite->emails = $this->emails;
		$invite->resend_invitation = $this->resend;
		$invite->additional_comment = $this->additional_comment;
		
		$invite->saveCsv($this->csv);
		
		$this->group_invite = $invite;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function execute(): void {
		if ($this->group_invite->shouldBeOffloaded()) {
			$this->group_invite->save();
		} else {
			$this->result = $this->group_invite->process();
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function success(): OkResponse {
		if ($this->group_invite->shouldBeOffloaded()) {
			$message = elgg_echo('group_tools:action:invite:success:offloaded');
		} elseif (!empty($this->result)) {
			$already_invited = (int) elgg_extract('already_invited', $this->result);
			$invited = (int) elgg_extract('invited', $this->result);
			$member = (int) elgg_extract('member', $this->result);
			$joined = (int) elgg_extract('joined', $this->result);
			
			if (!empty($invited) || !empty($join)) {
				if (!$this->adding) {
					$message = elgg_echo('group_tools:action:invite:success:invite', [$invited, $already_invited, $member]);
				} else {
					$message = elgg_echo('group_tools:action:invite:success:add', [$joined, $already_invited, $member]);
				}
			} else {
				if (!$this->adding) {
					throw new HttpException(elgg_echo('group_tools:action:invite:error:invite', [$already_invited, $member]));
				} else {
					throw new HttpException(elgg_echo('group_tools:action:invite:error:add', [$already_invited, $member]));
				}
			}
		} else {
			throw new HttpException(elgg_echo('group_tools:action:invite:error:unknown'));
		}
		
		return elgg_ok_response('', $message);
	}
}
