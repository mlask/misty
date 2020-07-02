<?php
namespace misty\form;
class input
{
	protected $form = null;
	protected $name = null;
	protected $value = null;
	protected $valid = 0;
	protected $errors = null;
	protected $required = false;
	protected $overwrite = false;
	private $formatter = null;
	private $validators = null;
	private $preprocessor = null;
	
	// konstruktur
	public function __construct ($name)
	{
		$this->name = $name;
	}
	
	// pobieranie stanu
	public final function get_form ()
	{
		return $this->form;
	}
	
	public final function get_name ()
	{
		return $this->name;
	}
	
	public final function get_value ($no_formatter = false)
	{
		return $this->formatter !== null && $no_formatter === false ? call_user_func($this->formatter, $this->value) : $this->value;
	}
	
	public final function get_errors ()
	{
		return $this->errors;
	}
	
	public final function is_valid ()
	{
		return (int)$this->valid === 1;
	}
	
	public final function is_required()
	{
		return $this->required;
	}
	
	public final function can_overwrite ()
	{
		return $this->overwrite;
	}
	
	// ustawienie stanu
	public final function required ($required = true)
	{
		$this->required = $required;
		return $this;
	}
	
	public final function overwrite ($overwrite = true)
	{
		$this->overwrite = $overwrite;
		return $this;
	}
	
	public final function has_error ($message)
	{
		$this->errors[] = $message;
		return $this;
	}
	
	public final function with_value ($value = null)
	{
		$this->value = $value;
		return $this;
	}
	
	public final function formatted_by ($formatter = null)
	{
		if (is_callable($formatter) || $formatter instanceof \Closure)
			$this->formatter = $formatter;
		return $this;
	}
	
	public final function validated_by (...$validators)
	{
		if (is_array($validators) && !empty($validators))
			foreach ($validators as & $validator)
				if (is_callable($validator) || $validator instanceof \Closure)
					$this->validators[] = $validator;
		return $this;
	}
	
	public final function preprocessed_by ($preprocessor = null)
	{
		if (is_callable($preprocessor) || $preprocessor instanceof \Closure)
			$this->preprocessor = $preprocessor;
		return $this;
	}
	
	// dopięcie do formularza
	public final function attach (& $form)
	{
		if ($this->form === null)
			$this->form = $form;
		return $this;
	}
	
	// walidacja
	public final function validate (& $request)
	{
		$this->set_value($this->preprocessor !== null ? call_user_func_array($this->preprocessor, [& $this, $request->sent('post', $this->name) ? $request->post($this->name) : null]) : ($request->sent('post', $this->name) ? $request->post($this->name) : null));
		
		// stan walidacji
		$this->valid = 1;
		
		// pole wymagane?
		if (is_callable([$this, 'validate_field']))
		{
			$this->validate_field($request);
		}
		else
		{
			if ($this->required && (!$request->sent('post', $this->name) || (!is_array($request->post($this->name)) && strlen(trim($request->post($this->name))) === 0) || (is_array($request->post($this->name)) && empty($request->post($this->name)))))
			{
				$this->errors[] = \misty\i18n::load()->_('Field cannot be empty');
				$this->valid = 0;
			}
		}
		
		// funkcje walidujące
		if ($this->valid === 1 && $this->validators !== null && is_array($this->validators) && !empty($this->validators))
		{
			foreach ($this->validators as & $validator)
			{
				$_valid = (int)call_user_func_array($validator, [& $this]);
				$this->valid &= $_valid;
				
				if ($_valid === 0)
					break;
			}
		}
		
		return $this->valid;
	}
	
	// aliasy funkcji
	public final function set_value (...$args) { $this->with_value(...$args); }
};