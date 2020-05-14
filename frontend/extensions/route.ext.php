<?php
namespace misty;
new class
{
	public function __construct ()
	{
		core::env()->request->_add_route(['/^(.+)\.html/' => 'main/page/$1']);
	}
};