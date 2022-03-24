<?php
namespace misty;
class form
{
	private $is_valid = false;
	private $is_sent = false;
	private $request = null;
	private $fields = [];
	private $id = null;
	
	public function __construct (mixed ...$fields)
	{
		$source = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[array_key_last(array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 'function'))];
		$this->id = sprintf('form.%s.%s', md5($source['file']), sha1(json_encode($source)));
		$this->request = request::load();
		$this->is_sent = $this->request->sent('post', '__form_id') && $this->request->post('__form_id') === $this->id;
		
		if (is_array($fields) && !empty($fields))
			$this->add(...$fields);
	}
	
	public function add (mixed ...$fields): self
	{
		foreach ($fields as $field)
		{
			if (!isset($this->fields[$field->get_name()]) || $field->can_overwrite())
				$this->fields[$field->get_name()] = $field->attach($this);
			else
				throw new exception(i18n::load()->_s('Duplicate form field: %s', $field->get_name()));
		}
		return $this;
	}
	
	public function get (string $name): mixed
	{
		if (isset($this->fields[$name]))
			return $this->fields[$name];
		else
			throw new exception(i18n::load()->_s('Unknown form field: %s', $name));
	}
	
	public function reset (): self
	{
		$this->is_valid = false;
		$this->fields = [];
		return $this;
	}
	
	public function get_id (): string
	{
		return $this->id;
	}
	
	public function get_value (string $name, bool $formatted = false): mixed
	{
		if (isset($this->fields[$name]))
			return $this->fields[$name]->get_value(!$formatted);
		return null;
	}
	
	public function get_values (bool $formatted = false): array
	{
		$values = [];
		foreach ($this->fields as $field)
			$values[$field->get_name()] = $field->get_value(!$formatted);
		return $values;
	}
	
	public function get_files (): array
	{
		$files = [];
		foreach ($this->fields as $field)
			if (method_exists($field, 'get_files'))
				$files[$field->get_name()] = $field->get_files();
		return $files;
	}
	
	public function on_sent (?callable $callback = null): self
	{
		if (is_callable($callback))
		{
			$this->validate();
			if ($this->is_sent)
				call_user_func_array($callback, [& $this]);
		}
		return $this;
	}
	
	public function validate (): bool
	{
		if ($this->is_sent)
		{
			$valid = 0;
			foreach ($this->fields as $field)
				$valid += (int)$field->validate($this->request);
		
			$this->is_valid = $valid === count($this->fields);
		}
		return $this->is_valid;
	}
	
	public function __get (string $name): mixed
	{
		if (isset($this->$name))
			return $this->$name;
		
		if (preg_match('/^(.+?)__([a-z0-9_]+)$/', $name, $match) && isset($this->fields[$match[1]]))
		{
			if (method_exists($this->fields[$match[1]], 'is_' . $match[2]))
				return $this->fields[$match[1]]->{'is_' . $match[2]}();
			elseif (method_exists($this->fields[$match[1]], 'get_' . $match[2]))
				return $this->fields[$match[1]]->{'get_' . $match[2]}();
		}
		
		return null;
	}
	
	public function __isset (string $name): bool
	{
		return isset($this->fields[$name]);
	}
	
	public function __call (string $name, ?array $args = null): mixed
	{
		if (count($args) > 0)
		{
			if (isset($this->fields[$args[0]]))
				return $this->fields[array_shift($args)]->{$name}(...$args);
			else
				throw new exception(i18n::load()->_s('Undefined form field: %s', $args[0]));
		}
		else
			throw new exception(i18n::load()->_s('Call to undefined form method: %s', $name));
	}
	
	public static function __callStatic (string $name, ?array $args = null): mixed
	{
		if (class_exists($class = '\\misty\\form\\' . $name))
			return new $class(...$args);
	}
	
	public static function create (mixed ...$fields): self
	{
		return (new static)->add(...$fields);
	}
};