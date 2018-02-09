<?php
class Stage extends AppModel {

	public function initWeekdayEvent($stages) {
		if ($wday = date('w')) {
			$index = 1;
			foreach ($stages as $i => $stage) {
				if ($stage['event_ui']) {
					if ($wday == $index++) {
						$stages[$i]['end_time'] = date('Y-m-d 23:59:59');
						break;
					}
				}
			}
		}

		return $stages;
	}
}
