<?php
class Enemy extends AppModel {
	
	public function findLevelSum($stage_id, $level_id) {
		$find = array(
			'fields' => array(
				'SUM(gold) AS goldSum',
				'SUM(experience) AS experienceSum',
			),
			'conditions' => array(
				'Enemy.stage_id' => $stage_id,
				'Enemy.level_id' => $level_id,
			),
		);
		return $this->find('first', $find);
	}
}
