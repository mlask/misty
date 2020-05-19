<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<base href="{$core_env->path->relative|rtrim:"/"}/" />
	<title>misty³</title>
	<link rel="stylesheet" href="frontend/styles/app.css?{$smarty.now}" />
</head>
<body data-layout="{if isset($core_env->instance)}{$core_env->instance->name}{/if}">
	{if isset($core_view.content)}
    <section class="section">
		<div class="container">
			{$core_view.content}
		</div>
	</section>
	{/if}
	{if isset($core_exception)}
	{include "exception.tpl"}
	{/if}
	{if $core_debug}
	{include "debug.tpl"}
	{/if}
</body>
</html>