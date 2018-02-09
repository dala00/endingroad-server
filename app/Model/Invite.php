<?php
class Invite extends AppModel {
	public $belongsTo = array('User');
	
	public function add($user_id, $invited_platform_id) {
		$sql = "INSERT INTO invites(user_id, invited_platform_id, created, modified) "
			. " VALUES($user_id, $invited_platform_id, NOW(), NOW()) "
			. " ON DUPLICATE KEY UPDATE created = created";
		return $this->query($sql) !== false;
	}
	
	public function close($invited) {
		$find = array(
			'conditions' => array(
				'Invite.invited_platform_id' => $invited['User']['platform_id'],
			),
		);
		if ($invites = $this->find('all', $find)) {
			App::uses('Message', 'Model');
			$mMessage = new Message();
			$closedExists = false;
			foreach ($invites as $invite) {
				if ($invite['Invite']['closed']) {
					$closedExists = true;
				} else {
					$this->set(array(
						'id' => $invite['Invite']['id'],
						'closed' => true,
					));
					if ($this->save()) {
						$mMessage->create(array(
							'user_id' => $invite['Invite']['user_id'],
							'name' => '招待成功報酬',
							'name' => '招待成功報酬です。',
							'item_id' => 2,
							'number' => 1,
						));
						if (!$mMessage->save()) {
							return false;
						}
					} else {
						return false;
					}
				}
			}
			if (!$closedExists) {
				$mMessage->create(array(
					'user_id' => $invited['User']['id'],
					'name' => '招待された報酬',
					'name' => '招待された報酬です。',
					'item_id' => 2,
					'number' => 1,
				));
				if (!$mMessage->save()) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	public function findCountForInviter($user_id) {
		$find = array(
			'conditions' => array(
				'Invite.user_id' => $user_id,
				'Invite.closed' => 1,
			),
		);
		return $this->find('count', $find);
	}
}
