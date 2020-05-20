<?php
namespace misty;
class form
{
	private $is_valid = false;
	private $is_sent = false;
	private $request = null;
	private $fields = [];
	private $id = null;
	
	public function __construct ()
	{
		$source = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[array_key_last(array_column(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 'function'))];
		$this->id = sprintf('form.%s.%s', md5($source['file']), sha1(json_encode($source)));
		$this->request = request::load();
		$this->is_sent = $this->request->sent('post', '__form_id') && $this->request->post('__form_id') === $this->id;
	}
	
	public function add (...$fields)
	{
		foreach ($fields as $field)
		{
			if (!isset($this->fields[$field->get_name()]) || $field->can_overwrite())
				$this->fields[$field->get_name()] = $field;
			else
				throw new exception('Duplicate field: ' . $field->get_name());
		}
		return $this;
	}
	
	public function get ($name)
	{
		if (isset($this->fields[$name]))
			return $this->fields[$name];
		else
			throw new exception('Unknown field: ' . $name);
	}
	
	public function get_id ()
	{
		return $this->id;
	}
	
	public function get_values ()
	{
		$values = [];
		foreach ($this->fields as $field)
			$values[$field->get_name()] = $field->get_value();
		return $values;
	}
	
	public function on_sent ($callback = null)
	{
		if (is_callable($callback))
		{
			$this->validate();
			call_user_func($callback, $this);
		}
	}
	
	public function validate ()
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
	
	public function __get ($name)
	{
		if (isset($this->$name))
			return $this->$name;
		
		if (preg_match('/^(.+?)__valid$/', $name, $match) && isset($this->fields[$match[1]]))
			return $this->fields[$match[1]]->is_valid();
		if (preg_match('/^(.+?)__value$/', $name, $match) && isset($this->fields[$match[1]]))
			return $this->fields[$match[1]]->get_value();
		if (preg_match('/^(.+?)__errors$/', $name, $match) && isset($this->fields[$match[1]]))
			return $this->fields[$match[1]]->get_errors();
		if (preg_match('/^(.+?)__required$/', $name, $match) && isset($this->fields[$match[1]]))
			return $this->fields[$match[1]]->is_required();
		
		return null;
	}
	
	public static function __callStatic ($name, $args)
	{
		if (class_exists($class = 'form_' . $name))
			return new $class(...$args);
	}
	
	public static function create (...$fields)
	{
		return (new self)->add(...$fields);
	}
}

class form_input
{
	private $name = null;
	private $value = null;
	private $valid = false;
	private $errors = null;
	private $history = null;
	private $required = false;
	private $validator = null;
	private $overwrite = false;
	
	// konstruktur
	
	public function __construct ($name)
	{
		$this->name = $name;
	}
	
	// pobieranie stanu
	
	public function get_name ()
	{
		return $this->name;
	}
	
	public function get_value ()
	{
		return $this->value;
	}
	
	public function get_errors ()
	{
		return $this->errors;
	}
	
	public function is_valid ()
	{
		return $this->valid;
	}
	
	public function is_required()
	{
		return $this->required;
	}
	
	public function can_overwrite ()
	{
		return $this->overwrite;
	}
	
	// ustawienie stanu
	
	public function required ($required = true)
	{
		$this->history['required'][] = $required;
		$this->required = $required;
		return $this;
	}
	
	public function overwrite ($overwrite = true)
	{
		$this->history['overwrite'][] = $overwrite;
		$this->overwrite = $overwrite;
		return $this;
	}
	
	public function with_value ($value = null)
	{
		$this->history['value'][] = $value;
		$this->value = $value;
		return $this;
	}
	
	public function validated_by ($validator = null)
	{
		$this->history['validator'][] = $validator;
		$this->validator = $validator;
		return $this;
	}
	
	// walidacja
	
	public function validate (& $request)
	{
		if ($request->sent('post', $this->name))
			$this->set_value($request->post($this->name));
		
		$this->valid = 1
			&& (!$this->required || ($this->required && $request->sent('post', $this->name) && strlen($request->post($this->name)) > 0))
			&& ($this->validator === null || ($this->validator !== null && is_callable($this->validator) && call_user_func($this->validator, $this)));
		
		return $this->valid;
	}
	
	// aliasy funkcji
	public function set_value (...$args) { $this->with_value(...$args); }
}

class form_file extends form_input
{
	private $max_size = null;
	
	public function max_size ($size = null)
	{
		$this->max_size = $size;
		return $this;
	}
}

class form_text extends form_input {}
class form_select extends form_input {}