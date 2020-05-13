<?php
namespace misty;
class config
{
	private static $instance = null;
	private $_loaded = false;
	private $_config = null;
	
	public static function load ()
	{
		if (!defined('\misty\core::VERSION'))
			throw new exception('Cannot load configuration without misty core!');
		
		if (self::$instance === null)
			self::$instance = new self;
		return self::$instance;
	}
	
	public function get ($name, $default = null)
	{
		return isset($this->_config[$name]) ? $this->_config[$name] : $default;
	}
	
	public function get_all ($match = null)
	{
		foreach ((new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
			if ($match === null || preg_match($match, $property->name))
				yield $property->name => json_decode(json_encode($this->{$property->name}), true);
	}
	
	public function is_loaded ()
	{
		return $this->_loaded;
	}
	
	private function __construct ()
	{
		foreach (['config.local', 'config'] as $name)
		{
			if (file_exists($file = sprintf('%s/config/%s.php', core::env()->path->core, $name)))
			{
				ob_start();
				$config = require_once($file);
				ob_end_clean();
				
				if (is_array($config) && !empty($config))
				{
					$this->_loaded = true;
					$this->_config = iterator_to_array($this->_config_map($config, $this));
				}
				$config = null;
				unset($config);
				
				break;
			}
		}
	}
	
	private function _config_map ($input = null, & $target = null, $prefix = null)
	{
		if (is_array($input))
		{
			if ($prefix !== null)
				yield $prefix => $input;
			
			foreach ($input as $key => $value)
			{
				if (!isset($target->{strtolower($key)}))
				{
					$target->{strtolower($key)} = new stdclass;
					yield from $this->_config_map($value, $target->{strtolower($key)}, ($prefix !== null ? $prefix . '.' : '') . strtolower($key));
				}
			}
		}
		else
		{
			$target = $input;
			yield $prefix => $input;
		}
	}
}