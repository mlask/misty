<?php
namespace misty\form;
class checkbox extends input
{
	public function __construct ($name)
	{
		parent::__construct($name);
		
		$this->preprocessed_by(function (& $field, $value) {
			if (is_array($value))
			{
				return array_map(function ($val) { return true; }, array_flip($value));
			}
			else
			{
				if (strlen($value) > 0 && (int)$value > 0)
					return true;
				return false;
			}
		});
	}
	
	public function validate_field (& $request)
	{
		if ($this->required && (!$request->sent('post', $this->name) || empty($request->post($this->name))))
		{
			$this->errors[] = \misty\i18n::load()->_('Option is required');
			$this->valid = 0;
		}
	}
};