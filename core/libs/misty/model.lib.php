<?php
namespace misty;
class model
{
	protected $db = null;
	private static $instance = null;
	
	public function __construct ()
	{
		$this->db = db::load(core::env()->config->get('db', []));
		
		if (is_callable([$this, '__init']))
			$this->__init();
	}
	
	public static function load ()
	{
		if (self::$instance === null)
			self::$instance = new static;
		return self::$instance;
	}
};