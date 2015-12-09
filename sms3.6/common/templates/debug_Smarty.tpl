<link rel=stylesheet href="/source/css/debug.css">
<style>
{literal}

{/literal}
</style>
<script>
{literal}
function clickDebug(){
	if (document.getElementById('debug_forma').style.display == 'none'){
		document.getElementById('debug_forma').style.display = 'block';
	} else {
		document.getElementById('debug_forma').style.display = 'none';
	}
}
{/literal}
</script>
<div id="debug_form" class="debug_form">
<table class="debug_table" onclick="clickDebug();return false;">
	<tr>
		<td class="debug_header">Debug information</td>
	</tr>
</table>
</div>
<div id="debug_forma" class="debug_forma" style="display: none;">
{if !empty($timers)}
<table class="debug_table">
	<tr>
		<td class="debug_header_name font10" colspan="2">Processing times:</td>
	</tr>
{foreach from=$timers key=key item=item}
	<tr>
		<td class="debug_name font12">{$item.name}:</td>
		<td class="debug_value font10">{$item.time*1000|string_format:"%.2f"} ms.</td>
	</tr>
{/foreach}
	<tr>
		<td class="debug_header_name font10" colspan="2">SQL Queries: (total {$sql.count}, {$sql.time} ms):</td>
	</tr>
{foreach from=$sql_queries key=key item=item}
	<tr>
		<td class="debug_name font10" style="text-align:center;">{$item.time} ms.</td>
		<td class="debug_value font10"><span class="debug_sql_text">{$item.sql}</span><br><font class="font9">{$item.file}, {$item.line}</font></td>
	</tr>
{/foreach}
	<tr>
		<td class="debug_header_name font10" colspan="2">SQL Errors:</td>
	</tr>
{foreach from=$sql_errors key=key item=item}
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10" style="color: #D90E0E;">{$item.error}</font><br>
			<span class="debug_sql_text">{$item.sql}</span><br>
			<font class="font9">{$item.file}, {$item.line}</font>
		</td>
	</tr>
{/foreach}
	<tr>
		<td class="debug_header_name font10" colspan="2">Info:</td>
	</tr>
{foreach from=$engine_errors.info key=key item=item}
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10">{$item.str}</font><br>
			<font class="font9">{$item.file}, {$item.line}</font>
		</td>
	</tr>
{/foreach}
	<tr>
		<td class="debug_header_name font10" colspan="2">Function Errors:</td>
	</tr>
{foreach from=$engine_errors.error key=key item=item}
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10">{$item.str}</font><br>
			<font class="font9">{$item.file}, {$item.line}</font>
		</td>
	</tr>
{/foreach}
	<tr>
		<td class="debug_header_name font10" colspan="2">Functions Warnings:</td>
	</tr>
{foreach from=$engine_errors.warning key=key item=item}
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10">{$item.str}</font><br>
			<font class="font9">{$item.file}, {$item.line}</font>
		</td>
	</tr>
{/foreach}
	<tr>
		<td class="debug_header_name font10" colspan="2">Function Notices:</td>
	</tr>
{foreach from=$engine_errors.notice key=key item=item}
	<tr>
		<td class="debug_name font10" style="text-align:center;">&nbsp;</td>
		<td class="debug_value font10">
			<font class="font10">{$item.str}</font><br>
			<font class="font9">{$item.file}, {$item.line}</font>
		</td>
	</tr>
{/foreach}
</table>
{/if}
</div>