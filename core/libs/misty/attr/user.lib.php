<?php
namespace misty\attr;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
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