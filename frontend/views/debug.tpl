<footer class="debug">
	<div class="content">
		{if isset($core_buffer) && $core_buffer}
		<p class="header">{$i18n->_('Zawartość bufora')}</p>
		<pre class="buffer">{$core_buffer}</pre>
		{/if}
		{if isset($core_log)}
		<p class="header">{$i18n->_('Komunikaty')}</p>
		<table class="log">
			{foreach $core_log as $log}
			<tr>
				<td>{$log.time|round|date_format:"Y-m-d H:i:s"}<br /><b>{if $log.memory >= 0}+{else}-{/if}{"%0.2f"|sprintf:($log.memory/1024)} kB</b></td>
				<td><code class="text">{$log.message}</code><span class="info"><b>{$log.source.file}</b>@{$log.source.line}</span>
			</tr>
			{/foreach}
		</table>
		{/if}
		<p class="header">{$i18n->_('Status')}</p>
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