<section class="section">
	<article class="message is-danger">
		<div class="message-header">
			<p>{$core_exception->getFile()}:{$core_exception->getLine()}</p>
		</div>
		<div class="message-body">
			<p style="margin-bottom: 1.5rem">
				<b>{$core_exception->getMessage()}</b>
			</p>
			
			{foreach $core_exception->getTrace() as $exception_trace}
			<article class="message is-small">
				<div class="message-header">{if isset($exception_trace.file)}{$exception_trace.file}:{$exception_trace.line}{else}(internal){/if}</div>
				<pre class="message-body"><b>{if isset($exception_trace.class)}{$exception_trace.class}{$exception_trace.type}{/if}{$exception_trace.function}</b>({if !empty($exception_trace.args)}<br>{foreach $exception_trace.args as $_trace_arg}	<b>{$_trace_arg|gettype}</b>{if gettype($_trace_arg) == 'resource'} {$_trace_arg|get_resource_type}{elseif gettype($_trace_arg) == 'object'} {$_trace_arg|get_class}{elseif gettype($_trace_arg) == 'array'} {$_trace_arg|json_encode}{elseif gettype($_trace_arg) == 'string'} "{$_trace_arg}"{elseif gettype($_trace_arg) == 'boolean'} {if $_trace_arg}TRUE{else}FALSE{/if}{elseif $_trace_arg !== null} {$_trace_arg}{/if},<br>{/foreach}{/if});</pre>
			</article>
			{/foreach}
		</div>
	</article>
</section>