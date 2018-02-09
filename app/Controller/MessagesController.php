<?php
class MessagesController extends AppController {
	public $name = 'Messages';
	public $uses = array('Message', 'UsersMessage');
	
	public function index() {
		$this->set('messages', $this->Message->findLatest($this->user['User']['id']));
	}
	
	public function receive() {
		$result = false;
		if ($message = $this->Message->read(null, $this->request->data['id'])) {
			$save = array(
				'user_id' => $this->user['User']['id'],
				'message_id' => $message['Message']['id'],
			);
			$this->Message->begin();
			if ($this->UsersMessage->save($save)) {
				if ($message['Message']['item_id']) {
					$number = $message['Message']['number'] ? $message['Message']['number'] : 1;
					$this->loadModel('UsersItem');
					$result = $this->UsersItem->get($this->user['User']['id'], $message['Message']['item_id'], $number);
					if ($result) {
						$usersItems = $this->UsersItem->findForUser($this->user['User']['id'], $message['Message']['item_id']);
						$this->set('usersItems', $usersItems);
					}
				} else if ($message['Message']['gold']) {
					$result = $this->User->addGold($this->user['User']['id'], $message['Message']['gold']);
					$this->reloadUser();
				} else if ($message['Message']['stone']) {
					$result = $this->User->addStone($this->user['User']['id'], $message['Message']['stone']);
					$this->reloadUser();
				} else {
					$result = true;
				}
			}
		}
		if ($result) {
			$this->Message->commit();
		} else {
			$this->Message->rollback();
		}
		$this->set('result', $result);
	}
}
