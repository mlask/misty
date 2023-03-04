<?php
namespace misty;
new class
{
	public function __construct ()
	{
		core::env()->request->add_route(['/^(.+)\.html$/' => 'test/page/$1']);
	}
};