<h1 class="title">{$core_env->instance->name}</h1>
<h2 class="subtitle">{$core_env->instance->action} &middot; {$core_env->request->params()|json_encode}</h2>

{if isset($core_env->user) && $core_env->user->status}
<div class="notification is-{if isset($core_env->user) && $core_env->user->auth}success{else}warning{/if}">
	{$translate->_s('Authorization status: <b>%s</b>', $core_env->user->status)}
</div>
{/if}

{if isset($core_env->user)}
{if !$core_env->user->auth}
<form action="{$core_env->request->self}" method="post">
	<input type="hidden" name="__form_id" value="{$core_env->user->form->id}">
	<div class="field">
		<p class="control has-icons-left">
			<input class="input{if $core_env->user->form->is_sent && !$core_env->user->form->auth_login__valid} is-danger{/if}" type="text" placeholder="{$translate->_('Login')}" name="auth_login" value="{$core_env->user->form->auth_login__value}">
			<span class="icon is-small is-left">
				<i class="mdi mdi-account"></i>
			</span>
		</p>
		{if $core_env->user->form->is_sent && !$core_env->user->form->auth_login__valid}
		<p class="help is-danger">
			{$translate->_('This field is required')}
		</p>
		{/if}
	</div>
	<div class="field">
		<p class="control has-icons-left">
			<input class="input{if $core_env->user->form->is_sent && !$core_env->user->form->auth_password__valid} is-danger{/if}" type="password" placeholder="{$translate->_('Password')}" name="auth_password" value="">
			<span class="icon is-small is-left">
				<i class="mdi mdi-lock"></i>
			</span>
		</p>
		{if $core_env->user->form->is_sent && !$core_env->user->form->auth_password__valid}
		<p class="help is-danger">
			{$translate->_('This field is required')}
		</p>
		{/if}
	</div>
	<div class="field">
		<p class="control">
			<button class="button is-success">
				{$translate->_('Login')}
			</button>
		</p>
	</div>
</form>
{else}
<p>
	{$translate->_s('User ID: <b>%s</b>', $core_env->user->data->user_id)}
</p>
<p>
	<a href="user/logout">{$translate->_('Logout')}</a><br />
	<a href="user/logout?redir=page/test.html">{$translate->_('Logout with redirection')}</a>
</p>
{/if}
{/if}