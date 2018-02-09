<?php
class MenuController extends AppController {
	public $name = 'Menu';
	public $uses = null;

	public function getMasterData() {
		$this->set('user', $this->user ? $this->user['User'] : null);

		$this->loadModel('News');
		$this->set('news', $this->News->findLatest());

		if ($this->user) {
			$this->loadModel('Message');
			$this->set('messages', $this->Message->findLatest($this->user['User']['id']));
		}

		$this->loadModel('Stage');
		$stages = $this->Stage->findSimple('all');
		$stages = $this->Stage->initWeekdayEvent($stages);
		$this->set('stages', $stages);

		$this->set('paymentItems', $this->PaymentAction->items);

		$this->set('gachaRatio', $GLOBALS['GACHA_RATIO']);

		if ($this->user) {
			$this->loadModel('UsersCharacter');
			$find = array(
				'conditions' => array(
					'UsersCharacter.user_id' => $this->user['User']['id'],
				),
				'order' => 'UsersCharacter.character_id',
			);
			$this->set('usersCharacters', $this->UsersCharacter->findSimple('all', $find));

			$this->loadModel('UsersItem');
			$find = array(
				'conditions' => array(
					'UsersItem.user_id' => $this->user['User']['id'],
				),
				'order' => 'UsersItem.item_id',
			);
			$this->set('usersItems', $this->UsersItem->findSimple('all', $find));
		}
	}

	public function test() {
	}

	public function webdump() {
		$this->log($_POST['str'], 'dump');
	}

	public function admin_dbdata() {
		$data = array();

		$this->loadModel('Character');
		$data['characters'] = $this->Character->findSimple('all');

		$this->loadModel('StageLevel');
		$data['stageLevels'] = $this->StageLevel->findSimple('all');

		foreach ($data as $key => $row) {
			echo "Configure.$key = " . json_encode($row, JSON_NUMERIC_CHECK) . ';';
			echo '<br />';
		}
		exit;
	}

	public function admin_status() {
		if (!empty($this->request->named['stageId'])) {
			$stageId = $this->request->named['stageId'];
			$levelId = $this->request->named['levelId'];
			$this->loadModel('Level');
			$this->loadModel('Enemy');
			$find = array(
				'fields' => 'SUM(experience) AS expSum, SUM(gold) AS goldSum',
				'conditions' => array(
					'or' => array(
						'Enemy.stage_id <' => $stageId,
						array(
							'Enemy.stage_id' => $stageId,
							'Enemy.level_id <=' => $levelId,
						),
					),
				),
			);
			$result = $this->Enemy->find('first', $find);
			$exp = $result[0]['expSum'];
			$levels = $this->Level->findSimple('all');
			foreach ($levels as $i => $row) {
				if ($row['experience'] > $exp) {
					$level = $i - 1;
					break;
				}
			}
		} else if (!empty($this->request->named['level'])) {
			$level = $this->request->named['level'];
		}

		$this->loadModel('Character');
		$find = array(
			'conditions' => array(),
		);
		if (isset($this->request->named['rares'])) {
			$find['conditions']['rare'] = explode(',', $this->request->named['rares']);
		}
		$characters = $this->Character->find('all', $find);
		$attackSum = 0;
		$defenceSum = 0;
		$hpSum = 0;
		foreach ($characters as $chara) {
			$chara = $chara['Character'];
			$hpSum += $chara['minHp'] + floor(($chara['maxHp'] - $chara['minHp']) * ($level - 1) / 99);
			$attackSum += $chara['minAttack'] + floor(($chara['maxAttack'] - $chara['minAttack']) * ($level - 1) / 99);
			$defenceSum += $chara['minDefence'] + floor(($chara['maxDefence'] - $chara['minDefence']) * ($level - 1) / 99);
		}

		$count = count($characters);
		echo "level: $level<BR>";
		echo "exp: $exp<BR>";
		echo "hp: " . floor($hpSum / $count) . '<BR>';
		echo "attack: " . floor($attackSum / $count) . '<BR>';
		echo "defence: " . floor($defenceSum / $count) . '<BR>';
		echo 'gold: ' . $result[0]['goldSum'] . '<BR>';
		exit;
	}

	public function admin_test() {
		$this->loadModel('Character');
		$counts = array();
		for ($i = 0; $i < 100; $i++) {
			$chara = $this->Character->gacha(array('SSR', 'SR', 'R'));
			if (empty($counts[$chara['Character']['rare']])) {
				$counts[$chara['Character']['rare']] = 1;
			} else {
				$counts[$chara['Character']['rare']]++;
			}
			echo $chara['Character']['rare'] . ',' . $chara['Character']['name'] . '<BR>';
		}
		pr($counts);
		exit;
	}
}
