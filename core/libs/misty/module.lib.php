<?php
namespace misty;
class module
{
	private $libs = null;
	protected $name = null;
	
	public function __construct (array $libs = null)
	{
		// module name
		$this->name = preg_match('/^misty\\\(.+?)_module$/', get_class($this), $m) ? $m[1] : basename(get_class($this), '_module');
		
		// additional libs
		if (!empty($libs))
			foreach ($libs as $name => & $lib)
				$this->libs[$name] = $lib;
		
		// init function
		if (is_callable([$this, '__init']))
		{
			$status = $this->__init();
			if ($status === false)
				$this->__break = true;
		}
	}
	
	public function __get ($name)
	{
		if (isset($this->libs[$name]))
			return $this->libs[$name];
	}
};