<?php
namespace misty;
new class
{
	private $view = null;
	
	public function __construct ()
	{
		$mod = null;
		$this->view = view::init();
		
		// "do, or do not, there's no..."
		try
		{
			// preload modules
			foreach (glob(core::env()->path->absolute . '/' . core::env()->path->workspace . '/modules/*/*.module.php') as $_mf)
			{
				require_once($_mf);
				
				$_mm = basename($_mf, '.module.php');
				$_mn = sprintf('\misty\%s_module', $_mm);
				
				if (class_exists($_mn))
				{
					$_mr = new \ReflectionClass($_mn);
					$mod[$_mm] = [
						'ref'		=> $_mr,
						'file'		=> $_mf,
						'name'		=> $_mm,
						'default'	=> defined($_mn . '::default_action') ? $_mn::DEFAULT_ACTION : null,
						'fallback'	=> defined($_mn . '::default_fallback') ? $_mn::DEFAULT_FALLBACK : false
					];
					
					if ($_mr->hasMethod('__preload'))
					{
						$pre = $_mr->getMethod('__preload');
						$obj = $_mr->newInstanceWithoutConstructor();
						$pre->invoke($obj);
						unset($obj, $pre);
					}
				}
			}
		
			// preload extensions
			foreach (array_merge(glob(core::env()->path->core . '/extensions/*.ext.php'), glob(core::env()->path->absolute . '/' . core::env()->path->workspace . '/extensions/*.ext.php')) as $_if)
			{
				require_once($_if);
			}
		
			// process module
			$_mn = core::env()->request->module('main');
			if (isset($mod[$_mn]))
			{
				$_mm = $mod[$_mn];
				core::env()->instance = new obj([
					'name'		=> $_mm['name'],
					'file'		=> $_mm['file'],
					'path'		=> dirname($_mm['file']),
					'action'	=> core::env()->request->action($_mm['default']),
					'params'	=> new obj,
					'object'	=> $_mm['ref']->newInstance(['view' => $this->view])
				]);
				
				if (!isset(core::env()->instance->object->__break) || core::env()->instance->object->__break === false)
				{
					if ($_mm['fallback'] && !$_mm['ref']->hasMethod(core::env()->instance->action))
					{
						core::env()->instance->action_requested = core::env()->instance->action;
						core::env()->instance->action = $_mm['default'];
					}
					
					if (!$_mm['ref']->hasMethod(core::env()->instance->action))
					{
						$_me = $_mm['ref']->getMethods(\ReflectionMethod::IS_PUBLIC);
						if (!empty($_me))
							core::env()->instance->action = array_shift($_me)->name;
						else
							throw new exception('Module does not expose any public methods: ' . $_mn);
					}
					
					if ($_mm['ref']->hasMethod(core::env()->instance->action))
					{
						$_me = $_mm['ref']->getMethod(core::env()->instance->action);
						core::log('call %s::%s', $_mn, $_me->name);
						
						foreach ($_me->getParameters() as $_p)
							core::env()->instance->params->{$_p->getPosition()} = 
								core::env()->request->param($_p->getPosition(), core::env()->request->param($_p->getName(), $_p->isDefaultValueAvailable() ? $_p->getDefaultValue() : null));
						$_me->invokeArgs(core::env()->instance->object, core::env()->instance->params->get());
					}
					else
						throw new exception('Action not found: ' . $_mn . '::' . core::env()->instance->action);
				}
			}
			else
				throw new exception('Module not found: ' . $_mn);
		}
		catch (exception $exception)
		{
			// exception to view
			$this->view->assign('core_exception', $exception);
		}
		
		// update i18n data
		i18n::reload();
		
		// output view
		$this->view->flush();
	}
};