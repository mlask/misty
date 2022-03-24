<?php
namespace misty\form;
class select extends input
{
	public function validate_field (& $request)
	{
		if ($this->required && (!$request->sent('post', $this->name) || strlen(trim($request->post($this->name) ?? '')) === 0))
		{
			$this->errors[] = \misty\i18n::load()->_('No option selected');
			$this->valid = 0;
		}
	}
};