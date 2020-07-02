<!DOCTYPE html>
<html lang="pl">
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
	<ul class="ui-nav-menu">
		{if isset($core_env->menu)}
		{function name="menu"}
		{if isset($items) && is_array($items)}
		{foreach $items as $item}
		<li{if $item->is_separator} class="is-separator"{/if}>
			{if !$item->is_separator}
			<a href{if $item->action}="{$item->action}"{/if}{if $item->badge} data-badge="{$item->badge}"{/if}>{$item->name}</a>
			{if $item->submenu}
			<ul>
				{call name="menu" items=$item->submenu}
			</ul>
			{/if}
			{/if}
		</li>
		{/foreach}
		{/if}
		{/function}
		{call name="menu" items=$core_env->menu->get()}
		{/if}
	</ul>
	{if isset($core_view.page)}
	<section class="ui-main">
		{if isset($core_view.menu)}
		<section class="ui-sidebar">
			{$core_view.menu}
		</section>
		{/if}
		<section class="ui-container">
			{if isset($core_view.page)}{$core_view.page}{/if}
		</section>
	</section>
	{/if}
	{if isset($core_view.content)}
	{$core_view.content}
	{/if}
	{if isset($core_exception)}
	{include "exception.tpl"}
	{/if}
	{if isset($core_debug) && $core_debug}
	{include "debug.tpl"}
	{/if}
	{if isset($view_assets.js)}
	{foreach $view_assets.js as $_asset_js}
	<script defer src="{$_asset_js.relpath}?{$_asset_js.ts}"></script>
	{/foreach}
	{/if}
</body>
</html>