<?php
namespace misty\form;
class file extends input
{
	private $max_size = null;
	
	public function max_size ($size = null)
	{
		$this->max_size = $size;
		return $this;
	}
};