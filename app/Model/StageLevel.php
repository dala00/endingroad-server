<?php
class StageLevel extends AppModel {
	
	public function findAllByStageId($stageId) {
		$find = array(
			'conditions' => array(
				'StageLevel.stageId' => $stageId,
			),
			'order' => 'StageLevel.level'
		);
		return $this->find('all', $find);
	}
	
	public function findByStageIdAndLevel($stageId, $level) {
		$find = array(
			'conditions' => array(
				'StageLevel.stageId' => $stageId,
				'StageLevel.level' => $level,
			),
		);
		return $this->find('first', $find);
	}
	
	public function dropItem($stageLevel) {
		$items = array();
		$lines = explode("\n", $stageLevel['StageLevel']['items']);
		foreach ($lines as $line) {
			if ($line = trim($line)) {
				list($itemId, $ratios) = explode(':', $line);
				$ratios = explode(',', $ratios);
				$rand = rand(0, count($ratios) - 1);
				if ($ratios[$rand]) {
					if (empty($items[$itemId])) {
						$items[$itemId] = $ratios[$rand];
					} else {
						$items[$itemId] += $ratios[$rand];
					}
				}
			}
		}
		
		return $items;
	}
}
