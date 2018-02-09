<?php
class News extends AppModel {
	
	public function findLatest() {
		$find = array(
			'conditions' => array(
				'News.disabled' => 0,
				'News.disabled_' . TERMINAL => 0,
			),
			'order' => 'News.published DESC',
			'limit' => 20,
		);
		return $this->findSimple('all', $find);
	}
}
