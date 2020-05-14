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
	
	public function page ($name)
	{
		// page module (router example)
		$this->view->assign('page', $name);
		$this->view->render('[module]page.tpl');
	}
}