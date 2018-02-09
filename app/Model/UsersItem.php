<?php
class UsersItem extends AppModel {
	
	public function findForUser($user_id, $item_id = null) {
		$find = array(
			'conditions' => array(
				'UsersItem.user_id' => $user_id,
			),
			'order' => 'UsersItem.item_id',
		);
		if ($item_id) {
			$find['conditions']['UsersItem.item_id'] = $item_id;
		}
		return $this->findSimple('all', $find);
	}
	
	public function getItems($user_id, $items) {
		foreach ($items as $item_id => $number) {
			if (!$this->get($user_id, $item_id, $number)) {
				return false;
			}
		}
		return true;
	}
	
	public function get($user_id, $item_id, $number) {
		$sql = "INSERT INTO users_items(user_id, item_id, number, created, modified) "
			. " VALUES($user_id, $item_id, $number, NOW(), NOW())"
			. " ON DUPLICATE KEY UPDATE number = number + $number, modified = NOW()";
		return $this->query($sql) !== false;
	}
	
	public function useItems($user_id, $items) {
		$itemIds = array_keys($items);
		$find = array(
			'conditions' => array(
				'UsersItem.user_id' => $user_id,
				'UsersItem.item_id' => $itemIds,
			),
		);
		if ($usersItems = $this->find('all', $find)) {
			$result = array();
			foreach ($items as $itemId => $number) {
				$ok = false;
				foreach ($usersItems as $usersItem) {
					if ($usersItem['UsersItem']['item_id'] == $itemId) {
						$ok = $usersItem['UsersItem']['number'] >= $number;
						$row = $usersItem['UsersItem'];
						$row['number'] -= $number;
						$result[] = $row;
						break;
					}
				}
				if (!$ok) return false;
			}
			foreach ($items as $itemId => $number) {
				if (!$this->useItem($user_id, $itemId, $number)) {
					return false;
				}
			}
			return $result;
		}
		return false;
	}
	
	public function useItem($user_id, $item_id, $number) {
		$sql = "UPDATE users_items SET number = IF(number < $number, 0, number - $number)"
			. " WHERE user_id = $user_id AND item_id = $item_id";
		return $this->query($sql) !== false;
	}
}
