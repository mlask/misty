<?php
class model
{
	public function __construct ()
	{
		if (is_callable([$this, '__init']))
			$this->__init();
	}
}