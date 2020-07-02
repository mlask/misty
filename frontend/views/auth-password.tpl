{if isset($core_env->user) && !$core_env->user->auth}
<section class="ui-welcome">
	<form action="{$core_env->request->self}" method="post">
		<div class="modal is-active">
			<div class="modal-background"></div>
			<div class="modal-content">
				<div class="box">
					<p class="title">
						{$i18n->_('Resetowanie hasła')}
					</p>
					{if $pwform->is_valid}
					<div class="notification is-success">
						{$i18n->_('Wiadomość z instrukcją jak zresetować hasło została wysłana na adres e-mail powiązany z kontem użytkownika.')}
					</div>
					<div class="field is-grouped">
						<p class="control">
							<a href="./" class="button is-primary">{$i18n->_('OK')}</a>
						</p>
					</div>
					{else}
					<input type="hidden" name="__form_id" value="{$pwform->id}">
					<div class="field">
						<p class="control has-icons-left">
							<input class="input{form_class form=$pwform field="auth_login" space=true}" type="email" placeholder="{$i18n->_('Adres e-mail')}" name="auth_login" value="{$pwform->auth_login__value}" required>
							<span class="icon is-left">
								<i class="mdi mdi-18px mdi-at"></i>
							</span>
						</p>
						{form_error form=$pwform field="auth_login" template="<p class=\"help is-danger\">%s</p>"}
					</div>
					<div class="field is-grouped">
						<p class="control">
							<button class="button is-primary has-text-weight-medium">
								{$i18n->_('Wyślij')}
							</button>
						</p>
						<p class="control">
							<a href="./" class="button is-text">{$i18n->_('Anuluj')}</a>
						</p>
					</div>
					{/if}
				</div>
			</div>
		</div>
	</form>
</section>
{/if}