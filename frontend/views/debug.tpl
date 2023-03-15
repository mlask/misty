<footer class="debug">
	<div class="content">
		{if isset($core_buffer) && $core_buffer}
		<h6>{$i18n->_('Zawartość bufora')}</h6>
		<pre class="buffer">{$core_buffer}</pre>
		{/if}
		{if isset($core_log)}
		<h6>{$i18n->_('Komunikaty')}</h6>
		<table class="log">
			{foreach $core_log as $log}
			<tr>
				<td>{$log.time|round|date_format:"Y-m-d H:i:s"}<br /><b>{if $log.memory >= 0}+{else}-{/if}{"%0.2f"|sprintf:($log.memory/1024)} kB</b></td>
				<td><code class="text">{$log.message}</code><span class="info"><b>{$log.source.file}</b>@{$log.source.line}</span>
			</tr>
			{/foreach}
		</table>
		{/if}
		<h6>{$i18n->_('Status')}</h6>
		<table class="log">
			<tr>
				<td>mem</td>
				<td><code class="text">{"%0.2f kB"|sprintf:(memory_get_usage(false)/1024)}</code></span>
			</tr>
			<tr>
				<td>peak mem</td>
				<td><code class="text">{"%0.2f kB"|sprintf:(memory_get_peak_usage(false)/1024)}</code></span>
			</tr>
		</table>
	</div>
</footer>