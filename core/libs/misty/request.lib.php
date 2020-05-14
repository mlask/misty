<?php
namespace misty;
class request
{
	const TYPE_BOOL = 0;
	const TYPE_INT = 1;
	const TYPE_ARRAY = 2;
	
	private $req = null;
	private $routes = null;
	
	public function __construct ()
	{
		$this->__process();
	}
	
	public function __call ($action, array $params = null)
	{
		switch (strtolower($action))
		{
			case 'param':
			{
				if (!empty($params))
					return isset($this->req['call_nparam'][$params[0]]) ? $this->req['call_nparam'][$params[0]] : (isset($this->req['call_params'][$params[0]]) ? $this->req['call_params'][$params[0]] : (isset($params[1]) ? $params[1] : null));
				break;
			}
			case 'params':
			{
				$_o = null;
				if (!empty($params))
				{
					foreach ($params as $_p)
					{
						if (is_array($_p))
						{
							foreach ($_p as $_pk => $_pd)
								$_o[$_pk] = isset($this->req['call_nparam'][$_pk]) ? $this->req['call_nparam'][$_pk] : (isset($this->req['call_params'][$_pk]) ? $this->req['call_params'][$_pk] : $_pd);
						}
						else
						{
							$_o[$_p] = isset($this->req['call_nparam'][$_p]) ? $this->req['call_nparam'][$_p] : (isset($this->req['call_params'][$_p]) ? $this->req['call_params'][$_p] : null);
						}
					}
				}
				return $_o;
				break;
			}
			case 'getd':
			case 'postd':
			case 'getpostd':
			{
				if (!empty($params))
				{
					$_o = isset($this->req[substr($action, 0, -1)][$params[0]]) ? $this->req[substr($action, 0, -1)][$params[0]] : (isset($params[1]) ? $params[1] : null);
					if ($_o !== null && isset($params[2]) && $params[2] !== null)
					{
						switch ($params[2])
						{
							case self::TYPE_BOOL:
								$_o = preg_match('/^(true|yes|y)$/i', $_o) ? true : (preg_match('/^(false|no|n)$/i', $_o) ? false : (bool)(int)$_o);
								break;
							
							case self::TYPE_INT:
								$_o = (int)$_o;
								break;
							
							case self::TYPE_ARRAY:
								$_o = explode(',', preg_replace('/\s+/', '', $_o));
								break;
						}
					}
					return $_o;
				}
				break;
			}
			case 'get':
			case 'post':
			case 'getpost':
			{
				$_o = null;
				if (!empty($params))
				{
					foreach ($params as $_p)
					{
						if (is_array($_p))
						{
							foreach ($_p as $_pk => $_pd)
								$_o[$_pk] = isset($this->req[$action][$_pk]) ? $this->req[$action][$_pk] : $_pd;
						}
						else
						{
							$_o[$_p] = isset($this->req[$action][$_p]) ? $this->req[$action][$_p] : null;
						}
					}
				}
				return $_o;
				break;
			}
			default:
			{
				if (isset($this->req['call_' . $action]))
					return $this->req['call_' . $action];
				elseif (!empty($params))
					return array_shift($params);
			}
		}
		return null;
	}
	
	public function __get ($param)
	{
		if ($param == 'self')
			return $this->req['self'];
		elseif ($param == 'xhr')
			return $this->req['xhr'];
		if (isset($this->req['get'][$param]))
			return $this->req['get'][$param];
		return null;
	}
	
	public function __isset ($param)
	{
		return isset($this->req['get'][$param]);
	}
	
	public function _redirect ($addr = '')
	{
		while (ob_get_level())
			ob_end_clean();
		
		session_write_close();
		header('Location: ' . ($addr === true ? (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI']) : (preg_match('/^http[s?]/', $addr) ? $addr : rtrim(core::env()->path->relative, '/') . '/' . ltrim($addr, '/'))));
		exit;
	}
	
	public function _add_route (array $route)
	{
		$this->routes = array_merge((array)$this->routes, $route);
		$this->__process();
	}
	
	public function _set (array $reqdata)
	{
		$this->req = array_merge($this->req, $reqdata);
	}
	
	private function __process ()
	{
		// przygotowanie danych źródłowych
		if (core::env()->cli)
		{
			$self = array_shift($_SERVER['argv']);
			$qs = [implode('/', $_SERVER['argv'])];
		}
		else
		{
			$self = $_SERVER['REQUEST_URI'];
			$qs = explode('?', $_SERVER['REQUEST_URI'], 3);
			if (!empty($qs))
				$qs[0] = substr(urldecode($qs[0]), strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/')) + 1);
		}
		
		// zapisanie podstawowego requestu
		$this->req = [
			'self'		=> $self,
			'query'		=> !empty($qs) ? array_shift($qs) : null,
			'params'	=> !empty($qs) ? array_shift($qs) : null,
			'get'		=> null,
			'post'		=> null,
			'getpost'	=> null,
			'xhr'		=> isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
		];
		
		// zastosowanie routerów
		if (!empty($this->routes))
		{
			core::log(sprintf('%d route(s) found', count($this->routes)));
			foreach ($this->routes as $r_from => $r_to)
			{
				if (preg_match($r_from, $this->req['query']))
					$this->req['query'] = preg_replace($r_from, $r_to, $this->req['query']);
			}
		}
		
		// moduł, akcja
		if ($this->req['query'] !== null && strlen($this->req['query']) > 0)
		{
			$this->req['call'] = explode('/', trim($this->req['query'], '\/'));
			$this->req['call_module'] = !empty($this->req['call']) && preg_match('/^[a-z0-9_-]+$/i', $this->req['call'][0]) ? str_replace('-', '_', array_shift($this->req['call'])) : null;
			$this->req['call_action'] = !empty($this->req['call']) && preg_match('/^[a-z0-9_-]+$/i', $this->req['call'][0]) ? str_replace('-', '_', array_shift($this->req['call'])) : null;
			$this->req['call_params'] = !empty($this->req['call']) ? $this->req['call'] : [];
			$this->req['call_nparam'] = [];
				
			foreach ($this->req['call_params'] as $_pi => $_pp)
			{
				if (preg_match('/^([a-z0-9_]+?):(.+?)$/', $_pp, $_pm))
				{
					$this->req['call_nparam'][$_pm[1]] = $_pm[2];
					unset($this->req['call_params'][$_pi]);
				}
			}
			
			$this->req['call_params'] = array_values($this->req['call_params']);
			$this->req['call'] = null;
			unset($this->req['call']);
		}
		
		// parametry (get, post)
		if ($this->req['params'] !== null)
			parse_str($this->req['params'], $this->req['get']);
		if (isset($_POST) && !empty($_POST))
			foreach ($_POST as $_pk => $_pv)
				$this->req['post'][$_pk] = $_pv;
		if (is_array($this->req['post']))
			foreach ($this->req['post'] as $_pk => $_pv)
				$this->req['getpost'][$_pk] = $_pv;
		if (is_array($this->req['get']))
			foreach ($this->req['get'] as $_gk => $_gv)
				$this->req['getpost'][$_gk] = $_gv;
		
		// porządki
		$qs = null;
		unset($qs);
	}
}