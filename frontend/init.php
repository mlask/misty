<?php
namespace misty;
new class
{
	public function __construct ()
	{
		$view = view::load();
		
		// "do, or do not, there's no..."
		try
		{
			core::run('main', [
				'view'		=> $view,
				'menu'		=> menu::load(),
				'request'	=> request::load()
			]);
		}
		catch (\Exception $e)
		{
			// exception to view
			$view->assign('core_exception', $e);
		}
		catch (\Throwable $t)
		{
			// throwable to view
			$view->assign('core_exception', $t);
		}
		
		// update i18n data
		i18n::reload();
		
		// output view
		$view->flush();
	}
};