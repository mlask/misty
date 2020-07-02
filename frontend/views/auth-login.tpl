{if isset($core_env->user) && !$core_env->user->auth && (!isset($core_env->instance) || $core_env->instance->need_auth)}
<section class="ui-welcome">
	<form action="{$core_env->request->self}" method="post">
		<div class="modal is-active">
			<div class="modal-background"></div>
			<div class="modal-content">
				<div class="box">
					<p class="title">
						{$i18n->_('Logowanie')}
					</p>
					{if !$core_env->user->auth && $core_env->user->status}
					<div class="notification is-danger" data-status="{$core_env->user->status}">
						{if $core_env->user->status === 'auth_session_invalid'}{$i18n->_('Sesja użytkownika wygasła. Zaloguj się ponownie.')}
						{elseif $core_env->user->status === 'db_connection_error'}{$i18n->_('Błąd połączenia z bazą danych. Spróbuj ponownie za kilka minut.')}
						{else}{$i18n->_('Nieprawidłowe dane logowania.')}{/if}
					</div>
					{/if}
					<input type="hidden" name="__form_id" value="{$core_env->user->form->id}">
					<div class="field">
						<p class="control has-icons-left">
							<input class="input{form_class form=$core_env->user->form field="auth_login" space=true}" type="email" placeholder="{$i18n->_('Adres e-mail')}" name="auth_login" value="{$core_env->user->form->auth_login__value}" autocomplete="username" required>
							<span class="icon is-left">
								<i class="mdi mdi-18px mdi-at"></i>
							</span>
						</p>
						{form_error form=$core_env->user->form field="auth_login" template="<p class=\"help is-danger\">%s</p>"}
					</div>
					<div class="field">
						<p class="control has-icons-left">
							<input class="input{form_class form=$core_env->user->form field="auth_password" space=true}" type="password" placeholder="{$i18n->_('Hasło')}" name="auth_password" value="" autocomplete="current-password" required>
							<span class="icon is-left">
								<i class="mdi mdi-18px mdi-lock"></i>
							</span>
						</p>
						{form_error form=$core_env->user->form field="auth_password" template="<p class=\"help is-danger\">%s</p>"}
					</div>
					<div class="field is-grouped">
						<p class="control">
							<button class="button is-primary has-text-weight-medium">
								{$i18n->_('Zaloguj się')}
							</button>
						</p>
						<p class="control">
							<a href="./user/password" class="button is-text">{$i18n->_('Nie pamiętam hasła')}</a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</form>
</section>
{/if}