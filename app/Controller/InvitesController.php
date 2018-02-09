<?php
class InvitesController extends AppController {
	public $name = 'Invites';
	
	public function add() {
		$platform_id = $this->user['User']['platform_id'];
		$people = $this->OpenSocial->get("people/$platform_id/@self", array('fields' => 'id,isVerified'));
		if (empty($people->isVerified)) {
			$this->set('result', true);
			return;
		}
		$ids = explode(',', $this->request->data['ids']);
		$result = false;
		if ($ids) {
			$this->Invite->begin();
			$result = true;
			foreach ($ids as $id) {
				if (!$this->Invite->add($this->user['User']['id'], $id)) {
					$result = false;
					break;
				}
			}
			if ($result) {
				$this->Invite->commit();
			} else {
				$this->Invite->rollback();
			}
		}
		$this->set('result', $result);
	}
}
