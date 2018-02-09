<?php $this->Html->scriptBlock("
var ids = [" . join(',', $ids) . "];
var currentIndex = 0;

function sendMessage() {
	var sendIds = [];
	var isOver = false;
	for (var i = 0; i < 240; i++) {
		var index = i + currentIndex;
		if (index < ids.length) {
			sendIds.push(ids[index]);
		} else {
			isOver = true;
			break;
		}
	}
	
	if (sendIds.length > 0) {
		currentIndex += sendIds.length;
		$.post(
			'" . $this->Html->url(array('action' => 'ajax_message')) . "',
			{ids:sendIds.join(','), message:$('#message').val()},
			function (result) {
				if (result == '') {
					$('#sent').html(currentIndex);
					setTimeout('sendMessage()', 1000);
				} else {
					alert(result);
				}
			},
			'text'
		);
	}
}

", array('inline' => false)); ?>
メッセージ<br />
半角38文字、全角19文字<br />
<input type="text" id="message" value="" /><br />
<br />
<input type="button" value="送信" onclick="sendMessage();" />
<div>
<span id="sent">0</span> / <?php echo count($ids); ?>送信完了
</div>