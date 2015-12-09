<link rel=stylesheet href="/source/css/debug.css">
<script>
function clickDebug(){
	if (document.getElementById('debug_forma').style.display == 'none'){
		document.getElementById('debug_forma').style.display = 'block';
	} else {
		document.getElementById('debug_forma').style.display = 'none';
	}
}
</script>
<div id="debug_form" class="debug_form">
<table class="debug_table" onclick="clickDebug();return false;">
	<tr>
		<td class="debug_header">Debug information</td>
	</tr>
</table>
</div>
<div id="debug_forma" class="debug_forma" style="display: none;">
<?php if (!empty($this->timers)): ?>
<table class="debug_table">
	<tr>
		<td class="debug_header_name font10" colspan="2">Processing times:</td>
	</tr>

	<?php foreach ($this->timers as $key => $item): ?>
	<tr>
		<td class="debug_name font12"><?php echo $item['name'] ?>:</td>
		<td class="debug_value font10"><?php echo sprintf("%.2f", $item['time']*1000) ?> ms.</td>
	</tr>
    <?php endforeach; ?>
	<tr>
		<td class="debug_header_name font10" colspan="2">SQL Queries: (total <?php echo $this->sql['count'].", ".$this->sql['time'] ?> ms):</td>
	</tr>

	<?php foreach ($this->sql_queries as $key => $item): ?>
	<tr>
		<td class="debug_name font10" style="text-align:center;"><?php echo $item['time'] ?> ms.</td>
		<td class="debug_value font10"><span class="debug_sql_text"><?php echo $item['sql'] ?></span><br><font class="font9"><?php echo $item['file'].", ".$item['line'] ?></font></td>
	</tr>
    <?php endforeach; ?>
	<tr>
		<td class="debug_header_name font10" colspan="2">SQL Errors:</td>
	</tr>
    <?php foreach ($this->sql_errors as $key => $item): ?>
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10" style="color: #D90E0E;"><?php echo $item['error'] ?></font><br>
			<span class="debug_sql_text"><?php echo $item['sql'] ?></span><br>
			<font class="font9"><?php echo $item['file'].", ".$item['line'] ?></font>
		</td>
	</tr>
    <?php endforeach; ?>
	<tr>
		<td class="debug_header_name font10" colspan="2">Info:</td>
	</tr>
    <?php foreach ($this->engine_errors['info'] as $key => $item): ?>
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10"><?php echo $item['str'] ?></font><br>
			<font class="font9"><?php echo $item['file'].", ".$item['line'] ?></font>
		</td>
	</tr>
    <?php endforeach; ?>
	<tr>
		<td class="debug_header_name font10" colspan="2">Function Errors:</td>
	</tr>
	<?php foreach ($this->engine_errors['error'] as $key => $item): ?>
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10"><?php echo $item['str'] ?></font><br>
			<font class="font9"><?php echo $item['file'].", ".$item['line'] ?></font>
		</td>
	</tr>
    <?php endforeach; ?>
	<tr>
		<td class="debug_header_name font10" colspan="2">Functions Warnings:</td>
	</tr>
	<?php foreach ($this->engine_errors['warning'] as $key => $item): ?>
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10"><?php echo $item['str'] ?></font><br>
			<font class="font9"><?php echo $item['file'].", ".$item['line'] ?></font>
		</td>
	</tr>
    <?php endforeach; ?>
	<tr>
		<td class="debug_header_name font10" colspan="2">Function Notices:</td>
	</tr>
	<?php foreach ($this->engine_errors['notice'] as $key => $item): ?>
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10"><?php echo $item['str'] ?></font><br>
			<font class="font9"><?php echo $item['file'].", ".$item['line'] ?></font>
		</td>
	</tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</div>