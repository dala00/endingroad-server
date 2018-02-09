<?php
class Message extends AppModel {
	
	public function findLatest($user_id) {
		$find = array(
			'fields' => array(
				'Message.*',
				'UsersMessage.*',
			),
			'joins' => array(
				'LEFT JOIN users_messages AS UsersMessage '
					. " ON UsersMessage.user_id = $user_id AND UsersMessage.message_id = Message.id"
			),
			'conditions' => array(
				'Message.user_id' => array(0, $user_id),
			),
			'order' => 'Message.created DESC',
			'limit' => 100,
		);
		$messages = $this->find('all', $find);
		foreach ($messages as $i => $message) {
			if ($message['UsersMessage']['id']) {
				$message['Message']['received'] = true;
			} else {
				$message['Message']['received'] = false;
			}
			$message = $message['Message'];
			$messages[$i] = $message;
		}
		return $messages;
	}
}
