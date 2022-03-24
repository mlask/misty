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
	
	public static function load (): ?self
	{
		return self::$instance;
	}
	
	public function __get (string $name): mixed
	{
		return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$name]) ? $_SESSION[$name] : false;
	}
	
	public function __set (string $name, mixed $value): void
	{
		if (session_status() === PHP_SESSION_ACTIVE)
			$_SESSION[$name] = $value;
	}
	
	public function __call (string $name, array $args = null): mixed
	{
		if (session_status() === PHP_SESSION_ACTIVE && function_exists('session_' . $name))
			return call_user_func_array('session_' . $name, $args);
		return false;
	}
	
	public function __isset (string $name): bool
	{
		return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$name]);
	}
	
	public function __unset (string $name): void
	{
		if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$name]))
		{
			$_SESSION[$name] = null;
			unset($_SESSION[$name]);
		}
	}
	
	public function set (array $values): void
	{
		if (session_status() === PHP_SESSION_ACTIVE)
			foreach ($values as $name => $value)
				$_SESSION[$name] = $value;
	}
	
	public function restart (): string
	{
		session_regenerate_id(true);
		return session_id();
	}
};