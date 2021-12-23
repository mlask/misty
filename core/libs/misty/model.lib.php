<?php
namespace misty;
class model
{
	protected $db = null;
	private static $instance = null;
	
	public function __construct (...$args)
	{
		$this->db = db::load(core::env()->config->get('db', []));
		
		if (method_exists($this, '__init'))
			$this->__init(...$args);
	}
	
	public static function load ()
	{
		$_class = get_called_class();
		if (!isset(self::$instance[$_class]) || self::$instance[$_class] === null)
			self::$instance[$_class] = new static;
		return self::$instance[$_class];
	}
};