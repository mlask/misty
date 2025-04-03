<!DOCTYPE html>
<html lang="pl" data-theme="lighty">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<base href="{$core_env->path->relative|rtrim:"/"}/" />
	<title>misty³</title>
	<link rel="stylesheet" href="frontend/styles/app.css?{$smarty.now}" />
	{if isset($view_assets.css)}
	{foreach $view_assets.css as $_asset_css}
	<link rel="stylesheet" href="{$_asset_css.relpath}?{$_asset_css.ts}" />
	{/foreach}
	{/if}
</head>
<body data-layout="{if isset($core_env->instance)}{$core_env->instance->name}{/if}">
	{if isset($core_env->menu)}
	{include "navbar.tpl"}
	{/if}
	<div class="columns">
		{if isset($core_view.menu)}
		<div class="column is-one-fifth">
			<section class="section">
				<aside class="menu">
				{$core_view.menu}
				</aside>
			</section>
		</div>
		{/if}
		{if isset($core_view.page)}
		<div class="column">
			<section class="section">
				{$core_view.page}
			</section>
		</div>
		{/if}
	</div>
	{if isset($core_view.content)}
	{$core_view.content}
	{/if}
	{if isset($core_exception)}
	{include "exception.tpl"}
	{/if}
	{if isset($core_debug) && $core_debug}
	{include "debug.tpl"}
	{/if}
	<script defer src="frontend/js/core.js"></script>
	{if isset($view_assets.js)}
	{foreach $view_assets.js as $_asset_js}
	<script defer src="{$_asset_js.relpath}?{$_asset_js.ts}"></script>
	{/foreach}
	{/if}
</body>
</html>