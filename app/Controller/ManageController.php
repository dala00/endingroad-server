<?php
class ManageController extends AppController {
	public $name = 'Manage';
	public $uses = null;
	
	public function admin_message() {
		$this->loadModel('User');
		$find = array(
			'fields' => array('User.platform_id'),
//			'conditions' => array('User.id' => 1),
		);
		$users = $this->User->find('all', $find);
		$ids = array();
		foreach ($users as $user) {
			$ids[] = $user['User']['platform_id'];
		}
		$this->set('ids', $ids);
	}
	
	public function admin_ajax_message() {
		$ids = explode(',', $this->request->data['ids']);
		$headers = array('Content-Type' => 'application/json; charset=utf8');
		foreach ($ids as $id) {
			$params = array(
				'type' => 'NOTIFICATION',
				'title' => $this->request->data['message'],
				'urls' => array(
					array(
						'value' => 'http://',
						'type' => 'canvas',
					),
				),
				'recipients' => array($id),
			);
			$json = $this->jsonEncode($params);
			$this->OpenSocial->post('messages/@me/@self/@outbox', $json, array(), $headers);
		}
		exit;
	}
}