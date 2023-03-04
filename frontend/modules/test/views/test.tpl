<h1 class="title">{$core_env->instance->name}</h1>
<h2 class="subtitle">{$core_env->instance->action} &middot; {$core_env->request->params()|json_encode}</h2>

{if isset($core_env->user) && $core_env->user->status}
<div class="notification is-{if isset($core_env->user) && $core_env->user->auth}success{else}warning{/if}">
	{$i18n->_s('Authorization status: <b>%s</b>', $core_env->user->status)}
</div>
{/if}

{if isset($core_env->user) && $core_env->user->auth}
<p>
	{$i18n->_s('User ID: <b>%s</b>', $core_env->user->data->user_id)}
</p>
<p>
	<a href="user/logout">{$i18n->_('Logout')}</a><br />
	<a href="user/logout?redir=page/test.html">{$i18n->_('Logout with redirection')}</a>
</p>
{/if}