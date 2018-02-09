<?php
class NewsController extends AppController {
	public $name = 'News';
	
	public function index() {
		$this->set('news', $this->News->findLatest());
	}
}
