<?php
namespace misty;
class test_model extends model
{
	// test model
	public function test (...$args)
	{
		throw new exception('exception handling test');
	}
}