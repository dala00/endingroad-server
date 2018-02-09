<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = array('OpenSocial', 'PaymentAction', 'Session');

	public $user = null;
	protected $stages = null;

	public function beforeFilter() {
		$myIP = '';

		$this->checkMaintenance();
		if (!empty($this->request->params['admin']) && $_SERVER['REMOTE_ADDR'] == $myIP) {
			return;
		}

		if ($this->request->params['controller'] == 'oauth') {
			return;
		}
		if (!$this->OpenSocial->checkRequest()) {
			$this->_404();
		}

		$platformId = $this->OpenSocial->getUserId();
		$this->loadModel('User');
		if ($this->user = $this->User->findByPlatformId($platformId)) {
			$this->loadModel('Stage');
			$this->stages = $this->Stage->findSimple('all');
			$this->stages = $this->Stage->initWeekdayEvent($this->stages);
			$this->user = $this->User->checkHp($this->user, $this->stages);
			$this->checkLoginBonus();
		}
		$this->set('userFound', $this->user ? true : false);
		if (!$this->user && $this->request->params['action'] != 'generate') {
			$this->beforeRender();
		}
	}

	protected function checkLoginBonus() {
		if ($this->user) {
			$today = date('Y-m-d');
			if ($this->user['User']['lastLoginDate'] != $today) {
				$save = array(
					'id' => $this->user['User']['id'],
					'lastLoginDate' => $today,
					'loginCount' => ($this->user['User']['loginCount'] + 1) % count($GLOBALS['LOGIN_BONUS']),
				);
				$day = $this->user['User']['loginCount'] + 1;
				$message = array(
					'user_id' => $this->user['User']['id'],
					'name' => 'ログインボーナス' . $day . '日目',
					'body' => 'ログインボーナス' . $day . '日目です。',
				);
				$bonus = $GLOBALS['LOGIN_BONUS'][$this->user['User']['loginCount']];
				$message += $bonus;
				$result = false;
				$this->loadModel('Message');
				$this->User->begin();
				if ($this->User->save($save)) {
					if ($this->Message->save($message)) {
						$log = array(
							'user_id' => $this->user['User']['id'],
						);
						$this->loadModel('LoginLog');
						if ($this->LoginLog->save($log)) {
							$result = true;
						}
					}
				}
				if ($result) {
					$this->User->commit();
				} else {
					$this->User->rollback();
				}
			}
		}
	}

	public function reloadUser() {
		$old = $this->user['User'];
		$this->user = $this->User->read(null, $this->user['User']['id']);
		$this->user['User'] = $this->user['User'] + $old;
		return $this->user;
	}

	public function beforeRender() {
		if (!empty($this->request->params['admin'])) {
			return;
		}
		$result = $this->viewVars;
		$result['version'] = GAME_VERSION;
		$result['maintenance'] = MAINTENANCE;

		if ($this->user) {
			$userKeys = array(
				'stage_id',
				'level_id',
				'gold',
				'stone',
				'hp',
				'maxHp',
				'hpChecked',
				'firstGachaDone',
				'lastGachaDate',
			);
			$result['user'] = array();
			foreach ($userKeys as $key) {
				$result['user'][$key] = $this->user['User'][$key];
			}
		}
		echo $this->jsonEncode($result);
		exit;
	}

	public function jsonEncode($str) {
		$json = json_encode($str, JSON_NUMERIC_CHECK);
		$json = str_replace('\\r\\n', 'KAIGYO', $json);
		$json = str_replace('\\r', 'KAIGYO', $json);
		$json = str_replace('\\n', 'KAIGYO', $json);
		return $json;
	}

	protected function checkMaintenance() {
		if (MAINTENANCE) {
			$result['maintenance'] = MAINTENANCE;
			$result['message'] = file_get_contents(APP . 'View/Elements/maintenance.ctp');
			echo $this->jsonEncode($result);
			exit;
		}
	}

	public function _404() {
		throw new NotFoundException();
	}
}
