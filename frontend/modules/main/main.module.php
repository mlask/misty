<?php
namespace misty;

#[attr\user(auth: true)]
#[attr\defaults(action: "main", fallback: true)]
#[attr\description("Moduł główny")]
class main_module extends module
{
	public function __preload ()
	{
		menu::load()->add(
			menu::item(i18n::load()->_('Main'))->add(
				menu::item(i18n::load()->_('Submenu 1'), 'main/test1'),
				menu::item(i18n::load()->_('Submenu 2'), 'main/test2'),
				menu::item(i18n::load()->_('Submenu 3'), 'main/test3/a:b/c:d'),
			)
		);
	}
	
	#[attr\description("Główna funkcja modułu")]
	public function main (): void
	{
		// main module
		$this->view->render('[module]menu.tpl', view::MENU);
		$this->view->render('[module]main.tpl', view::PAGE);
	}
}