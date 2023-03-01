<?php
namespace misty;

#[\AllowDynamicProperties]
class config
{
	private static $instance = null;
	private $_loaded = false;
	private $_config = null;
	
	public static function load (): ?self
	{
		if (!class_exists('\misty\core') || \misty\core::env() === null)
			throw new exception('Cannot load configuration without misty core!');
		
		if (self::$instance === null)
			self::$instance = new static;
		return self::$instance;
	}
	
	public function get (string $name, mixed $default = null): mixed
	{
		return isset($this->_config[$name]) ? $this->_config[$name] : $default;
	}
	
	public function get_all (bool $match = null): \generator
	{
		foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property)
			if ($match === null || preg_match($match, $property->name))
				yield $property->name => json_decode(json_encode($this->{$property->name}), true);
	}
	
	public function is_loaded (): bool
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
	
	private function _config_map (mixed $input = null, mixed & $target = null, ?string $prefix = null): \generator
	{
		if (is_array($input))
		{
			if ($prefix !== null)
				yield $prefix => $input;
			
			foreach ($input as $key => $value)
			{
				if (!isset($target->{strtolower($key)}))
				{
					$target->{strtolower($key)} = new \stdClass;
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
};