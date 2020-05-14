<?php
namespace misty;
class form
{
	const id = '_form_id';
	
	private $id = null;
	private $i18n = null;
	private $sent = false;
	private $valid = false;
	private $fields = null;
	
	public function __construct (array $fields = null)
	{
		$this->fields = $fields === null ? [] : $fields;
		$this->i18n = i18n::init();
		$this->_update();
		$this->_validate();
	}
	
	/* ---------- Funkcje publiczne ---------- */
	
	public function add (array $fields)
	{
		$this->fields = array_merge($this->fields, $fields);
		$this->_update();
		$this->_validate();
	}
	
	public function view ()
	{
		$output = [
			'_id' 		=> $this->id,
			'_sent'		=> $this->sent,
			'_valid'	=> $this->valid
		];
		foreach ($this->fields as $field)
		{
			$output[$field['name']] = [
				'value'		=> $field['value'],
				'invalid'	=> $this->sent && !$field['valid'],
				'message'	=> $field['messages'] !== null ? implode('; ', $field['messages']) : null,
				'required'	=> $field['required']
			];
		}
		return $output;
	}
	
	public function values ()
	{
		$output = [];
		foreach ($this->fields as $field)
			$output[$field['name']] = $field['value_in'] !== null ? $field['value_in'] : $field['value'];
		return $output;
	}
	
	public function __get ($name)
	{
		if (isset($this->$name))
			return $this->$name;
		elseif ($name === 'values')
			return $this->values();
		return false;
	}
	
	/* ---------- Funkcje prywatne ---------- */
	
	private function _update ()
	{
		$trace = array_pop(debug_backtrace(0, 2));
		$types = ['text', 'password', 'checkbox', 'radio', 'select', 'multi-select'];
		$reserved = ['_id', '_sent', '_valid'];
		$this->id = sha1(json_encode([$trace['file'], $trace['line'], $trace['function'], $trace['args']]) . json_encode($this->fields));
		
		foreach ($this->fields as $name => & $field)
		{
			$field = [
				'name'		=> $name,
				'type'		=> isset($field['type']) ? $field['type'] : 'text',
				'value'		=> isset($field['value']) ? $field['value'] : false,
				'valid'		=> isset($field['required']) ? !(bool)$field['required'] : true,
				'value_in'	=> null,
				'messages'	=> null,
				'required'	=> isset($field['required']) ? (bool)$field['required'] : false,
				'validator'	=> isset($field['validator']) ? $field['validator'] : false
			];
			
			if (in_array($name, $reserved))
				throw new exception('Restricted field name: ' . $name);
			
			if (!in_array($field['type'], $types))
				throw new exception('Unknown form field type: ' . $field['type']);
		}
		
		$trace = null;
		unset($trace);
	}
	
	private function _validate ()
	{
		if (isset($_POST[self::id]) && $_POST[self::id] === $this->id)
		{
			$this->sent = true;
			$this->valid = 0;
			foreach ($this->fields as & $field)
			{
				if (isset($_POST[$field['name']]))
					$field['value_in'] = $_POST[$field['name']];
				
				if (is_array($field['value_in']))
				{
					if ($field['required'] && empty($field['value_in']))
					{
						$field['messages'][] = $this->i18n->_('Field is required');
						$field['valid'] = false;
					}
					else
						$field['valid'] = true;
				}
				else
				{
					if ($field['required'] && ($field['value_in'] === null || strlen(trim($field['value_in'])) === 0))
					{
						$field['messages'][] = $this->i18n->_('Field is required');
						$field['valid'] = false;
					}
					else
						$field['valid'] = true;
				}
				if ($field['value_in'] !== null)
				{
					switch ($field['type'])
					{
						case 'select':
							$field['value'] = is_array($field['value_in']) ? array_shift($field['value_in']) : $field['value_in'];
							break;
					
						case 'multi-select':
							$field['value'] = array_combine(array_values($field['value_in']), array_fill(0, count($field['value_in']), true));
							break;
							
						default:
							$field['value'] = $field['value_in'];
					}
				}
			}
			foreach ($this->fields as & $field)
			{
				if (((is_array($field['value']) && !empty($field['value'])) || strlen($field['value']) > 0) && $field['valid'] === true && isset($field['validator']))
				{
					$validates = true;
					if (is_array($field['validator']))
					{
						foreach ($field['validator'] as $v_id => $v_data)
						{
							if ($v_data !== false)
							{
								if (is_int($v_id))
								{
									if (is_callable([$this, '_validate_' . $v_data]))
										if (!$this->{'_validate_' . $v_data}($field))
											$validates = false;
								}
								else
								{
									if (is_callable([$this, '_validate_' . $v_id]))
										if (!$this->{'_validate_' . $v_id}($field, $v_data))
											$validates = false;
								}
							}
						}
					}
					else
					{
						if (is_callable([$this, '_validate_' . $field['validator']]))
							if (!$this->{'_validate_' . $field['validator']}($field))
								$validates = false;
					}
					$field['valid'] = $validates;
				}
				$this->valid += (int)$field['valid'];
			}
			$this->valid = $this->valid === count($this->fields);
		}
	}
	
	/* ---------- Funkcje walidujące dane ---------- */
	
	private function _validate_func (array & $field, callable $function)
	{
		return $function($field);
	}
	
	private function _validate_func_ext (array & $field, array $config)
	{
		if (isset($config['func']) && isset($config['message']) && is_callable($config['func']))
		{
			$config['valid_on'] = isset($config['valid_on']) ? (bool)$config['valid_on'] : false;
			if (call_user_func_array($config['func'], array_merge([$field['value_in']], isset($config['params']) ? (array)$config['params'] : [])) === !$config['valid_on'])
			{
				$field['messages'][] = $config['message'];
				return false;
			}
			return true;
		}
		else
			throw new exception('Invalid callback or missing parameters');
	}
	
	private function _validate_length (array & $field, $length)
	{
		if (is_array($length) && (strlen($field['value_in']) < $length[0] || strlen($field['value_in']) > $length[1]))
		{
			$field['messages'][] = $this->i18n->_s('Incorrect length (min. %d, max. %s)', $length[0], format::num($length[1], [$this->i18n->_('chars ~~0'), $this->i18n->_('char ~~1'), $this->i18n->_('chars ~~2')]));
			return false;
		}
		elseif (is_numeric($length) && strlen($field['value_in']) < $length)
		{
			$field['messages'][] = $this->i18n->_s('Incorrect length (min. %s)', format::num($length, [$this->i18n->_('chars ~~0'), $this->i18n->_('char ~~1'), $this->i18n->_('chars ~~2')]));
			return false;
		}
		return true;
	}
	
	private function _validate_email (array & $field)
	{
		if (strpos($field['value'], '@') === false)
		{
			$field['messages'][] = $this->i18n->_('Incorrect e-mail address');
			return false;
		}
		return true;
	}
	
	private function _validate_regexp (array & $field, $regexp = '')
	{
		if (!preg_match($regexp, $field['value']))
		{
			$field['messages'][] = $this->i18n->_('Incorrect format');
			return false;
		}
		return true;
	}
	
	private function _validate_password (array & $field, array $params = null)
	{
		if (isset($params['match-field']) && isset($this->fields[$params['match-field']]) && strlen($this->fields[$params['match-field']]['value']) > 0)
		{
			if (strcmp($field['value'], $this->fields[$params['match-field']]['value']) !== 0)
			{
				$field['messages'][] = $this->i18n->_('Entered passwords are not equal');
				return false;
			}
		}
		return true;
	}
}