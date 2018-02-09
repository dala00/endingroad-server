<?php
class EnemiesController extends AppController {
	public $name = 'Enemies';
	
	public function admin_position($stage_id, $level_id, $phase) {
		if (!empty($this->request->data)) {
			foreach ($this->request->data['Enemy'] as $enemy) {
				$this->Enemy->set($enemy);
				$this->Enemy->save();
			}
			$this->redirect(array('action' => 'position', $stage_id, $level_id, $phase + 1));
		}
		$enemies = $this->Enemy->findAllByStageIdAndLevelIdAndPhase($stage_id, $level_id, $phase);
		$exp = 0;
		$gold = 0;
		foreach ($enemies as $enemy) {
			$exp += $enemy['Enemy']['experience'];
			$gold += $enemy['Enemy']['gold'];
		}
		$this->set('enemies', $enemies);
		$this->set('stage_id', $stage_id);
		$this->set('level_id', $level_id);
		$this->set('phase', $phase);
		$this->set('exp', $exp);
		$this->set('gold', $gold);
	}
}
