<?php
namespace misty\attr;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class defaults
{
	public function __construct (private ?string $action = null, private bool $fallback = false)
	{
	}
	
	public function action ()
	{
		return $this->action;
	}
	
	public function fallback ()
	{
		return $this->fallback;
	}
}