<?php
namespace misty;
class main_module extends module
{
	const DEFAULT_ACTION = 'info';
	const DEFAULT_FALLBACK = true;
	
	public function info ()
	{
		$options = [];
		
		foreach (glob(core::env()->path->absolute . '/' . core::env()->path->workspace . '/modules/*/*.module.php') as $_mf)
		{
			if ($_mf !== __FILE__)
			{
				require_once($_mf);
			
				$_mm = basename($_mf, '.module.php');
				$_mn = sprintf('\\misty\\%s_module', $_mm);
			
				if (class_exists($_mn))
					$options[] = ['option' => $_mm, 'module' => $_mn, 'file' => $_mf];
			}
		}
		
		printf("💡 \x1b[96;1mAvailable CLI modules:\x1b[0m\n");
		if (!empty($options))
		{
			foreach ($options as $option)
				printf("   %s \x1b[1m%s\x1b[0m\n", core::env()->request->self, $option['option']);
		}
		else
			printf("   \x1b[91;1mno valid modules found!\x1b[0m\n");
		printf("\n");
	}
}