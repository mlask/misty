<?php
namespace misty;
class request
{
	const TYPE_INT = 0;
	const TYPE_BOOL = 1;
	const TYPE_ARRAY = 2;
	
	public $xhr = null;
	public $self = null;
	private $routes = null;
	private $req_get = null;
	private $req_both = null;
	private $req_call = null;
	private $req_post = null;
	
	public function __construct ()
	{
		$this->_process();
	}
	
	public function __get ($name)
	{
		return isset($this->req_get[$name]) ? $this->req_get[$name] : null;
	}
	
	public function __call ($name, array $args = null)
	{
		return isset($this->req_call->{$name}) && !is_object($this->req_call->{$name}) ? $this->req_call->{$name} : (!empty($args) ? array_shift($args) : null);
	}
	
	public function __isset ($name)
	{
		return isset($this->req_get[$name]);
	}
	
	public function any (...$args)
	{
		return $this->_get_req('both', ...$args);
	}
	
	public function get (...$args)
	{
		return $this->_get_req('get', ...$args);
	}
	
	public function post (...$args)
	{
		return $this->_get_req('post', ...$args);
	}
	
	public function anyd (...$args)
	{
		return $this->_get_req_d('both', ...$args);
	}
	
	public function getd (...$args)
	{
		return $this->_get_req_d('get', ...$args);
	}
	
	public function postd (...$args)
	{
		return $this->_get_req_d('post', ...$args);
	}
	
	public function param ($name, $default = null)
	{
		return isset($this->req_call->nparam->{$name}) ? $this->req_call->nparam->{$name} : (isset($this->req_call->params->{$name}) ? $this->req_call->params->{$name} : $default);
	}
	
	public function params (...$args)
	{
		$output = null;
		if (is_array($args) && !empty($args))
		{
			foreach ($args as $arg)
				$output[$name] = is_array($arg) ? $this->param(array_shift($arg), array_shift($arg)) : $this->param($arg);
		}
		else
		{
			$output = [
				'_by_idx'	=> $this->req_call->params->get(),
				'_by_name'	=> $this->req_call->nparam->get()
			];
		}
		return $output;
	}
	
	public function redirect ($addr = '')
	{
		while (ob_get_level())
			ob_end_clean();
		
		session_write_close();
		header('Location: ' . ($addr === true ? (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI']) : (preg_match('/^http[s?]/', $addr) ? $addr : rtrim(core::env()->path->relative, '/') . '/' . ltrim($addr, '/'))));
		exit;
	}
	
	public function add_route (array $route)
	{
		$this->routes = array_merge((array)$this->routes, $route);
		$this->_process();
	}
	
	private function _process ()
	{
		// request arguments
		if (core::env()->cli)
		{
			$this->self = array_shift($_SERVER['argv']);
			$query_args = [implode('/', $_SERVER['argv'])];
		}
		else
		{
			$this->self = $_SERVER['REQUEST_URI'];
			$query_args = explode('?', $_SERVER['REQUEST_URI'], 3);
			if (!empty($query_args))
				$query_args[0] = substr(urldecode($query_args[0]), strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/')) + 1);
		}
		
		// xmlhttprequest
		$this->xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
		
		// request
		$this->query = !empty($query_args) ? array_shift($query_args) : null;
		$this->params = !empty($query_args) ? array_shift($query_args) : null;
		
		// routes
		if (is_array($this->routes) && !empty($this->routes))
		{
			core::log('%d route(s) found', count($this->routes));
			foreach ($this->routes as $r_from => $r_to)
			{
				if (preg_match($r_from, $this->query))
					$this->query = preg_replace($r_from, $r_to, $this->query);
			}
		}
		
		// module, action, params...
		if ($this->query !== null && strlen($this->query) > 0)
		{
			$call = explode('/', trim($this->query, '\/'));
			$this->req_call = new obj([
				'module'	=> !empty($call) && preg_match('/^[a-z0-9_-]+$/i', $call[0]) ? str_replace('-', '_', array_shift($call)) : null,
				'action'	=> !empty($call) && preg_match('/^[a-z0-9_-]+$/i', $call[0]) ? str_replace('-', '_', array_shift($call)) : null,
				'params'	=> new obj(!empty($call) ? $call : []),
				'nparam'	=> new obj
			]);
			
			foreach ($this->req_call->params->get() as $_pi => $_pp)
			{
				if (preg_match('/^([a-z0-9_]+?):(.+?)$/', $_pp, $_pm))
				{
					$this->req_call->nparam->{$_pm[1]}	 = $_pm[2];
					unset($this->req_call->params->{$_pi});
				}
			}
			
			$this->req_call->params->reindex();
		}
		else
		{
			$this->req_call = new obj([
				'module'	=> null,
				'action'	=> null,
				'params'	=> new obj,
				'nparam'	=> new obj
			]);
		}
		
		// request parameters
		if ($this->params !== null)
		{
			parse_str($this->params, $this->req_get);
			$this->req_both = $this->req_get;
		}
		if (isset($_POST) && !empty($_POST))
		{
			foreach ($_POST as $_pk => $_pv)
			{
				$this->req_post[$_pk] = $_pv;
				$this->req_both[$_pk] = $_pv;
			}
		}
		
		// porządki
		$qs = null;
		unset($qs);
	}
	
	private function _get_req ($type, ...$args)
	{
		$output = null;
		if (is_array($args) && !empty($args))
		{
			foreach ($args as $arg)
			{
				$arg_name = is_array($arg) ? array_shift($arg) : $arg;
				$arg_def = is_array($arg) && !empty($arg) ? array_shift($arg) : null;
				
				$output[$name] = isset($this->{'req_' . $type}[$arg_name]) ? $this->{'req_' . $type}[$arg_name] : $arg_def;
			}
		}
		return $output;
	}
	
	private function _get_req_d ($type, ...$args)
	{
		if (is_array($args) && !empty($args))
		{
			$arg_name = array_shift($args);
			$arg_def = !empty($args) ? array_shift($args) : null;
			$arg_fmt = !empty($args) ? array_shift($args) : null;
			
			$value = isset($this->{'req_' . $type}[$arg_name]) ? $this->{'req_' . $type}[$arg_name] : $arg_def;
			if ($value !== null && $arg_fmt !== null)
			{
				switch ($arg_fmt)
				{
					case self::TYPE_INT:
					{
						$value = (int)$value;
						break;
					}
					case self::TYPE_BOOL:
					{
						$value = preg_match('/^(true|yes|y)$/i', $value) ? true : (preg_match('/^(false|no|n)$/i', $value) ? false : (bool)(int)$value);
						break;
					}
					case self::TYPE_ARRAY:
					{
						$value = explode(',', preg_replace('/\s+/', '', $value));
						break;
					}
				}
			}
			return $value;
		}
		return null;
	}
}