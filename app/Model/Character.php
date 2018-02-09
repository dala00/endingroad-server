<?php
class Character extends AppModel {
	
	public function gacha($rares, &$usersCharacters) {
		$sum = 0;
		
		//引けないレア度を排除
		$rares = $this->checkRares($rares, $usersCharacters);
		foreach ($rares as $rare) {
			$sum += $GLOBALS['GACHA_RATIO'][$rare];
		}
		
		$random = rand(0, $sum - 1);
		$start = 0;
		foreach ($rares as $rare) {
			$ratio = $GLOBALS['GACHA_RATIO'][$rare];
			if ($start <= $random && $random < $start + $ratio) {
				//+99のキャラを排除して抽選
				$forSelect = array();
				foreach ($usersCharacters as $usersCharacter) {
					if ($usersCharacter['Character']['rare'] == $rare) {
						if ($usersCharacter['UsersCharacter']['plus'] != 99) {
							$forSelect[] = $usersCharacter;
						}
					}
				}
				shuffle($forSelect);
				$character = $forSelect[0];
				
				//所持データを更新
				foreach ($usersCharacters as $i => $usersCharacter) {
					if ($usersCharacter['Character']['id'] == $character['Character']['id']) {
						if ($usersCharacter['UsersCharacter']['id']) {
							$usersCharacter['UsersCharacter']['plus']++;
						} else {
							$usersCharacter['UsersCharacter']['id'] = 'got';
							$usersCharacter['UsersCharacter']['plus'] = 0;
						}
						$usersCharacters[$i] = $usersCharacter;
						break;
					}
				}
				return $character;
			}
			$start += $ratio;
		}
		//echo "$sum, $random<BR><BR>";
		return null;
	}
	
	private function checkRares($rares, $usersCharacters) {
		$oldRares = $rares;
		$rares = array();
		foreach ($oldRares as $rare) {
			foreach ($usersCharacters as $usersCharacter) {
				if ($usersCharacter['Character']['rare'] == $rare) {
					if ($usersCharacter['UsersCharacter']['plus'] != 99) {
						$rares[] = $rare;
						break;
					}
				}
			}
		}
		
		return $rares;
	}
	
	public function findAllByEnemy($enemies) {
		$names = array();
		foreach ($enemies as $enemy) {
			if (!in_array($enemy['Enemy']['name'], $names)) {
				$names[] = $enemy['Enemy']['name'];
			}
		}
		return $this->findAllByName($names);
	}
	
	public function parseProgressItem($character) {
		if ($character['Character']['progressItem']) {
			$items = array();
			$lines = explode("\n", $character['Character']['progressItem']);
			foreach ($lines as $line) {
				if ($line = trim($line)) {
					list($itemId, $number) = explode(':', $line);
					$items[$itemId] = $number;
				}
			}
			return $items;
		}
		return null;
	}
}
