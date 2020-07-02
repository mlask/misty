<?php
namespace misty\menu;
class separator extends item
{
	protected $is_separator = true;
	
	public function __construct ()
	{
		$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'];
		
		$this->name = null;
		$this->action = null;
	}
};