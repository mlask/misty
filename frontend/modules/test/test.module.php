<?php
namespace misty;

#[attr\user(auth: false)]
#[attr\defaults(action: "main", fallback: true)]
#[attr\description("Moduł testowy")]
class test_module extends module
{
	#[attr\description("Główna funkcja modułu")]
	public function main (): void
	{
		// main module
		$this->view->render('[module]test.tpl', view::PAGE);
	}
}