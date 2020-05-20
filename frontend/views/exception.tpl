<section class="section">
	<article class="message is-exception">
		<div class="message-header">
			<div class="icon"><i class="mdi mdi-24px mdi-alert-octagram"></i></div>
			<div class="text">
				{$core_exception->getMessage()}
				<div class="path">
					{$core_exception->getFile()|dirname}/<b>{$core_exception->getFile()|basename}</b>:{$core_exception->getLine()}
				</div>
			</div>
		</div>
		<div class="message-body">
			{foreach $core_exception->getTrace() as $exception_trace}
			<details class="collapsible">
				<summary>
					<div class="text">{if isset($exception_trace.class)}{$exception_trace.class}{$exception_trace.type|replace:"->":"→"}{/if}<b>{$exception_trace.function}</b>({if !empty($exception_trace.args)}&hellip;{/if});</div>
					<div class="path">{if isset($exception_trace.file)}{$exception_trace.file|dirname}/<b>{$exception_trace.file|basename}</b>:{$exception_trace.line}{else}(internal){/if}</div>
				</summary>
				<pre class="details">{$exception_trace|print_r:true}</pre>
			</details>
			{/foreach}
		</div>
	</article>
</section>