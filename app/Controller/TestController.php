<?php
class TestController extends AppController {
	public $name = 'Test';
	public $uses = null;
	
	public function admin_analyze() {
		$this->loadModel('Payment');
		$find = array(
			'conditions' => array(
				'Payment.status' => 1,
				'Payment.created >=' => date('Y-m-d 00:00:00', time() - 3 * 24 * 60 * 60),
			),
			'order' => 'Payment.created DESC',
		);
		$payments = $this->Payment->find('all', $find);
		$this->set('payments', $payments);
		
		$this->loadModel('Invite');
		$inviteAllCount = $this->Invite->find('count');
		$find = array('conditions' => array('Invite.closed' => 1));
		$invitedCount = $this->Invite->find('count', $find);
		$this->set('inviteAllCount', $inviteAllCount);
		$this->set('invitedCount', $invitedCount);
	}
}
