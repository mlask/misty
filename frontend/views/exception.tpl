<section class="section">
	<article class="message is-exception">
		<div class="message-header">
			<b>{$core_exception->getMessage()}</b>
			<i>{$core_exception->getFile()|dirname}/<b>{$core_exception->getFile()|basename}</b>:{$core_exception->getLine()}</i>
		</div>
		<div class="message-body">
			{foreach $core_exception->getTrace() as $exception_trace}
			<details class="collapsible">
				<summary>
					{if isset($exception_trace.file)}{$exception_trace.file|dirname}/<b>{$exception_trace.file|basename}</b>:{$exception_trace.line}{else}(internal){/if}
				</summary>
				<pre><b>{if isset($exception_trace.class)}{$exception_trace.class}{$exception_trace.type}{/if}{$exception_trace.function}</b>({if !empty($exception_trace.args)}<br>{foreach $exception_trace.args as $_trace_arg}	<b>{$_trace_arg|gettype}</b>{if gettype($_trace_arg) == 'resource'} {$_trace_arg|get_resource_type}{elseif gettype($_trace_arg) == 'object'} {$_trace_arg|get_class}{elseif gettype($_trace_arg) == 'array'} {$_trace_arg|json_encode}{elseif gettype($_trace_arg) == 'string'} "{$_trace_arg}"{elseif gettype($_trace_arg) == 'boolean'} {if $_trace_arg}TRUE{else}FALSE{/if}{elseif $_trace_arg !== null} {$_trace_arg}{/if},<br>{/foreach}{/if});</pre>
				<pre class="details">{$exception_trace|print_r:true}</pre>
			</details>
			{/foreach}
		</div>
	</article>
</section>