<?php
class UsersController extends AppController {
	public $name = 'Users';
	public $uses = array('User', 'UsersCharacter');
	private $useStones = array(
		'continue' => 1,
		'heal' => 1,
	);
	
	public function playGame() {
		$result = false;
		foreach ($this->stages as $stage) {
			if ($stage['id'] == $this->request->data['stage']) {
				$now = date('Y-m-d H:i:s');
				if ($stage['start_time'] && $now < $stage['start_time']) {
					$stage = null;
				}
				if ($stage['end_time'] && $now > $stage['end_time']) {
					$stage = null;
				}
				break;
			}
		}
		if ($stage) {
			$characterIds = explode(',', $this->request->data['character_id']);
			$usersCharacters = $this->UsersCharacter->findForUser($this->user['User']['id'], $characterIds);
			if ($usersCharacters) {
				$save = array(
					'id' => $this->user['User']['id'],
					'playingStage' => $this->request->data['stage'],
					'playingLevel' => $this->request->data['level'],
					'playingCharacters' => $this->request->data['character_id'],
				);
				$this->loadModel('StageLevel');
				$find = array(
					'conditions' => array(
						'StageLevel.stageId' => $save['playingStage'],
						'StageLevel.level' => $save['playingLevel'],
					),
				);
				$stageLevel = $this->StageLevel->find('first', $find);
				if ($this->user['User']['hp'] >= $stageLevel['StageLevel']['hp']) {
					$save['hp'] = $this->user['User']['hp'] - $stageLevel['StageLevel']['hp'];
					$this->User->set($save);
					if ($this->User->save()) {
						$this->loadModel('Enemy');
						$find = array(
							'conditions' => array(
								'Enemy.stage_id' => $save['playingStage'],
								'Enemy.level_id' => $save['playingLevel'],
							),
						);
						if ($enemies = $this->Enemy->findSimple('all', $find)) {
							$result = true;
							$this->set('usersCharacters', $usersCharacters);
							$this->set('enemies', $enemies);
						}
					}
				}
			}
		} else {
			$this->set('errorMessage', 'そのステージは現在プレイできません。');
		}
		$this->set('result', $result);
	}
	
	public function endGame() {
		$result = false;
		if ($this->user['User']['playingStage']) {
			$this->loadModel('Enemy');
			$this->loadModel('Character');
			$this->loadModel('StageLevel');
			$this->loadModel('UsersItem');
			$this->User->begin();
			$characterIds = explode(',', $this->user['User']['playingCharacters']);
			$enemies = $this->Enemy->findAllByStageIdAndLevelId(
				$this->user['User']['playingStage'],
				$this->user['User']['playingLevel']
			);

			$stageLevels = $this->StageLevel->findAllByStageId($this->user['User']['playingStage']);
			foreach ($stageLevels as $row) {
				if ($row['StageLevel']['level'] == $this->user['User']['playingLevel']) {
					$stageLevel = $row;
				}
			}
			$gold = 0;
			$exp = 0;
			$gets = array();
			foreach ($enemies as $enemy) {
				$gold += $enemy['Enemy']['gold'];
				$exp += $enemy['Enemy']['experience'];
				//1.5%の確率で仲間になる
				if (!$stageLevel['StageLevel']['addCharacterId']) {
					if (rand(0, 999) < 15) {
						$gets[] = $enemy;
					}
				}
			}
			//プレイ前状態に戻す
			$save = array(
				'playingStage' => 0,
				'playingLevel' => 0,
				'playingCharacters' => "''",
				'gold' => "gold + $gold",
			);
			$isLastLevel = false;
			//先のストーリーであれば現在位置を更新
			if ($this->user['User']['playingStage'] < 10000) {
				if ($this->user['User']['playingStage'] > $this->user['User']['stage_id']
				|| ($this->user['User']['playingStage'] == $this->user['User']['stage_id']
				&& $this->user['User']['playingLevel'] > $this->user['User']['level_id'])) {
					$save['stage_id'] = $this->user['User']['playingStage'];
					$save['level_id'] = $this->user['User']['playingLevel'];
					//最後のレベルであれば体力増加＆回復、アイテムプレゼント
					$lastLevel = $stageLevels[count($stageLevels) - 1];
					if ($lastLevel['StageLevel']['level'] == $this->user['User']['playingLevel']) {
						$isLastLevel = true;
						$this->user['User']['stage_id'] = $save['stage_id'];
						$this->user['User']['level_id'] = $save['level_id'];
						$maxHp = $this->User->getMaxHp($this->user, $this->stages);
						$this->user['User']['maxHp'] = $maxHp;
						if ($maxHp > $this->user['User']['hp']) {
							$save['hp'] = $this->user['User']['hp'] + $maxHp;
							$save['hpChecked'] = 'NOW()';
						}
					}
				}
			}
			$conditions = array(
				'User.id' => $this->user['User']['id'],
			);
			if ($this->User->updateAll($save, $conditions)) {
				//経験値追加
				if ($this->UsersCharacter->addExperience($this->user['User']['id'], $characterIds, $exp)) {
					if ($usersCharacters = $this->UsersCharacter->findForUser(
						$this->user['User']['id'],
						$characterIds
					)) {
						$this->set('usersCharacters', $usersCharacters);
						$result = true;
						$getUsersCharacters = null;
						$getItems = null;
						if ($stageLevel['StageLevel']['addCharacterId']) {
							//イベントで仲間になる場合
							if (!$this->UsersCharacter->findForUser($this->user['User']['id'], $stageLevel['StageLevel']['addCharacterId'])) {
								if ($got = $this->UsersCharacter->generate(
									$this->user['User']['id'],
									$stageLevel['StageLevel']['addCharacterId'],
									true
								)) {
									$getUsersCharacters = array($got['UsersCharacter']);
								} else {
									$result = false;
								}
							}
						} else if ($gets) {
							//確率で仲間になる場合
							if ($characters = $this->Character->findAllByEnemy($gets)) {
								$getUsersCharacters = array();
								foreach ($characters as $character) {
									if ($got = $this->UsersCharacter->generate(
										$this->user['User']['id'],
										$character['Character']['id'],
										true
									)) {
										$getUsersCharacters[] = $got['UsersCharacter'];
									} else {
										$result = false;
										break;
									}
								}
							}
						}
						$this->set('getUsersCharacters', $getUsersCharacters);
						
						if ($result && $isLastLevel) {
							//ステージ完了時にアイテム付与
							$result = $this->UsersItem->get($this->user['User']['id'], 2, 3);
							if ($result && $this->user['User']['stage_id'] == 2) {
								$this->loadModel('Invite');
								$result = $this->Invite->close($this->user);
							}
						}
						if ($result) {
							//アイテムドロップ
							if ($stageLevel['StageLevel']['items']) {
								if ($items = $this->StageLevel->dropItem($stageLevel)) {
									$result = $this->UsersItem->getItems($this->user['User']['id'], $items);
									$getItems = $items;
								}
							}
							$this->set('getItems', $getItems);
							$itemIds = array();
							if ($getItems) {
								$itemIds = array_keys($getItems);
							}
							if ($isLastLevel) {
								$itemIds[] = 2;
							}
							if ($itemIds) {
								$this->set('usersItems', $this->UsersItem->findForUser(
									$this->user['User']['id'], $itemIds
								));
							}
							if ($result) {
								$this->set('isLastLevel', $isLastLevel);
								$this->User->commit();
								$this->reloadUser();
							}
						}
					}
				}
			}
			if (!$result) {
				$this->User->rollback();
			}
		}
		$this->set('result', $result);
	}
	
	public function retireGame() {
		//プレイ前状態に戻す
		$save = array(
			'id' => $this->user['User']['id'],
			'playingStage' => 0,
			'playingLevel' => 0,
			'playingCharacters' => '',
		);
		$this->User->set($save);
		$result = $this->User->save();
		$this->set('result', $result);
	}
	
	public function useStone() {
		$result = false;
		$this->User->begin();
		if (isset($this->useStones[$this->request->data['mode']])) {
			$number = $this->useStones[$this->request->data['mode']];
			if ($this->user['User']['stone'] >= $number) {
				if ($this->User->useStone($this->user['User']['id'], $number)) {
					if ($this->User->useStoneEffect($this->user['User']['id'], $this->request->data['mode'])) {
						$this->loadModel('StoneLog');
						$log = array(
							'user_id' => $this->user['User']['id'],
							'used' => 1,
							'mode' => $this->request->data['mode'],
							'stone' => $number,
						);
						$this->StoneLog->create($log);
						if ($this->StoneLog->save()) {
							$result = true;
						}
					}
				}
			}
		}
		if ($result) {
			$this->reloadUser();
			$this->User->commit();
		} else {
			$this->User->rollback();
		}
		$this->set('result', $result);
	}
	
	public function generate() {
		$result = false;
		if (!$this->user) {
			$user = array(
				'platform_id' => $this->OpenSocial->getUserId(),
				'hp' => 10,
				'hpChecked' => date('Y-m-d H:i:s'),
			);
			$this->User->create($user);
			$this->User->begin();
			if ($this->User->save()) {
				$id = $this->User->getInsertID();
				$this->loadModel('Character');
				$this->loadModel('UsersCharacter');
				$characterIds = array();
				$find = array(
					'conditions' => array(
						'rare' => 'R',
						'noGacha' => false,
					),
					'order' => 'RAND()',
					'limit' => 3,
				);
				$characters = $this->Character->find('all', $find);
				$find['conditions']['rare'] = 'SR';
				$find['limit'] = 1;
				$characters[] = $this->Character->find('first', $find);
				$result = true;
				foreach ($characters as $character) {
					$usersCharacter = array(
						'user_id' => $id,
						'character_id' => $character['Character']['id'],
					);
					$this->UsersCharacter->create($usersCharacter);
					if (!$this->UsersCharacter->save()) {
						$result = false;
						break;
					}
					$characterIds[] = $character['Character']['id'];
				}
				$characterIds = array_reverse($characterIds);
				$this->set('characterIds', join(',', $characterIds) . ',,');
			}
			if ($result) {
				$this->User->commit();
			} else {
				$this->User->rollback();
			}
		}
		$this->set('result', $result);
	}
}
