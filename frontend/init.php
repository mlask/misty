<?php
namespace misty;
new class
{
	private $view = null;
	
	public function __construct ()
	{
		$mod = null;
		$this->view = view::load();
		
		// "do, or do not, there's no..."
		try
		{
			core::run('main', [
				'view'		=> $this->view,
				'menu'		=> menu::load(),
				'request'	=> request::load()
			]);
		}
		catch (\Exception $e)
		{
			// exception to view
			$this->view->assign('core_exception', $e);
		}
		catch (\Throwable $t)
		{
			// throwable to view
			$this->view->assign('core_exception', $t);
		}
		
		// update i18n data
		i18n::reload();
		
		// output view
		$this->view->flush();
	}
};