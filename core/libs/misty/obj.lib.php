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
	
	public function get (): array
	{
		return $this->data !== null ? $this->data : [];
	}
	
	public function set (array $data = null): void
	{
		if (is_array($data) && !empty($data))
		{
			$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
			foreach ($data as $key => $value)
			{
				if (isset($this->data[$key]) && $this->origin !== $bt['file'] && ($this->allow === null || !in_array($bt['file'], $this->allow)))
					throw new exception(i18n::load()->_('Cannot modify misty\obj outside it\'s origin!'), $bt['file'], $bt['line']);
				$this->data[$key] = $value;
			}
		}
	}
	
	public function allow (string $file): void
	{
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
		if ($this->origin === $bt['file'])
			$this->allow[] = $file;
	}
	
	public function reindex (): void
	{
		if (is_array($this->data))
			$this->data = array_values($this->data);
	}
	
	public function __get (string $name): mixed
	{
		if (isset($this->data[$name]))
			return $this->data[$name];
		return null;
	}
	
	public function __set (string $name, mixed $value): void
	{
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
		if ($this->origin !== $bt['file'] && ($this->allow === null || !in_array($bt['file'], $this->allow)))
			throw new exception(i18n::load()->_('Cannot modify misty\obj outside it\'s origin!'), $bt['file'], $bt['line']);
		$this->data[$name] = $value;
	}
	
	public function __call (string $name, array $arguments = null): mixed
	{
		if (isset($this->data[$name]) && (is_callable($this->data[$name]) || $this->data[$name] instanceof \Closure))
			return $this->data[$name](...$arguments);
	}
	
	public function __isset (string $name): bool
	{
		return isset($this->data[$name]);
	}
	
	public function __unset (string $name): void
	{
		if (isset($this->data[$name]))
			unset($this->data[$name]);
	}
};