<?php
namespace misty;
new class
{
	public function __construct ()
	{
		$session = session::load();
		
		// user object
		$user = new obj([
			'has_access'	=> function ($module, $action = null) {
				return isset(core::env()->user->acl[$module]['*']) && core::env()->user->acl[$module]['*'] === true && ($action === null || !isset(core::env()->user->acl[$module][strtr($action, '-', '_')]) || (isset(core::env()->user->acl[$module][strtr($action, '-', '_')]) && core::env()->user->acl[$module][strtr($action, '-', '_')] === true));
			},
			'status'		=> false,
			'auth'			=> false,
			'data'			=> null,
			'form'			=> new form,
			'acl'			=> null
		]);
		
		// auth form
		$user->form->add(
			form::text('auth_login')->required()->validated_by(form::validator()->email()),
			form::text('auth_password')->required()
		);
		
		// check user by session id
		if (!$user->auth && isset($session->id))
		{
			try
			{
				$db = db::load(core::env()->config->get('db', []));
				$auth = $db->get_row("SELECT
						BIN_TO_UUID(mu.user_id, 0) AS `user_id`,
						BIN_TO_UUID(mur.role_id, 0) AS `role_id`,
						mu.user_name,
						mu.user_login,
						mu.user_password,
						mur.role_name,
						mur.role_module
					FROM
						`m_user` mu
					JOIN
						`m_user_role` mur ON mur.role_id = mu.role_id
					WHERE
						mu.user_session_id = [::1]
					AND
						mu.delete_date IS NULL
					LIMIT 1",
					$session->id);
				
				if (is_array($auth) && !empty($auth))
				{
					$user->acl = $this->get_acl($db, $auth);
					$user->auth = true;
					$user->data = new obj($auth);
					$user->status = 'auth_ok';
				}
				else
				{
					$user->status = 'auth_session_invalid';
				}
			}
			catch (\exception $e)
			{
				$user->status = 'db_connection_error';
			}
			
			$auth = null;
			unset($auth);
		}
		
		// check user by credentials
		if (!$user->auth && $user->form->validate())
		{
			try
			{
				$db = db::load(core::env()->config->get('db', []));
				$auth = $db->get_row("SELECT
						BIN_TO_UUID(mu.user_id, 0) AS `user_id`,
						BIN_TO_UUID(mur.role_id, 0) AS `role_id`,
						mu.user_name,
						mu.user_login,
						mu.user_password,
						mur.role_name,
						mur.role_module
					FROM
						`m_user` mu
					JOIN
						`m_user_role` mur ON mur.role_id = mu.role_id
					WHERE
						mu.user_login = [::1]
					AND
						mu.delete_date IS NULL
					LIMIT 1",
					$user->form->get_value('auth_login'));
				
				if (is_array($auth) && !empty($auth))
				{
					if (password_verify($user->form->get_value('auth_password'), $auth['user_password']))
					{
						$auth['user_session_id'] = crypt(sprintf('%s:%s', microtime(true), json_encode($user)), '$5$' . core::env()->uuid);
						$session->id = $auth['user_session_id'];
					
						$db->query("UPDATE
								`m_user` mu
							SET
								mu.user_session_id = [::user_session_id]
							WHERE
								mu.user_id = UUID_TO_BIN([::user_id], 0)",
							$auth);
						
						$user->acl = $this->get_acl($db, $auth);
						$user->auth = true;
						$user->data = new obj($auth);
						$user->status = 'auth_ok';
					}
					else
					{
						$user->status = 'auth_invalid_password';
					}
				}
				else
				{
					$user->status = 'auth_invalid_login';
				}
			}
			catch (\exception $e)
			{
				$user->status = 'db_connection_error';
				
				// dump auth exception
				print_r($e);
			}
			
			$auth = null;
			unset($auth);
		}
		
		// add user to core::env
		core::env()->set(['user' => $user]);
		
		// process after module preload
		core::env()->after('preload', function () {
			// register logout action
			if (core::env()->user->auth)
			{
				core::env()->request->add_route(['/^user\/logout$/i' => function (& $query) {
					$query = null;
					$session = session::load();
					$request = request::load();
					if (isset($session->id) && core::env()->user->auth)
					{
						db::load(core::env()->config->get('db', []))->query("UPDATE
								`m_user` mu
							SET
								mu.user_session_id = NULL
							WHERE
								mu.user_session_id = [::1]
							AND
								mu.delete_date IS NULL",
							$session->id);
						unset($session->id);
					}
					$request->redirect($request->get('redir', ''));
				}]);
			}
			else
			{
				view::load()->render('{auth}auth-login.tpl');
				core::env()->request->add_route(['/^user\/password$/i' => function (& $query) {
					$query = null;
				
					$pwform = form::create(
						form::text('auth_login')->required()->validated_by(function (& $field) {
							$field->has_error(i18n::load()->_('Podany adres e-mail jest nieprawidłowy'));
							return false;
						})
					)->on_sent(function (& $form) {
						if ($form->is_valid)
						{
							$_user = new user_model;
							$_user->reset_password($form->get_value('auth_login'));
						}
					});
					
					view::load()->assign('pwform', $pwform);
					view::load()->render('{auth}auth-password.tpl');
				}]);
			}
		});
	}
	
	private function get_acl (\misty\db $db, array & $auth): ?array
	{
		$uacl = $db->get_array("SELECT
				mua.acl_module,
				mua.acl_action,
				IF(mua.acl_type = 'ALLOW', 1, 0) AS `allow`,
				IF(mua.acl_action IS NOT NULL, 1, 0) + IF(mua.user_id IS NOT NULL, 2, 0) AS `priority`
			FROM
				`m_user_acl` mua
			WHERE
				mua.role_id = UUID_TO_BIN([::1], 0)
			AND
				(mua.user_id IS NULL OR mua.user_id = UUID_TO_BIN([::2], 0))
			ORDER BY
				`priority` ASC",
			$auth['role_id'],
			$auth['user_id']);
		
		if (is_array($uacl) && !empty($uacl))
		{
			$acl = [];
			foreach ($uacl as $uacl_item)
			{
				if (!isset($acl[$uacl_item['acl_module']]))
					$acl[$uacl_item['acl_module']]['*'] = isset($uacl['acl_action']) ? (bool)$uacl_item['allow'] : true;
				
				if (isset($uacl_item['acl_action']))
					$acl[$uacl_item['acl_module']][$uacl_item['acl_action']] = (bool)$uacl_item['allow'];
			}
			return $acl;
		}
		
		return null;
	}
};