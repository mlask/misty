<?php
namespace misty\attr;

#[\Attribute(\Attribute::TARGET_CLASS)]
class defaults
{
	public function __construct (private ?string $action = null, private bool $fallback = false)
	{
	}
	
	public function action (): ?string
	{
		return $this->action;
	}
	
	public function fallback (): bool
	{
		return $this->fallback;
	}
}