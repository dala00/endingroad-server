<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
	
	public function findSimple($type = 'first', $query = array()) {
		if ($type == 'all') {
			if (!empty($this->gameData)) {
				return $this->gameData;
			} else {
				$data = $this->find($type, $query);
				$result = array();
				foreach ($data as $i => $row) {
					if (isset($row[$this->name])) {
						$row = $row[$this->name];
					}
					$result[] = $row;
				}
				return $result;
			}
		}
	}
	
	public function findWithKey($type = 'first', $query = array()) {
		$data = $this->gameData;
		foreach ($data as $id => $row) {
			$row['id'] = $id;
			$data[$id] = $row;
		}
		return $data;
	}
	
	public function findSimpleKey($type = 'first', $query = array()) {
		$result = $this->find($type, $query);
		if ($type == 'first') {
			$keys = array_keys($result);
			return $result[$keys[0]];
		} else {
			if ($result) {
				$keys = array_keys($result[0]);
				foreach ($result as $i => $row) {
					$result[$i] = $row[$keys[0]];
				}
			}
		}
		return $result;
	}
}
