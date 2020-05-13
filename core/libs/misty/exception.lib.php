<?php
namespace misty;
class exception extends \exception
{
	public function __construct ($message = null, $file = null, $line = null)
	{
		if ($file !== null)
			$this->file = $file;
		if ($line !== null)
			$this->line = $line;
		parent::__construct($message);
	}
}