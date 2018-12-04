<aside class="debug">
	{if="$core.exception"}
	<h6 class="exception">Wyjątek</h6>
	<p><strong>{$core.exception.message}</strong><br /><small>{$core.exception.file}@{$core.exception.line}</small></p>
	<pre class="exception">{$core.exception.trace|var_export}</pre>
	{/if}
	{if="$core.buffer"}
	<h6>Bufor</h6>
	<pre>{$core.buffer}</pre>
	{/if}
	{if="$core.log"}
	<h6>Log</h6>
	<table class="log">
		{loop="$core.log" as $log}
		<tr>
			<td>{$log.ts|$vf->ftime}</td>
			<td><span class="text">{$log.text}</span><span class="info"><strong>{$log.call}</strong>, {$log.file}@{$log.line}</span>
		</tr>
		{/loop}
	</table>
	{/if}
</aside>