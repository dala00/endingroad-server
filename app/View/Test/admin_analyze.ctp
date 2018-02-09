<?php $fields = array('id', 'user_id', 'platform', 'name', 'price', 'detail', 'created'); ?>
<?php $colors = array('#FFFFFF', '#DDDDFF'); ?>
invite: <?php echo $invitedCount; ?> / <?php echo $inviteAllCount; ?>
<table>
<tr>
<?php foreach ($fields as $field) { ?>
<th><?php echo $field; ?></th>
<?php } ?>
</tr>
<?php $colorIndex = 1; $date = null; foreach ($payments as $payment) {
if ($date != substr($payment['Payment']['created'], 0, 10)) {
	$date = substr($payment['Payment']['created'], 0, 10);
	$colorIndex = 1 - $colorIndex;
}
?>
<tr style="background-color:<?php echo $colors[$colorIndex]; ?>">
<?php foreach ($fields as $field) { ?>
<td><?php echo $payment['Payment'][$field]; ?></td>
<?php } ?>
</tr>
<?php } ?>
</table>

