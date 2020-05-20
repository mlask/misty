<?php
namespace misty;
class main_module extends module
{
	const DEFAULT_ACTION = 'main';
	const DEFAULT_FALLBACK = false;
	
	public function main ()
	{
		print_r(core::env());
		
		// main module
		$this->view->render('[module]main.tpl');
	}
	
	public function page ($name, $test = null, ...$args)
	{
		$model = new test_model;
		
		$model->test(123, 3.14, false);
		
		// page module (router example)
		$this->view->assign([
			'name'	=> $name,
			'test'	=> $test,
			'args'	=> $args
		]);
		$this->view->render('[module]page.tpl');
	}
}