<?php
namespace misty;
class core
{
	const VERSION = 3.20;
	const VERSION_DATE = 20200513;
	
	private static $env = null;
	private static $log = null;
	
	public function __construct ($workspace = null)
	{
		// init autoloader
		spl_autoload_register(function ($name, $ext = null) {
			if (file_exists($file = sprintf('%s/%s.lib.php', __DIR__, strtr($name, '\\', '/'))) ||
				(($lib = $this->_get_lib($name)) !== false && file_exists($file = sprintf('%1$s/%2$ss/%3$s.%2$s.php', $lib['path'], $lib['type'], $lib['name']))))
				require_once($file);
			else
				printf("not found: %s\n", $file);
		});
		
		// environment configuration
		self::$env = new obj([
			'core'		=> sprintf('Misty Core v%0.2f-d%d', self::VERSION, self::VERSION_DATE),
			'uuid'		=> sprintf('misty3-%04x', crc32(realpath(dirname(dirname(__DIR__))))),
			'root'		=> realpath(dirname(dirname(__DIR__))),
			'local'		=> isset($_SERVER['SERVER_ADDR']) && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'],
			'path'		=> new obj([
				'workspace'	=> $workspace !== null ? $workspace : basename(dirname($_SERVER['PHP_SELF']) !== '.' ? dirname($_SERVER['PHP_SELF']) : getcwd()),
				'relative'	=> dirname($_SERVER['SCRIPT_NAME']),
				'core'		=> dirname(__DIR__),
				'absolute'	=> realpath(dirname(dirname(__DIR__))),
				'data'		=> realpath(dirname(dirname(__DIR__))) . '/data',
				'files'		=> realpath(dirname(dirname(__DIR__))) . '/data/files',
				'cache'		=> realpath(dirname(dirname(__DIR__))) . '/data/cache'
			]),
			'cli'		=> php_sapi_name() === 'cli',
			'i18n'		=> null,
			'config'	=> null,
			'request'	=> null,
			'instance'	=> null
		]);
		
		// allow full access to env from workspace init
		self::$env->allow(self::$env->path->absolute . '/' . self::$env->path->workspace . '/init.php');
		
		// check required files and directories
		if (!file_exists(self::$env->path->cache) || !is_writable(self::$env->path->cache))
			throw new exception('Cache directory not found or not writable: ' . self::$env->path->cache);
		if (!file_exists(self::$env->path->workspace))
			throw new exception('Workspace not found: ' . self::$env->path->workspace);
		
		// set session identifier
		session_name(self::$env->uuid);
		
		// load config
		self::$env->config = config::load();
		
		// load i18n
		self::$env->i18n = i18n::init();
		
		// get request
		self::$env->request = new request;
		
		// vendor libs autoloader
		if (file_exists(self::$env->path->core . '/libs/vendor/autoload.php'))
			require_once(self::$env->path->core . '/libs/vendor/autoload.php');
		
		// init workspace
		if (file_exists(self::$env->path->workspace . '/init.php'))
			require_once(self::$env->path->workspace . '/init.php');
	}
	
	public static function add (array $items)
	{
		foreach ($items as $key => $value)
			if (!isset(self::$env->{$key}))
				self::$env->{$key} = $value;
	}
	
	public static function env ()
	{
		return self::$env;
	}
	
	public static function log (...$message)
	{
		if (count($message) > 0)
		{
			self::$log[] = [
				'time'		=> microtime(true),
				'memory'	=> memory_get_usage(true),
				'source'	=> debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0],
				'message'	=> count($message) > 1 ? vsprintf(array_shift($message), $message) : array_shift($message)
			];
		}
		return self::$log;
	}
	
	public static function run (...$args)
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
				'object'	=> $_mm['ref']->newInstance(...$args)
			]);
			
			if (!isset(core::env()->instance->object->_break) || core::env()->instance->object->_break === false)
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
					core::log('call module "%s", action "%s" (args %s)', $_mn, $_me->name, json_encode(core::env()->request->params()));
					
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
	
	private function _get_lib ($name)
	{
		if (isset(self::$env->path->core))
		{
			$lib = explode('\\', $name);
			if (count($lib = explode('_', end($lib))) > 1)
				return ['path' => self::$env->path->core, 'type' => array_pop($lib), 'name' => implode('_', $lib)];
		}
		return false;
	}
}