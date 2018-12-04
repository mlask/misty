<?php
namespace Rain\Tpl\Plugin;
require_once __DIR__ . '/../Plugin.php';

class Cleanup extends \Rain\Tpl\Plugin
{
	protected $hooks = array('afterParse');
	
	public function afterParse (\ArrayAccess $context)
	{
		$html = $context->code;
		$html = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $html);
		$html = preg_replace("/[\r\n]+?(\<\?php)/", '$1', $html);
		$html = preg_replace("/^\t+/m", '', $html);
		$html = preg_replace("/[\r\n]/", '', $html);
		$html = str_replace('<ul>\s*?</ul>', '', $html);
		$context->code = $html;
	}
}
