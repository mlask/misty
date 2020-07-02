<?php
namespace misty\form;
class validator
{
	public function date ($message = null)
	{
		return function (& $field) use ($message)
		{
			if (strlen($field->get_value(true)) > 0)
			{
				$valid = true;
				
				if (preg_match('/^(19|20)\d\d[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])$/', $field->get_value(true), $match))
				{
					if ((int)$match[3] === 31 && ((int)$match[2] === 4 || (int)$match[2] === 6 || (int)$match[2] === 9 || (int)$match[2] === 11))
						$valid = false; // 31st of a month with 30 days
					elseif ((int)$match[3] >= 30 && (int)$match[2] === 2)
						$valid = false; // Februrary 30th or 31st
					elseif ((int)$match[2] === 2 && (int)$match[3] === 29 && !((int)$match[1] % 4 === 0 && ((int)$match[1] % 100 !== 0 || (int)$match[1] % 400 === 0)))
						$valid = false; // February 29th outside a leap year
				}
				else
					$valid = false;
				
				if (!$valid)
				{
					$field->has_error($message !== null ? $message : \misty\i18n::load()->_s('Invalid date format: %s', $field->get_value(true)));
					return false;
				}
			}
			return true;
		};
	}
	
	public function email ($message = null)
	{
		return function (& $field) use ($message)
		{
			if (strlen($field->get_value(true)) > 0 && !preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $field->get_value(true)))
			{
				$field->has_error($message !== null ? $message : \misty\i18n::load()->_('Invalid e-mail address format'));
				return false;
			}
			return true;
		};
	}
	
	public function phone ($message = null)
	{
		return function (& $field) use ($message)
		{
			if (strlen($field->get_value(true)) > 0 && !preg_match('/^[0-9+]{0,16}$/', $field->get_value(true)))
			{
				$field->has_error($message !== null ? $message : \misty\i18n::load()->_('Invalid phone number format'));
				return false;
			}
			return true;
		};
	}
	
	public function regex ($regex, $message = null)
	{
		return function (& $field) use ($regex, $message)
		{
			if (strlen($field->get_value(true)) > 0 && !preg_match($regex, $field->get_value(true)))
			{
				$field->has_error($message !== null ? $message : \misty\i18n::load()->_('Invalid value format'));
				return false;
			}
			return true;
		};
	}
	
	public function numeric ($message = null)
	{
		return function (& $field) use ($message)
		{
			if (strlen($field->get_value(true)) > 0 && !is_numeric($field->get_value(true)))
			{
				$field->has_error($message !== null ? $message : \misty\i18n::load()->_('Non-numeric value'));
				return false;
			}
			return true;
		};
	}
};