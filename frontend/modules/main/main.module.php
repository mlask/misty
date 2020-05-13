<?php
namespace misty;
class main_module extends module
{
	const DEFAULT_ACTION = 'main';
	const DEFAULT_FALLBACK = true;
	
	public function main ()
	{
		print_r($this);
		
		// main module
		$this->view->display('main', $this->_name);
		$this->view->flush('index');
	}
}