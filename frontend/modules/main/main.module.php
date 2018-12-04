<?php
class main_module extends module
{
	const default_action = 'main';
	const default_fallback = true;
	
	public function main ()
	{
		// main module
		$this->view->display('main', $this->_name);
	}
}