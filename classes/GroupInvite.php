<?php

use Elgg\Exceptions\ExceptionInterface;
use Elgg\Groups\Notifications\InviteMembershipEventHandler;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Temporary object to store and handle group invites
 *
 * @property bool   $add_users          should we add or invite users
 * @property string $additional_comment additional text in the invitation
 * @property bool   $all_users          process all site users (only for admins)
 * @property array  $emails             e-mail addresses to process
 * @property bool   $has_csv            is there a CSV with e-mail addresses to process
 * @property bool   $locked             currently processing
 * @property bool   $resend_invitation  resend the invitation to already invited users
 * @property array  $users              user guids to invite
 */
class GroupInvite extends \ElggObject {
	
	public const SUBTYPE = 'group_tools_group_invite';
	
	protected array $result = [
		'already_invited' => 0,
		'invited' => 0,
		'member' => 0,
		'joined' => 0,
	];
	
	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
		$this->attributes['access_id'] = ACCESS_PRIVATE;
		
		$this->add_users = false;
		$this->all_users = false;
		$this->has_csv = false;
		$this->resend_invitation = false;
	}
	
	/**
	 * {@inheritdoc}
	 *
	 * @return \ElggGroup|null
	 */
	public function getContainerEntity(): ?\ElggGroup {
		$container = parent::getContainerEntity();
		
		return $container instanceof \ElggGroup ? $container : null;
	}
	
	/**
	 * Store the CSV with user information
	 *
	 * @param UploadedFile|null $csv uploaded CSV
	 *
	 * @return void
	 */
	public function saveCsv(?UploadedFile $csv): void {
		if (!$csv instanceof UploadedFile) {
			return;
		}
		
		$this->save();
		
		$file = $this->getCsvFile();
		
		try {
			$file->open('write');
			$file->write($csv->getContent());
			$file->close();
			
			$this->has_csv = true;
		} catch (ExceptionInterface $e) {
			// do nothing
		}
	}
	
	/**
	 * Should we offload the processing of the invites
	 *
	 * We do this with large datasets to prevent timeouts for the user
	 *
	 * @return bool
	 */
	public function shouldBeOffloaded(): bool {
		if ($this->all_users || $this->has_csv) {
			return true;
		}
		
		$count = 0;
		if (isset($this->users)) {
			$count += is_array($this->users) ? count($this->users) : 1;
		}
		
		if (isset($this->emails)) {
			$count += is_array($this->emails) ? count($this->emails) : 1;
		}
		
		return $count >= 20;
	}
	
	/**
	 * Process all the invitations
	 *
	 * @return array
	 */
	public function process(): array {
		if (!$this->getOwnerEntity() instanceof \ElggUser || !$this->getContainerEntity() instanceof \ElggGroup) {
			return $this->result;
		}
		
		$session_manager = elgg()->session_manager;
		$backup = $session_manager->getLoggedInUser();
		
		try {
			// unregister default invite notification handler
			elgg_unregister_notification_event('relationship', 'invited', 'create:after', InviteMembershipEventHandler::class);
			
			// set correct logged-in user
			$session_manager->setLoggedInUser($this->getOwnerEntity());
			
			// processing could take some time
			set_time_limit(0);
			
			elgg_call(ELGG_SHOW_DISABLED_ENTITIES, function() {
				$this->processUsers();
				$this->processEmails();
				$this->processCsv();
			});
		} catch (\Throwable $t) {
			elgg_log($t->getMessage(), LogLevel::ERROR);
		}
		
		// re-register default invite notification handler
		elgg_register_notification_event('relationship', 'invited', 'create:after', InviteMembershipEventHandler::class);
		
		if ($backup instanceof \ElggUser) {
			$session_manager->setLoggedInUser($backup);
		} else {
			$session_manager->removeLoggedInUser();
		}
		
		return $this->result;
	}
	
	/**
	 * Send a status notification to the owner that the invites have been processed
	 *
	 * @return void
	 */
	public function sendStatusNotification(): void {
		$owner = $this->getOwnerEntity();
		if (!$owner instanceof \ElggUser) {
			return;
		}
		
		$owner->notify('invite_results', $this->getContainerEntity(), [
			'results' => $this->result,
			'adding' => $this->add_users,
		]);
	}
	
	/**
	 * Send a notification to the owner that there was a failure during processing
	 *
	 * @return void
	 */
	public function sendFailureNotification(): void {
		$owner = $this->getOwnerEntity();
		if (!$owner instanceof \ElggUser) {
			return;
		}
		
		$owner->notify('invite_failure', $this->getContainerEntity());
	}
	
	/**
	 * Process the users
	 *
	 * @return void
	 */
	protected function processUsers(): void {
		if ($this->all_users) {
			/** @var \ElggBatch $users */
			$users = elgg_get_entities([
				'type' => 'user',
				'limit' => false,
				'batch' => true,
			]);
		} elseif (!empty($this->users)) {
			/** @var \ElggBatch $users */
			$users = elgg_get_entities([
				'type' => 'user',
				'guids' => $this->users,
				'limit' => false,
				'batch' => true,
			]);
		} else {
			return;
		}
		
		/** @var \ElggUser $user */
		foreach ($users as $user) {
			$this->processUser($user);
		}
	}
	
	/**
	 * Process the email addresses
	 *
	 * @return void
	 */
	protected function processEmails(): void {
		if (empty($this->emails)) {
			return;
		}
		
		$emails = (array) $this->emails;
		foreach ($emails as $email) {
			$invite_result = $this->sendEmailInvite($email);
			if ($invite_result === true) {
				$this->result['invited']++;
			} elseif ($invite_result === null) {
				$this->result['already_invited']++;
			}
		}
	}
	
	/**
	 * Process the CSV contents
	 *
	 * @return void
	 */
	protected function processCsv(): void {
		if (!$this->has_csv) {
			return;
		}
		
		$csv = $this->getCsvFile();
		if (!$csv->exists()) {
			// how did we get here
			return;
		}
		
		try {
			$fh = $csv->open('read');
			if (!is_resource($fh)) {
				return;
			}
		} catch (ExceptionInterface $e) {
			return;
		}
		
		/*
		 * data structure
		 * data[0] => e-mail address
		 */
		while ($data = fgetcsv($fh, null, ';', '"', '\\')) {
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
				$this->processUser($user);
			} else {
				// user not found so invite based on email address
				$invite_result = $this->sendEmailInvite($email);
				if ($invite_result === true) {
					$this->result['invited']++;
				} elseif ($invite_result === null) {
					$this->result['already_invited']++;
				}
			}
		}
		
		$csv->close();
	}
	
	/**
	 * Get the file object to store/read csv contents from
	 *
	 * @return \ElggFile
	 */
	protected function getCsvFile(): \ElggFile {
		$file = new \ElggFile();
		$file->owner_guid = $this->guid;
		$file->setFilename('users.csv');
		
		return $file;
	}
	
	/**
	 * Process a single user
	 *
	 * @param \ElggUser $user user
	 *
	 * @return void
	 */
	protected function processUser(\ElggUser $user): void {
		$group = $this->getContainerEntity();
		if ($group->isMember($user)) {
			$this->result['member']++;
			return;
		}
		
		if ($this->add_users) {
			if ($this->addUserToGroup($user)) {
				$this->result['joined']++;
			}
			
			return;
		}
		
		if ($group->hasRelationship($user->guid, 'invited') && !$this->resend_invitation) {
			// user was already invited
			$this->result['already_invited']++;
			return;
		}
		
		if ($this->inviteUser($user)) {
			$this->result['invited']++;
		}
	}
	
	/**
	 * Add a user to the group
	 *
	 * @param \ElggUser $user user to add
	 *
	 * @return bool
	 */
	protected function addUserToGroup(\ElggUser $user): bool {
		return elgg_call(ELGG_IGNORE_ACCESS, function() use ($user) {
			if (!$this->getContainerEntity()->join($user)) {
				return false;
			}
			
			// notify user
			$user->notify('add_user', $this->getContainerEntity(), [
				'text' => $this->additional_comment,
			]);
			
			return true;
		});
	}
	
	/**
	 * Invite a user to the group
	 *
	 * @param \ElggUser $user user
	 *
	 * @return bool
	 */
	protected function inviteUser(\ElggUser $user): bool {
		// Create relationship
		$relationship = $this->getContainerEntity()->addRelationship($user->guid, 'invited');
		if (!$relationship && !$this->resend_invitation) {
			return false;
		}
		
		$user->notify('invite', $this->getContainerEntity(), [
			'invite_text' => $this->additional_comment,
		]);
		
		return true;
	}
	
	/**
	 * Send out an invitation for the group to an e-mail address
	 *
	 * @param string $address e-mail address
	 *
	 * @return bool|null
	 */
	protected function sendEmailInvite(string $address): ?bool {
		$loggedin_user = elgg_get_logged_in_user_entity();
		if (!elgg_is_valid_email($address) || empty($loggedin_user)) {
			return false;
		}
		
		$group = $this->getContainerEntity();
		
		// generate invite code
		$invite_code = elgg_build_hmac([
			strtolower($address),
			$group->guid,
		])->getToken();
		
		$found_group = group_tools_check_group_email_invitation($invite_code, $group->guid);
		if (!empty($found_group) && empty($this->resend_invitation)) {
			return null;
		}
		
		if (empty($found_group)) {
			// register invite with group
			$group->annotate('email_invitation', "{$invite_code}|{$address}", ACCESS_LOGGED_IN, $group->guid);
		}
		
		// make site email
		$site = elgg_get_site_entity();
		$email = \Elgg\Email::factory([
			'from' => $site,
			'to' => $address,
			'subject' => elgg_echo('group_tools:groups:invite:email:subject', [$group->getDisplayName()]),
			'body' => elgg_echo('group_tools:groups:invite:email:body', [
				$loggedin_user->getDisplayName(),
				$group->getDisplayName(),
				$site->getDisplayName(),
				$this->additional_comment,
				$site->getDisplayName(),
				elgg_generate_url('account:register', [
					'group_invitecode' => $invite_code,
				]),
				$invite_code,
			]),
			'params' => [
				'group' => $group,
				'inviter' => $loggedin_user,
				'invitee' => $address,
			],
		]);
		
		$body = elgg_trigger_event_results('invite_notification', 'group_tools', $email->getParams(), $email->getBody());
		$email->setBody($body);
		
		return elgg_send_email($email);
	}
}
