<?php
class UsersCharactersController extends AppController {
	public $name = 'UsersCharacters';
	
	public function index() {
		$conditions = array();
		$characterIds = null;
		if (!empty($this->request->data['character_id'])) {
			$characterIds = explode(',', $this->request->data['character_id']);
		}
		$usersCharacters = $this->UsersCharacter->findForUser($this->user['User']['id'], $characterIds);
		$this->set('usersCharacters', $usersCharacters);
	}
	
	public function progress() {
		$this->UsersCharacter->begin();
		$usersCharacters = $this->UsersCharacter->findForUser(
			$this->user['User']['id'],
			$this->request->data['character_id']
		);
		$result = false;
		if ($usersCharacters) {
			$this->loadModel('Character');
			$usersCharacter = $usersCharacters[0];
			$character = $this->Character->read(null, $usersCharacter['character_id']);
			if ($character && $character['Character']['progress']) {
				if ($items = $this->Character->parseProgressItem($character)) {
					$this->loadModel('UsersItem');
					if ($usersItems = $this->UsersItem->useItems($this->user['User']['id'], $items)) {
						if ($this->UsersCharacter->progress($usersCharacter['id'], $character['Character']['progress'])) {
							$usersCharacters = $this->UsersCharacter->findForUser(
								$this->user['User']['id'],
								$character['Character']['progress']
							);
							if ($usersCharacters) {
								$result = true;
								$this->set('usersCharacter', $usersCharacters[0]);
								$this->set('usersItems', $usersItems);
							}
						}
					}
				}
			}
		}
		if ($result) {
			$this->UsersCharacter->commit();
		} else {
			$this->UsersCharacter->rollback();
		}
		$this->set('result', $result);
	}
	
	public function plus() {
		$this->UsersCharacter->begin();
		$usersCharacters = $this->UsersCharacter->findForUser(
			$this->user['User']['id'],
			$this->request->data['character_id']
		);
		$result = false;
		if ($usersCharacters) {
			$this->loadModel('Character');
			$usersCharacter = $usersCharacters[0];
			if ($usersCharacter['plus'] < PLUS_MAX) {
				$character = $this->Character->read(null, $usersCharacter['character_id']);
				if ($character && $character['Character']['plus']) {
					if ($items = $this->Character->parseProgressItem($character)) {
						$this->loadModel('UsersItem');
						if ($usersItems = $this->UsersItem->useItems($this->user['User']['id'], $items)) {
							if ($this->UsersCharacter->addPlus($this->user['User']['id'], $usersCharacter['character_id'])) {
								$usersCharacters = $this->UsersCharacter->findForUser(
									$this->user['User']['id'],
									$character['Character']['id']
								);
								if ($usersCharacters) {
									$result = true;
									$this->set('usersCharacter', $usersCharacters[0]);
									$this->set('usersItems', $usersItems);
								}
							}
						}
					}
				}
			}
		}
		if ($result) {
			$this->UsersCharacter->commit();
		} else {
			$this->UsersCharacter->rollback();
		}
		$this->set('result', $result);
	}
}
