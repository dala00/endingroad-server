<?php
class User extends AppModel {
	
	public function checkHp($user, $stages) {
		$save = array();
		$maxHp = $this->getMaxHp($user, $stages);
		$user['User']['maxHp'] = $maxHp;
		$sec = 60 * 5;
		
		if ($user['User']['hp'] < $user['User']['maxHp']) {
			$now = time();
			$old = strtotime($user['User']['hpChecked']);
			$old -= $old % $sec;
			$past = $now - $old;
			$past -= $past % $sec;
			if ($past) {
				$heal = $past / $sec;
				$save['hp'] = $user['User']['hp'] + $heal;
				if ($save['hp'] > $maxHp) {
					$save['hp'] = $maxHp;
				}
				$save['hpChecked'] = date('Y-m-d H:i:s');
			}
		} else {
			$save['hpChecked'] = date('Y-m-d H:i:s');
		}
		
		if ($save) {
			$save['id'] = $user['User']['id'];
			$this->set($save);
			if ($this->save()) {
				$user['User'] = $save + $user['User'];
			}
		}
		
		return $user;
	}
	
	public function getMaxHp($user = null, $stages = null) {
		static $maxHp = null;

		if (/*$maxHp == null*/$stages) {
			$tmp = $stages;
			$stages = array();
			foreach ($tmp as $stage) {
				$stages[$stage['id']] = $stage;
			}
			$stageId = $user['User']['stage_id'];
			$stage = $stages[$stageId];
			$levelCount = $stage['levelCount'] ? $stage['levelCount'] : 10;
			
			if ($user['User']['level_id'] < $levelCount) {
				if ($stageId == 1) {
					$maxHp = 10;
				} else {
					$maxHp = $stages[$stageId - 1]['maxHp'];
				}
			} else {
				$maxHp = $stage['maxHp'];
			}
		}
		
		return $maxHp;
	}
	
	public function useStoneEffect($id, $mode) {
		$func = 'useStoneEffect' . lcfirst($mode);
		if (method_exists($this, $func)) {
			return $this->$func($id);
		}
		return true;
	}
	
	public function useStoneEffectHeal($id) {
		$maxHp = $this->getMaxHp();
		$sql = "UPDATE users SET hp = hp + $maxHp WHERE id = $id";
		return $this->query($sql) !== false;
	}
	
	public function addStone($id, $number) {
		$sql = "UPDATE users SET stone = stone + $number WHERE id = $id";
		return $this->query($sql) !== false;
	}
	
	public function useStone($id, $number) {
		$sql = "UPDATE users SET stone = IF(stone < $number, 0, stone - $number) WHERE id = $id";
		return $this->query($sql) !== false;
	}
	
	public function addGold($id, $number) {
		$sql = "UPDATE users SET gold = gold + $number WHERE id = $id";
		return $this->query($sql) !== false;
	}
	
	public function useGold($id, $number) {
		$sql = "UPDATE users SET gold = IF(gold < $number, 0, gold - $number) WHERE id = $id";
		return $this->query($sql) !== false;
	}
}
