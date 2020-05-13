<?php
class module
{
	protected $_name = null;
	protected $env = null;
	protected $view = null;
	
	public function __construct ()
	{
		// aktualny moduł
		$this->_name = basename(get_class($this), '_module');
		
		// środowisko
		$this->env = core::env();
		$this->i18n = i18n::init();
		if (!$this->env->cli)
			$this->view = view::init();
		
		// funkcja inicjalizująca
		if (is_callable([$this, '__init']))
		{
			$status = $this->__init();
			if ($status === false)
				$this->__break = true;
		}
	}
}