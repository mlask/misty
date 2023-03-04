{function name="menu"}
{if isset($items) && is_array($items)}
	{foreach $items as $item}
		{if $item->is_reparator}
		<hr class="navbar-divider">
		{elseif $item->submenu}
		<div class="navbar-item has-dropdown is-hoverable">
			<a class="navbar-link"{if $item->action} href="{$item->action}"{/if}{if $item->badge} data-badge="{$item->badge}"{/if}>
				{$item->name}
			</a>
			<div class="navbar-dropdown">
				{call name="menu" items=$item->submenu}
			</div>
		</div>
		{else}
		<a class="navbar-item"{if $item->action} href="{$item->action}"{/if}{if $item->badge} data-badge="{$item->badge}"{/if}>
			{$item->name}
		</a>
		{/if}
	{/foreach}
{/if}
{/function}
<nav class="navbar is-light is-size-5" role="navigation" aria-label="main navigation">
	<div class="navbar-brand">
		<div class="navbar-item has-text-weight-semibold has-text-primary">
			misty³
		</div>
		<a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
		</a>
	</div>
	<div class="navbar-menu">
	{call name="menu" items=$core_env->menu->get()}
	</div>
	<div class="navbar-end">
		{if isset($core_env->user) && $core_env->user->auth}
		<div class="navbar-item has-dropdown">
			<a class="navbar-link">
				<b>{$core_env->user->data->user_name}</b> ({$core_env->user->data->role_name})<br>
				{$core_env->user->data->user_login}
			</a>
			<div class="navbar-dropdown">
				{if $core_env->user->has_access('settings', 'user')}
				<a class="navbar-item" href="settings/user?redir={$core_env->request->self|urlencode}">
					{$i18n->_('Ustawienia')}
				</a>
				{/if}
				<a class="navbar-item" href="user/logout">
					{$i18n->_('Wylogowanie')}
				</a>
			</div>
		</div>
		{else}
		<div class="navbar-item">
			<a class="button is-primary" href="./">
				<span class="icon">
					<i class="mdi mdi-login"></i>
				</span>
				<span>{$i18n->_('Logowanie')}</span>
			</a>
		</div>
		{/if}
	</div>
</nav>