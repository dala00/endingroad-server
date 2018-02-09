<?php
class OauthController extends AppController {
	public $uses = null;
	
	public function request_temporary_credential() {
		$this->Session->write('userId', $this->request->data['userId']);
		$params = array(
			'oauth_callback' => 'oob',
		);
		$result = $this->OpenSocial->post(OAUTH_URL . 'request_temporary_credential', null, $params);
		$this->Session->write('temporary_credential', $result);
		echo $result['oauth_token'];
		exit;
	}
	
	public function request_token_credential() {
		$params = array(
			'oauth_verifier' => $this->request->data['verifier'],
		);
		$result = $this->OpenSocial->post(OAUTH_URL . 'request_token', null, $params);
		$this->Session->write('token', $result);
		echo 'OK';
		exit;
	}
}
