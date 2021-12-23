<?php
namespace misty;
class request
{
	const TYPE_INT = 0;
	const TYPE_BOOL = 1;
	const TYPE_ARRAY = 2;
	
	public $xhr = null;
	public $self = null;
	public $query = null;
	public $params = null;
	public $callback = null;
	private $routes = null;
	private $req_get = null;
	private $req_both = null;
	private $req_call = null;
	private $req_post = null;
	private $processing = true;
	private static $instance = null;
	
	public function __construct ()
	{
		self::$instance = $this;
		$this->_process();
	}
	
	public static function load ()
	{
		return self::$instance;
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
	
	public function sent ($type, ...$args)
	{
		foreach ($args as $name)
			if (!isset($this->{'req_' . strtolower($type)}[$name]))
				return false;
		return true;
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
	
	public function replace (array $params = null, array $call = null)
	{
		if ((is_array($params) && !empty($params)) || (is_array($call) && !empty($call)))
		{
			$query = null;
			$replaced = false;
			
			// src module
			if (isset($call['module']))
				$query[] = $call['module'];
			elseif ($this->req_call->module !== null)
				$query[] = $this->req_call->module;
			elseif (isset(core::env()->instance))
				$query[] = core::env()->instance->name;
			
			// src action
			if (isset($call['action']))
				$query[] = $call['action'];
			elseif ($this->req_call->action !== null)
				$query[] = $this->req_call->action;
			elseif (isset(core::env()->instance) && !core::env()->instance->default)
				$query[] = core::env()->instance->action;
				
			// src params
			if (is_iterable($this->req_call->iparam))
			{
				foreach ($this->req_call->iparam as $param)
				{
					if (isset($params[$param->name]) && $params[$param->name] === false)
						continue;
					
					$query[] = isset($params[$param->name]) ? sprintf('%s:%s', $param->name, $params[$param->name]) : $param->raw;
					if (isset($params[$param->name]))
						unset($params[$param->name]);
				}
			}
			
			// dst params
			if (!empty($params))
				foreach ($params as $_name => $_value)
					if ($_value !== false)
						$query[] = sprintf('%s:%s', $_name, $_value);
			
			return strlen($this->query) > 0 ? str_replace($this->query, implode('/', $query), $this->self) : implode('/', $query);
		}
		return $this->self;
	}
	
	public function redirect ($addr = '')
	{
		while (ob_get_level())
			ob_end_clean();
		
		core::env()->session->commit();
		header('Location: ' . ($addr === true ? (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI']) : (preg_match('/^http[s?]:/', $addr) ? $addr : (strlen($addr) > 0 && $addr[0] === '/' ? $addr : rtrim(core::env()->path->relative, '/') . '/' . ltrim($addr, '/')))));
		exit;
	}
	
	public function add_route (array $route)
	{
		$this->routes = array_merge((array)$this->routes, $route);
		foreach ($route as $r_from => $r_to)
			core::log('added route %s => %s', $r_from, is_callable($r_to) || $r_to instanceof \Closure ? '(function)' : $r_to);
		$this->_process();
	}
	
	public function get_input ()
	{
		return file_get_contents('php://input');
	}
	
	public function is_method (...$methods)
	{
		return isset($_SERVER['REQUEST_METHOD']) ? in_array(strtoupper($_SERVER['REQUEST_METHOD']), array_map(function ($item) { return strtoupper($item); }, $methods)) : false;
	}
	
	public function get_method ()
	{
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
	}
	
	public function processing ($toggle = true)
	{
		$this->processing = $toggle;
		if ($toggle)
			$this->_process();
	}
	
	private function _process ()
	{
		if (!$this->processing)
			return;
		
		core::log('processing request');
		
		// request arguments
		if (core::env()->cli)
		{
			$argv = $_SERVER['argv'];
			$nparam = null;
			
			$this->self = array_shift($argv);
			foreach ($argv as $aidx => $arg)
			{
				if (preg_match('/^--?(\w+?)(=(.+))?$/', $arg, $argm))
				{
					$nparam[$argm[1]] = isset($argm[3]) ? $argm[3] : true;
					unset($argv[$aidx]);
				}
			}
			
			$query_args = [implode('/', $argv)];
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
		$this->callback = null;
		
		// routes
		if (is_array($this->routes) && !empty($this->routes))
		{
			foreach ($this->routes as $r_from => $r_to)
			{
				if (preg_match($r_from, $this->query))
				{
					if (is_callable($r_to) || $r_to instanceof \Closure)
						$this->callback = call_user_func_array($r_to, [&$this->query]);
					else
						$this->query = preg_replace($r_from, $r_to, $this->query);
				}
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
				'nparam'	=> new obj,
				'iparam'	=> new \ArrayObject
			]);
				
			foreach ($this->req_call->params->get() as $_pi => $_pp)
			{
				if (preg_match('/^([a-z0-9_]+?):(.+?)$/', $_pp, $_pm))
				{
					$this->req_call->iparam[(int)$_pi] = new obj(['name' => $_pm[1], 'value' => $_pm[2], 'index' => $_pi, 'raw' => $_pp]);
					$this->req_call->nparam->{$_pm[1]} = $_pm[2];
					unset($this->req_call->params->{$_pi});
				}
				else
					$this->req_call->iparam[(int)$_pi] = new obj(['name' => $_pp, 'value' => null, 'index' => $_pi, 'raw' => $_pp]);
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
		
		// cli named params
		if (core::env()->cli && isset($nparam))
			$this->req_call->nparam = new obj($nparam);
		
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
		if (!empty($args))
		{
			$arg_name = array_shift($args);
			$arg_def = !empty($args) ? array_shift($args) : null;
			return isset($this->{'req_' . strtolower($type)}[$arg_name]) ? $this->{'req_' . strtolower($type)}[$arg_name] : $arg_def;
		}
		return null;
	}
	
	private function _get_req_d ($type, ...$args)
	{
		if (is_array($args) && !empty($args))
		{
			$arg_name = array_shift($args);
			$arg_def = !empty($args) ? array_shift($args) : null;
			$arg_fmt = !empty($args) ? array_shift($args) : null;
			
			$value = isset($this->{'req_' . strtolower($type)}[$arg_name]) ? $this->{'req_' . strtolower($type)}[$arg_name] : $arg_def;
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
};