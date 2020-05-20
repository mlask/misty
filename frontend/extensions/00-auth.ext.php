<?php
namespace misty;
new class
{
	public function __construct ()
	{
		$db = db::init(core::env()->config->get('db', []));
		
		core::env()->set([
			'user'	=> ['auth' => false]
		]);
	}
};