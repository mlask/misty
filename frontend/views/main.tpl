<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<base href="{$core.env.path.relative|rtrim:"/"}/" />
	<title>misty³</title>
	{if="$core.env.local"}
	<link rel="stylesheet/less" type="text/css" href="frontend/styles/less/app.less" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/2.2.0/less.min.js" data-env="development"></script>
	{else}
	<link rel="stylesheet" href="frontend/styles/app.css" />
	{/if}
</head>
<body data-layout="{$core.env.instance.name}">
	{if="isset($view) && $view.content"}
	<div id="app-content">
	{$view.content|safe}
	</div>
	{/if}
	{if="$core.debug"}
	{include="debug"}
	{/if}
</body>
</html>