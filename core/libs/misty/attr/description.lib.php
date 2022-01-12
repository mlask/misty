<?php
namespace misty\attr;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class description
{
	public function __construct (private string $description)
	{
	}
}