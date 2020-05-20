<?php
namespace misty;
new class
{
	public function __construct ()
	{
		$db = db::load(core::env()->config->get('db', []));
		$session = session::load();
		
		// user object
		$user = new obj([
			'status'	=> false,
			'auth'		=> false,
			'data'		=> null,
			'form'		=> new form
		]);
		
		// auth form
		$user->form->add(
			(new form_text('auth_login'))->required(),
			(new form_text('auth_password'))->required()
		);
		
		// check user by session id
		if (!$user->auth && isset($session->id))
		{
			$auth = $db->get_row("SELECT
					BIN_TO_UUID(mu.user_id) AS `user_id`,
					BIN_TO_UUID(mur.role_id) AS `role_id`,
					mu.user_login,
					mu.user_password,
					mur.role_name
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
				$user->auth = true;
				$user->data = new obj($auth);
				$user->status = 'auth_ok';
			}
			else
			{
				$user->status = 'auth_session_invalid';
			}
			
			unset($auth);
		}
		
		// check user by credentials
		if (!$user->auth && $user->form->validate())
		{
			$auth = $db->get_row("SELECT
					BIN_TO_UUID(mu.user_id) AS `user_id`,
					BIN_TO_UUID(mur.role_id) AS `role_id`,
					mu.user_login,
					mu.user_password,
					mur.role_name
				FROM
					`m_user` mu
				JOIN
					`m_user_role` mur ON mur.role_id = mu.role_id
				WHERE
					mu.user_login = [::1]
				AND
					mu.delete_date IS NULL
				LIMIT 1",
				$user->form->auth_login__value);
			
			if (is_array($auth) && !empty($auth))
			{
				if (password_verify($user->form->auth_password__value, $auth['user_password']))
				{
					$auth['user_session_id'] = crypt(sprintf('%s:%s', microtime(true), json_encode($user)), '$5$' . core::env()->uuid);
					$session->id = $auth['user_session_id'];
					
					$db->query("UPDATE
							`m_user` mu
						SET
							mu.user_session_id = [::user_session_id]
						WHERE
							mu.user_id = UNHEX([::user_id])",
						$auth);
					
					$user->auth = true;
					$user->data = $auth;
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
			
			unset($auth);
		}
		
		// add user to core::env
		core::env()->set(['user' => $user]);
		
		// register logout action
		if ($user->auth)
		{
			core::env()->request->add_route(['/^user\/logout$/i' => function () {
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
	}
};