<?php
namespace misty\attr;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class description
{
	public function __construct (private string $description)
	{
	}
	
	public function __toString (): string
	{
		return $this->description;
	}
}