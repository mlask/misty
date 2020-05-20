<footer class="debug">
	{if isset($core_buffer) && $core_buffer}
	<h6>Bufor</h6>
	<pre class="buffer">{$core_buffer}</pre>
	{/if}
	{if isset($core_log)}
	<h6>Log</h6>
	<table class="log">
		{foreach $core_log as $log}
		<tr>
			<td>{$log.time|round|date_format:"Y-m-d H:i:s"}<br /><b>{if $log.memory >= 0}+{else}-{/if}{"%0.2f"|sprintf:($log.memory/1024)} kB</b></td>
			<td><code class="text">{$log.message}</code><span class="info"><b>{$log.source.file}</b>@{$log.source.line}</span>
		</tr>
		{/foreach}
	</table>
	{/if}
</footer>