<?php
namespace misty;
new class
{
	private $view = null;
	
	public function __construct ()
	{
		$mod = null;
		$this->view = view::init();
		
		// "do, or do not, there's no..."
		try
		{
			core::run(['view' => $this->view]);
		}
		catch (exception $exception)
		{
			// exception to view
			$this->view->assign('core_exception', $exception);
		}
		
		// update i18n data
		i18n::reload();
		
		// output view
		$this->view->flush();
	}
};