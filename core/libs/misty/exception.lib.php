<?php
namespace misty;
class exception extends \exception
{
	public function __construct (string $message = null, string $file = null, int $line = null)
	{
		if ($file !== null)
			$this->file = $file;
		if ($line !== null)
			$this->line = $line;
		parent::__construct($message);
	}
};