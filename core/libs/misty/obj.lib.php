<?php
namespace misty;
class obj
{
	private $data = null;
	private $allow = null;
	private $origin = null;
	
	public function __construct (array $data = null)
	{
		$this->origin = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];
		$this->set($data);
	}
	
	public function allow ($file)
	{
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
		if ($this->origin === $bt['file'])
			$this->allow[] = $file;
	}
	
	public function set (array $data = null)
	{
		if (is_array($data) && !empty($data))
			foreach ($data as $key => $value)
				$this->data[$key] = $value;
	}
	
	public function get ($flags = null)
	{
		return $this->data;
	}
	
	public function __get ($name)
	{
		if (isset($this->data[$name]))
			return $this->data[$name];
		return null;
	}
	
	public function __set ($name, $value)
	{
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
		if ($this->origin !== $bt['file'] && ($this->allow === null || !in_array($bt['file'], $this->allow)))
		{
			print_r($this->allow);
			throw new exception('Cannot modify collection outside it\'s origin', $bt['file'], $bt['line']);
		}
		$this->data[$name] = $value;
	}
	
	public function __call ($name, $arguments)
	{
		if (isset($this->data[$name]) && is_callable($this->data[$name]))
			$this->data[$name](...$arguments);
	}
}