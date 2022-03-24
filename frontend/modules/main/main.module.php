<?php
namespace misty;

#[attr\user(auth: true)]
#[attr\defaults(action: "main")]
#[attr\description("Moduł główny")]
class main_module extends module
{
	#[attr\description("Główna funkcja modułu")]
	public function main (): void
	{
		// main module
		$this->view->render('[module]main.tpl');
	}
}