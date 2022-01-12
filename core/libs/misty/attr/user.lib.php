<?php
namespace misty\attr;

#[\Attribute(\Attribute::TARGET_CLASS)]
class user
{
	public function __construct (private bool $auth = false)
	{
	}
	
	public function require_auth (): bool
	{
		return $this->auth;
	}
}