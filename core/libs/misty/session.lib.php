<?php
namespace misty;
class session
{
	private static $instance = null;
	
	public function __construct ()
	{
		session_name(core::env()->uuid);
		session_start();
		self::$instance = $this;
		core::log('session started with id "%s"', session_id());
	}
	
	public static function load ()
	{
		return self::$instance;
	}
	
	public function __get ($name)
	{
		return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$name]) ? $_SESSION[$name] : false;
	}
	
	public function __set ($name, $value)
	{
		if (session_status() === PHP_SESSION_ACTIVE)
			$_SESSION[$name] = $value;
	}
	
	public function __call ($name, $args)
	{
		if (session_status() === PHP_SESSION_ACTIVE && function_exists('session_' . $name))
			return call_user_func_array('session_' . $name, $args);
		return false;
	}
	
	public function __isset ($name)
	{
		return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$name]);
	}
	
	public function __unset ($name)
	{
		if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$name]))
		{
			$_SESSION[$name] = null;
			unset($_SESSION[$name]);
		}
	}
	
	public function set (array $values)
	{
		if (session_status() === PHP_SESSION_ACTIVE)
			foreach ($values as $name => $value)
				$_SESSION[$name] = $value;
	}
	
	public function restart ()
	{
		session_regenerate_id(true);
		return session_id();
	}
};