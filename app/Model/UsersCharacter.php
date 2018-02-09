<?php
class UsersCharacter extends AppModel {
	
	public function generate($user_id, $character_id, $returnData = false) {
		if ($this->findByUserIdAndCharacterId($user_id, $character_id)) {
			$result = $this->addPlus($user_id, $character_id);
		} else {
			$add = array(
				'user_id' => $user_id,
				'character_id' => $character_id,
			);
			$this->create($add);
			$result = $this->save();
		}
		if ($returnData) {
			return $this->findByUserIdAndCharacterId($user_id, $character_id);
		}
		return $result;
	}
	
	public function findForUser($user_id, $character_id = null) {
		$find = array(
			'conditions' => array(
				'UsersCharacter.user_id' => $user_id,
			),
			'order' => 'UsersCharacter.character_id',
		);
		if ($character_id) {
			$find['conditions']['UsersCharacter.character_id'] = $character_id;
		}
		$usersCharacters = $this->findSimple('all', $find);
		return $usersCharacters;
	}
	
	public function progress($id, $character_id) {
		$save = array(
			'id' => $id,
			'character_id' => $character_id,
		);
		return $this->save($save);
	}
	
	public function calcGachaLeft($user_id, $rares, &$usersCharacters) {
		App::uses('Character', 'Model');
		$mCharacter = new Character();
		$find = array(
			'fields' => array('Character.*', 'UsersCharacter.*'),
			'joins' => array(
				"LEFT JOIN users_characters AS UsersCharacter "
				. " ON UsersCharacter.user_id = $user_id AND UsersCharacter.character_id = Character.id",
			),
			'conditions' => array(
				'Character.rare' => $rares,
				'Character.noGacha' => 0,
			),
		);
		$usersCharacters = $mCharacter->find('all', $find);
		$count = 0;
		foreach ($usersCharacters as $usersCharacter) {
			if ($usersCharacter['UsersCharacter']['id']) {
				$count += 99 - $usersCharacter['UsersCharacter']['plus'];
			} else {
				$count += 100;
			}
		}
		
		return $count;
	}
	
	public function setStatusAll($usersCharacters) {
		foreach ($usersCharacters as $i => $usersCharacter) {
			$usersCharacters[$i] = $this->setStatus($usersCharacter);
		}
		return $usersCharacters;
	}
	
	public function setStatus($usersCharacter) {
		$level = $this->exp2level($usersCharacter['experience']);
		$chara = $usersCharacter['Character'];
		$usersCharacter['level'] = $level;
		$usersCharacter['hp'] = $this->getStatusValue($level, $chara['minHp'], $chara['maxHp']);
		$usersCharacter['attack'] = $this->getStatusValue($level, $chara['minAttack'], $chara['maxAttack']);
		$usersCharacter['defence'] = $this->getStatusValue($level, $chara['minDefence'], $chara['maxDefence']);
		
		return $usersCharacter;
	}
	
	public function getStatusValue($level, $min, $max) {
		$power = $min;
		$power += floor(($max - $min) * ($level - 1) / 99);
		return $power;
	}
	
	public function exp2level($experience) {
		App::uses('Level', 'Model');
		$mLevel = new Level();
		$levels = $mLevel->findWithKey('all');
		foreach ($levels as $level => $row) {
			if ($experience < $row['experience']) {
				return $level - 1;
			}
		}
		return $level;
	}
	
	public function addExperience($user_id, $character_id, $experience) {
		$save = array(
			'experience' => "experience + $experience",
		);
		$conditions = array(
			'user_id' => $user_id,
			'character_id' => $character_id,
		);
		return $this->updateAll($save, $conditions);
	}
	
	public function addPlus($user_id, $character_id, $plus = 1) {
		$save = array(
			'plus' => "`plus` + $plus",
		);
		$conditions = array(
			'user_id' => $user_id,
			'character_id' => $character_id,
		);
		return $this->updateAll($save, $conditions);
	}
}
