<?php
namespace misty\form;
class decimal extends input
{
	public function __construct ($name, $decimals = 0)
	{
		parent::__construct($name);
		
		$this->formatted_by(function ($value) use ($decimals) {
			return number_format((float)$value, $decimals, ',', '');
		});
		
		$this->preprocessed_by(function (& $field, $value) {
			if (strlen($value) > 0)
				return (float)preg_replace('/[^0-9\.]/', '', strtr($value, ',', '.'));
			return 0;
		});
	}
};