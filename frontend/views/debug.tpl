<aside class="debug">
	{if isset($core_exception)}
	<h6 class="exception">Wyjątek</h6>
	<p><strong>{$core_exception->getMessage()}</strong><br /><small>{$core_exception->getFile()}@{$core_exception->getLine()}</small></p>
	<pre class="exception">{$core_exception->getTrace()|var_export:true}</pre>
	{/if}
	{if isset($core_buffer) && $core_buffer}
	<h6>Bufor</h6>
	<pre>{$core_buffer}</pre>
	{/if}
	{if isset($core_log)}
	<h6>Log</h6>
	<table class="log">
		{foreach $core_log as $log}
		<tr>
			<td>{$log.time|round|date_format:"Y-m-d H:i:s"}</td>
			<td><span class="text">{$log.message}</span><span class="info"><strong>{$log.callee.file}</strong>@{$log.callee.line}</span>
		</tr>
		{/foreach}
	</table>
	{/if}
</aside>