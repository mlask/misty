<?php
namespace misty\form;
class file
{
	protected $form = null;
	protected $name = null;
	protected $value = null;
	protected $valid = 0;
	protected $errors = null;
	protected $multiple = false;
	protected $max_size = null;
	protected $required = false;
	protected $overwrite = false;
	protected $allow_mime_type = null;
	
	// konstruktur
	public function __construct (string $name, bool $multiple = false)
	{
		$this->name = $name;
		$this->multiple = $multiple;
	}
	
	// pobieranie stanu
	public final function get_form (): \misty\form
	{
		return $this->form;
	}
	
	public final function get_name (): ?string
	{
		return $this->name;
	}
	
	public final function get_files (): ?array
	{
		$output = null;
		if ($this->multiple === true)
		{
			if (isset($_FILES[$this->name]) && is_array($_FILES[$this->name]['tmp_name']))
			{
				foreach ($_FILES[$this->name]['tmp_name'] as $_idx => $_tmp)
				{
					if (is_uploaded_file($_FILES[$this->name]['tmp_name'][$_idx]) && $_FILES[$this->name]['error'][$_idx] === UPLOAD_ERR_OK && (!is_array($this->allow_mime_type) || in_array($_FILES[$this->name]['type'][$_idx], $this->allow_mime_type)))
					{
						$output[] = [
							'name'		=> $_FILES[$this->name]['name'][$_idx],
							'tmp_name'	=> $_FILES[$this->name]['tmp_name'][$_idx],
							'type'		=> $_FILES[$this->name]['type'][$_idx],
							'size'		=> $_FILES[$this->name]['size'][$_idx],
							'error'		=> $_FILES[$this->name]['error'][$_idx]
						];
					}
				}
			}
		}
		else
		{
			if (isset($_FILES[$this->name]) && is_uploaded_file($_FILES[$this->name]['tmp_name']) && $_FILES[$this->name]['error'] === UPLOAD_ERR_OK && (!is_array($this->allow_mime_type) || in_array($_FILES[$this->name]['type'], $this->allow_mime_type)))
				$output = $_FILES[$this->name];
		}
		return $output;
	}
	
	public final function get_value (bool $no_formatter = false): mixed
	{
		return $this->value;
	}
	
	public final function get_errors (): mixed
	{
		return $this->errors;
	}
	
	public final function get_allowed_mime_type (): string
	{
		return is_array($this->allow_mime_type) && !empty($this->allow_mime_type) ? implode(',', $this->allow_mime_type) : '*/*';
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
	public final function multiple (bool $multiple = true): self
	{
		$this->multiple = true;
		return $this;
	}
	
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
	
	public final function max_size (?int $max_size = null): self
	{
		$this->max_size = $max_size;
		return $this;
	}
	
	public final function allow_mime_type (string ...$mime_types): self
	{
		if (is_array($mime_types) && !empty($mime_types))
			$this->allow_mime_type = $mime_types;
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
		if ($this->multiple === true)
		{
			// walidacja wielu plików
			$uploaded_files = null;
			if (isset($_FILES[$this->name]) && is_array($_FILES[$this->name]['tmp_name']))
				foreach ($_FILES[$this->name]['tmp_name'] as $_idx => $_tmp)
					if (is_uploaded_file($_tmp) && $_FILES[$this->name]['error'][$_idx] === UPLOAD_ERR_OK && (!is_array($this->allow_mime_type) || in_array($_FILES[$this->name]['type'][$_idx], $this->allow_mime_type)))
						$uploaded_files[] = $_FILES[$this->name]['name'][$_idx];
			$this->set_value($uploaded_files);
		
			// stan walidacji
			$this->valid = 1;
		
			// pole wymagane?
			if ($this->required && !$uploaded_files)
			{
				$this->errors[] = \misty\i18n::load()->_('Files not sent');
				$this->valid = 0;
			}
		
			// walidacja przesłanego pliku
			if ($this->valid === 1 && isset($_FILES[$this->name]))
			{
				foreach ($_FILES[$this->name]['error'] as $_idx => $_err)
				{
					if (is_uploaded_file($_FILES[$this->name]['tmp_name'][$_idx]) && $_err !== UPLOAD_ERR_OK)
					{
						$this->errors[] = match ($_err)
						{
							UPLOAD_ERR_PARTIAL => \misty\i18n::load()->_s('The uploaded file was only partially uploaded: %s', $_FILES[$this->name]['name'][$_idx]),
							UPLOAD_ERR_INI_SIZE => \misty\i18n::load()->_s('The uploaded file exceeds the upload_max_filesize directive in php.ini: %s', $_FILES[$this->name]['name'][$_idx]),
							UPLOAD_ERR_EXTENSION => \misty\i18n::load()->_s('A PHP extension stopped the file upload: %s', $_FILES[$this->name]['name'][$_idx]),
							UPLOAD_ERR_FORM_SIZE => \misty\i18n::load()->_s('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form: %s', $_FILES[$this->name]['name'][$_idx]),
							UPLOAD_ERR_CANT_WRITE => \misty\i18n::load()->_s('Failed to write file to disk: %s', $_FILES[$this->name]['name'][$_idx]),
							UPLOAD_ERR_NO_TMP_DIR => \misty\i18n::load()->_s('Missing a temporary folder: %s', $_FILES[$this->name]['name'][$_idx]),
							default => \misty\i18n::load()->_s('Unknown upload error: %s', $_FILES[$this->name]['name'][$_idx])
						};
						$this->valid = 0;
						break;
					}
					elseif (is_uploaded_file($_FILES[$this->name]['tmp_name'][$_idx]) && $_err === UPLOAD_ERR_OK && is_array($this->allow_mime_type) && !in_array($_FILES[$this->name]['type'][$_idx], $this->allow_mime_type))
					{
						$this->errors[] = \misty\i18n::load()->_s('File type (%s) not allowed: %s', $_FILES[$this->name]['type'][$_idx], $_FILES[$this->name]['name'][$_idx]);
						$this->valid = 0;
						break;
					}
				}
			}
		}
		else
		{
			// walidacja pojedynczego pliku
			$this->set_value(isset($_FILES[$this->name]) && is_uploaded_file($_FILES[$this->name]['tmp_name']) && $_FILES[$this->name]['error'] === UPLOAD_ERR_OK && (!is_array($this->allow_mime_type) || in_array($_FILES[$this->name]['type'], $this->allow_mime_type)) ? $_FILES[$this->name]['name'] : null);
		
			// stan walidacji
			$this->valid = 1;
		
			// pole wymagane?
			if ($this->required && (!isset($_FILES[$this->name]) || (is_uploaded_file($_FILES[$this->name]['tmp_name']) && $_FILES[$this->name]['error'] !== UPLOAD_ERR_OK)))
			{
				$this->errors[] = \misty\i18n::load()->_('File not sent');
				$this->valid = 0;
			}
		
			// walidacja przesłanego pliku
			if ($this->valid === 1 && isset($_FILES[$this->name]))
			{
				if (is_uploaded_file($_FILES[$this->name]['tmp_name']) && $_FILES[$this->name]['error'] !== UPLOAD_ERR_OK)
				{
					switch ($_FILES[$this->name]['error'])
					{
						case UPLOAD_ERR_INI_SIZE:	$this->errors[] = \misty\i18n::load()->_s('The uploaded file exceeds the upload_max_filesize directive in php.ini: %s', $_FILES[$this->name]['name']); break;
						case UPLOAD_ERR_FORM_SIZE:	$this->errors[] = \misty\i18n::load()->_s('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form: %s', $_FILES[$this->name]['name']); break;
						case UPLOAD_ERR_PARTIAL:	$this->errors[] = \misty\i18n::load()->_s('The uploaded file was only partially uploaded: %s', $_FILES[$this->name]['name']); break;
						case UPLOAD_ERR_NO_TMP_DIR:	$this->errors[] = \misty\i18n::load()->_s('Missing a temporary folder: %s', $_FILES[$this->name]['name']); break;
						case UPLOAD_ERR_CANT_WRITE:	$this->errors[] = \misty\i18n::load()->_s('Failed to write file to disk: %s', $_FILES[$this->name]['name']); break;
						case UPLOAD_ERR_EXTENSION:	$this->errors[] = \misty\i18n::load()->_s('A PHP extension stopped the file upload: %s', $_FILES[$this->name]['name']); break;
						default:					$this->errors[] = \misty\i18n::load()->_s('Unknown upload error: %s', $_FILES[$this->name]['name']); break;
					}
					$this->valid = 0;
				}
				elseif (is_uploaded_file($_FILES[$this->name]['tmp_name']) && $_FILES[$this->name]['error'] === UPLOAD_ERR_OK && is_array($this->allow_mime_type) && !in_array($_FILES[$this->name]['type'], $this->allow_mime_type))
				{
					$this->errors[] = \misty\i18n::load()->_s('File type (%s) not allowed: %s', $_FILES[$this->name]['type'], $_FILES[$this->name]['name']);
					$this->valid = 0;
				}
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