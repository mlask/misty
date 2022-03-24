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
	public function __construct (string $name)
	{
		$this->name = $name;
	}
	
	// pobieranie stanu
	public final function get_form (): ?\misty\form
	{
		return $this->form;
	}
	
	public final function get_name (): ?string
	{
		return $this->name;
	}
	
	public final function get_value (bool $no_formatter = false): mixed
	{
		return $this->formatter !== null && $no_formatter === false ? call_user_func($this->formatter, $this->value) : $this->value;
	}
	
	public final function get_errors (): mixed
	{
		return $this->errors;
	}
	
	public final function is_valid (): bool
	{
		return (int)$this->valid === 1;
	}
	
	public final function is_required(): bool
	{
		return $this->required;
	}
	
	public final function can_overwrite (): bool
	{
		return $this->overwrite;
	}
	
	// ustawienie stanu
	public final function required (bool $required = true): self
	{
		$this->required = $required;
		return $this;
	}
	
	public final function overwrite (bool $overwrite = true): self
	{
		$this->overwrite = $overwrite;
		return $this;
	}
	
	public final function has_error (string $message): self
	{
		$this->errors[] = $message;
		return $this;
	}
	
	public final function with_value (mixed $value = null): self
	{
		$this->value = $value;
		return $this;
	}
	
	public final function formatted_by (callable $formatter = null): self
	{
		if (is_callable($formatter) || $formatter instanceof \Closure)
			$this->formatter = $formatter;
		return $this;
	}
	
	public final function validated_by (callable ...$validators): self
	{
		if (is_array($validators) && !empty($validators))
			foreach ($validators as & $validator)
				if (is_callable($validator) || $validator instanceof \Closure)
					$this->validators[] = $validator;
		return $this;
	}
	
	public final function preprocessed_by (callable $preprocessor = null): self
	{
		if (is_callable($preprocessor) || $preprocessor instanceof \Closure)
			$this->preprocessor = $preprocessor;
		return $this;
	}
	
	// dopięcie do formularza
	public final function attach (\misty\form & $form): self
	{
		if ($this->form === null)
			$this->form = $form;
		return $this;
	}
	
	// walidacja
	public final function validate (\misty\request & $request): bool
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
			if ($this->required && (!$request->sent('post', $this->name) || (!is_array($request->post($this->name)) && strlen(trim($request->post($this->name) ?? '')) === 0) || (is_array($request->post($this->name)) && empty($request->post($this->name)))))
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
	public final function set_value (mixed ...$args): void
	{
		$this->with_value(...$args);
	}
};