<?php
class Payment extends AppModel {
	public $items = array(
		1 => array('id' => 1, 'name' => '宝玉', 'price' => 100, 'itemId' => 1, 'amount' => 1),
		10 => array('id' => 10, 'name' => '宝玉10個セット', 'price' => 1000, 'itemId' => 1, 'amount' => 10),
		100 => array('id' => 100, 'name' => 'ガチャ', 'price' => 300, 'itemId' => 100, 'amount' => 1),
		110 => array('id' => 110, 'name' => 'ガチャ10連', 'price' => 3000, 'itemId' => 100, 'amount' => 10),
	);
}
