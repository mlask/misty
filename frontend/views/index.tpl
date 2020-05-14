<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<base href="{$core_env->path->relative|rtrim:"/"}/" />
	<title>misty³</title>
	{if $core_env->local}
	<link rel="stylesheet/less" type="text/css" href="frontend/styles/less/app.less" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/2.2.0/less.min.js" data-env="development"></script>
	{else}
	<link rel="stylesheet" href="frontend/styles/app.css" />
	{/if}
</head>
<body data-layout="{$core_env->instance->name}">
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