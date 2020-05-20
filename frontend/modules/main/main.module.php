<?php
namespace misty;
class main_module extends module
{
	const DEFAULT_ACTION = 'main';
	const DEFAULT_FALLBACK = false;
	
	public function main ()
	{
		// main module
		$this->view->render('[module]main.tpl');
	}
}