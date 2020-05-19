<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<base href="{$core_env->path->relative|rtrim:"/"}/" />
	<title>misty³</title>
	<link rel="stylesheet" href="frontend/styles/app.css" />
</head>
<body data-layout="{if isset($core_env->instance)}{$core_env->instance->name}{/if}">
	{if isset($core_view.content)}
	<div id="app-content">
	{$core_view.content}
	</div>
	{/if}
	{if $core_debug}
	{include "debug.tpl"}
	{/if}
</body>
</html>