<script type="text/javascript">
$(document).ready(function() {
	$('.enemy').draggable({
		stop: function(event, ui) {
			var no = this.id.replace('enemy', '');
			var x = ui.position.left + 16;
			var y = 360 - ui.position.top - 16;
			$('#Enemy' + no + 'X').val(x);
			$('#Enemy' + no + 'Y').val(y);
		}
	});
});
</script>

<h1>ステージ<?php echo $stage_id; ?> レベル<?php echo $level_id; ?> フェイズ<?php echo $phase; ?></h1>
<div>
gold: <?php echo $gold; ?><br />
exp: <?php echo $exp; ?>
</div>
<div>
<?php echo $this->Html->link('前のレベル', array('action' => 'position', $stage_id, $level_id - 1, 1)); ?>
 <?php echo $this->Html->link('次のレベル', array('action' => 'position', $stage_id, $level_id + 1, 1)); ?>
</div>
<div>
<?php echo $this->Html->link('前のフェイズ', array('action' => 'position', $stage_id, $level_id, $phase - 1)); ?>
 <?php echo $this->Html->link('次のフェイズ', array('action' => 'position', $stage_id, $level_id, $phase + 1)); ?>
</div>
<div style="width:640px; height:360px; background-color: #CCC; position:relative;">
<?php foreach ($enemies as $i => $enemy) { ?>
<div id="enemy<?php echo $i; ?>" class="enemy" style="
	position:absolute;
	left:<?php echo $enemy['Enemy']['x'] - 16; ?>px;
	top:<?php echo 360 - $enemy['Enemy']['y'] - 16; ?>px;
	width:32px;
	height:32px;
	background-color:white;
	font-size: 6px;
	white-space:nowrap;
" />
ID:<?php echo $enemy['Enemy']['id']; ?><br />
<?php echo $enemy['Enemy']['name']; ?><br />
<?php echo $enemy['Enemy']['hp']; ?>
</div>
<?php } ?>
</div>

<?php echo $this->Form->create('Enemy');
foreach ($enemies as $i => $enemy) {
	echo $this->Form->hidden("$i.id", array('value' => $enemy['Enemy']['id']));
	echo $this->Form->hidden("$i.x", array('value' => $enemy['Enemy']['x']));
	echo $this->Form->hidden("$i.y", array('value' => $enemy['Enemy']['y']));
}
?>
<input type="submit" value="更新" />
</form>
