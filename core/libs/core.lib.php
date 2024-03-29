<?php
declare(strict_types = 1);
namespace misty;
class core
{
	private static $env = null;
	private static $log = null;
	private static $mem = null;
	private static $run = null;
	
	public function __construct (?string $workspace = null)
	{
		if (PHP_VERSION_ID < 80100)
			die("Required at least PHP version 8.1!\n\n");
		
		// log
		core::log('__construct: %s', $workspace);
		
		// error reporting
		error_reporting(E_ALL);
		
		// preconfiguration
		ini_set('xdebug.var_display_max_depth', 16);
		
		// init autoloader
		spl_autoload_register(function ($name, $ext = null) {
			if (file_exists($file = sprintf('%s/%s.lib.php', __DIR__, strtr($name, '\\', '/'))) ||
				(($lib = $this->_get_lib($name)) !== false && file_exists($file = sprintf('%1$s/%2$ss/%3$s.%2$s.php', $lib['path'], $lib['type'], $lib['name']))))
				require_once($file);
			else
				core::log('not found for autoload: %s', $name);
		});
		
		// environment configuration
		self::$env = new obj([
			'core'		=> sprintf('Misty Core @%s', version::get()),
			'uuid'		=> sprintf('misty3-%04x', crc32(realpath(dirname(dirname(__DIR__))))),
			'root'		=> realpath(dirname(dirname(__DIR__))),
			'local'		=> isset($_SERVER['SERVER_ADDR']) && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'],
			'path'		=> new obj([
				'workspace'	=> $workspace !== null ? $workspace : basename(dirname($_SERVER['PHP_SELF']) !== '.' ? dirname($_SERVER['PHP_SELF']) : getcwd()),
				'relative'	=> dirname($_SERVER['SCRIPT_NAME']),
				'absolute'	=> realpath(dirname(dirname(__DIR__))),
				'core'		=> dirname(__DIR__),
				'data'		=> realpath(dirname(dirname(__DIR__))) . '/data',
				'files'		=> realpath(dirname(dirname(__DIR__))) . '/data/files',
				'cache'		=> realpath(dirname(dirname(__DIR__))) . '/data/cache'
			]),
			'cli'		=> php_sapi_name() === 'cli',
			'i18n'		=> null,
			'config'	=> null,
			'request'	=> null,
			'session'	=> null,
			'instance'	=> null,
			'after'		=> function (string $event, callable $callable) {
				if (!isset(self::$run[$event]))
					self::$run[$event] = [];
				self::$run[$event][] = $callable;
			}
		]);
		
		// allow full access to env from workspace init
		self::$env->allow(self::$env->path->absolute . '/' . self::$env->path->workspace . '/init.php');
		
		// create session object
		self::$env->session = new session;
		
		// get request
		self::$env->request = new request;
		
		// load config
		self::$env->config = config::load();
		
		// load i18n
		self::$env->i18n = i18n::load();
		
		// check required files and directories
		if (!file_exists(self::$env->path->cache) || !is_writable(self::$env->path->cache))
			throw new exception(self::$env->i18n->_s('Cache directory not found or not writable: %s', self::$env->path->cache));
		if (!file_exists(self::$env->path->absolute . '/' . self::$env->path->workspace))
			throw new exception(self::$env->i18n->_s('Workspace not found: %s', self::$env->path->absolute . '/' . self::$env->path->workspace));
		
		// vendor libs autoloader
		core::log('initializing vendor preloader...');
		if (file_exists(self::$env->path->core . '/libs/vendor/autoload.php'))
			require_once(self::$env->path->core . '/libs/vendor/autoload.php');
		
		// init workspace
		core::log('initializing workspace...');
		if (file_exists(self::$env->path->absolute . '/' . self::$env->path->workspace . '/init.php'))
			require_once(self::$env->path->absolute . '/' . self::$env->path->workspace . '/init.php');
	}
	
	public static function add (array $items): void
	{
		foreach ($items as $key => $value)
			if (!isset(self::$env->{$key}))
				self::$env->{$key} = $value;
	}
	
	public static function env (): ?\misty\obj
	{
		return self::$env;
	}
	
	public static function log (mixed ...$message): ?array
	{
		if (count($message) > 0)
		{
			$mem = memory_get_usage();
			$opt = is_array($message[count($message) - 1]) ? array_pop($message) : null;
			self::$log[] = [
				'time'		=> microtime(true),
				'memory'	=> self::$mem !== null ? $mem - self::$mem : $mem,
				'source'	=> debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, ($lvl = $opt['level'] ?? 1))[$lvl - 1],
				'message'	=> count($message) > 1 ? vsprintf(array_shift($message), $message) : array_shift($message)
			];
			self::$mem = $mem;
			$mem = null;
			unset($mem);
		}
		return self::$log;
	}
	
	public static function run (string $module, mixed ...$args): void
	{
		$mod = null;
		$run = true;
		
		// disable request processing
		self::$env->request->processing(false);
		
		// preload extensions
		core::log('processing extensions...');
		foreach (array_merge(glob(self::$env->path->core . '/extensions/*.ext.php'), glob(self::$env->path->absolute . '/' . self::$env->path->workspace . '/extensions/*.ext.php')) as $_if)
		{
			require_once($_if);
			core::log('loaded extension: %s', $_if);
		}
		
		// run after "extensions"
		self::_run_after('extensions');
		
		// enable request processing
		self::$env->request->processing(true);
		
		// preload modules
		core::log('preloading modules...');
		foreach (glob(self::$env->path->absolute . '/' . self::$env->path->workspace . '/modules/*/*.module.php') as $_mf)
		{
			require_once($_mf);
			
			$_mm = basename($_mf, '.module.php');
			$_mn = sprintf('\\misty\\%s_module', $_mm);
			
			if (class_exists($_mn))
			{
				$_mr = new \ReflectionClass($_mn);
				
				// module meta
				$mod[$_mm] = [
					'ref'		=> $_mr,
					'attr'		=> null,
					'file'		=> $_mf,
					'name'		=> $_mm
				];
				
				// attributes
				foreach ($_mr->getAttributes() as $_attr)
					$mod[$_mm]['attr'][str_replace('misty\\attr\\', '', $_attr->getName())] = $_attr->newInstance();
				
				// call preload action
				if ($_mr->hasMethod('__preload'))
				{
					if (!isset($mod[$_mm]['attr']['user']) || !$mod[$_mm]['attr']['user']->require_auth() || ($mod[$_mm]['attr']['user']->require_auth() && isset(self::$env->user) && self::$env->user->auth && self::$env->user->has_access($_mm)))
					{
						$pre = $_mr->getMethod('__preload');
						$obj = $_mr->newInstanceWithoutConstructor();
						$pre->invoke($obj);
						unset($obj, $pre);
					}
				}
			}
		}
		
		// run after "preload"
		self::_run_after('preload');
		
		// process module
		$_mn = self::$env->request->module(isset(self::$env->user, self::$env->user->data->role_module) && self::$env->user->auth ? self::$env->user->data->role_module : $module);
		if (isset($mod[$_mn]))
		{
			$_mm = $mod[$_mn];
			if (isset($_mm['attr']['user']) && $_mm['attr']['user']->require_auth() && (!isset(self::$env->user) || !self::$env->user->auth))
			{
				core::log('user authorization required to access module "%s"', $_mn);
				$run = false;
			}
			
			// create module instance
			if ($run)
			{
				core::log('creating instance...');
				self::$env->instance = new obj([
					'name'		=> $_mm['name'],
					'file'		=> $_mm['file'],
					'path'		=> dirname($_mm['file']),
					'action'	=> self::$env->request->action(isset($_mm['attr']['defaults']) ? $_mm['attr']['defaults']->action() : null),
					'params'	=> new obj,
					'object'	=> null,
					'default'	=> self::$env->request->action(isset($_mm['attr']['defaults']) ? $_mm['attr']['defaults']->action() : null) === (isset($_mm['attr']['defaults']) ? $_mm['attr']['defaults']->action() : null),
					'relpath'	=> ltrim(str_replace(self::$env->path->absolute, '', dirname($_mm['file'])), '/'),
					'need_auth'	=> isset($_mm['attr']['user']) && $_mm['attr']['user']->require_auth()
				]);
				
				// reload i18n, if loaded
				i18n::reload();
				
				// create module object instance
				self::$env->instance->object = $_mm['ref']->newInstance(...$args);
			
				// process module instance
				if (!isset(self::$env->instance->object->_break) || self::$env->instance->object->_break === false)
				{
					if (isset($_mm['attr']['defaults']) && $_mm['attr']['defaults']->fallback() && $_mm['attr']['defaults']->action() !== null && (!$_mm['ref']->hasMethod(self::$env->instance->action) || !$_mm['ref']->getMethod(self::$env->instance->action)->isPublic()))
					{
						self::$env->instance->action_requested = self::$env->instance->action;
						self::$env->instance->action = $_mm['attr']['defaults']->action();
					}
					
					if (self::$env->instance->action !== null)
					{
						if (isset($_mm['attr']['defaults']) && $_mm['attr']['defaults']->fallback() && (!$_mm['ref']->hasMethod(self::$env->instance->action) || !$_mm['ref']->getMethod(self::$env->instance->action)->isPublic()))
						{
							$_me = array_filter($_mm['ref']->getMethods(\ReflectionMethod::IS_PUBLIC), function (& $item) {
								return (isset($item->name) && strpos($item->name, '__') === 0) ? false : true;
							});
							if (!empty($_me))
								self::$env->instance->action = array_shift($_me)->name;
							else
								throw new exception(self::$env->i18n->_s('Module does not expose any public methods: %s', $_mn));
						}
						
						if ($_mm['ref']->hasMethod(self::$env->instance->action) && $_mm['ref']->getMethod(self::$env->instance->action)->isPublic())
						{
							// check if allowed
							if (self::$env->instance->need_auth && isset(self::$env->user) && self::$env->user->auth && !self::$env->user->has_access($_mm['name'], self::$env->instance->action))
								throw new exception(self::$env->i18n->_s('Access denied: %s', $_mn . '→' . self::$env->instance->action));
							
							$_me = $_mm['ref']->getMethod(self::$env->instance->action);
							core::log('call module "%s", action "%s" (args %s)', $_mn, $_me->name, json_encode(self::$env->request->params()));
							
							foreach ($_me->getParameters() as $_p)
								self::$env->instance->params->{$_p->getPosition()} =
									self::$env->request->param($_p->getPosition(), self::$env->request->param($_p->getName(), $_p->isDefaultValueAvailable() ? $_p->getDefaultValue() : null));
							$_me->invokeArgs(self::$env->instance->object, self::$env->instance->params->get());
						}
						else
							throw new exception(self::$env->i18n->_s('Action not found: %s', $_mn . '→' . self::$env->instance->action));
					}
					else
						throw new exception(self::$env->i18n->_s('No suitable action found in module: %s', $_mn));
				}
			}
		}
		else
			throw new exception(self::$env->i18n->_s('Module not found: %s', $_mn));
		
		// run after "module"
		self::_run_after('module');
	}
	
	private function _get_lib (string $name): mixed
	{
		if (isset(self::$env->path->core))
		{
			$lib = explode('\\', $name);
			if (count($lib = explode('_', end($lib))) > 1)
				return ['path' => self::$env->path->core, 'type' => array_pop($lib), 'name' => implode('_', $lib)];
		}
		return false;
	}
	
	private static function _run_after (string $event): void
	{
		core::log('_run_after: %s', $event);
		
		// process attached callbacks ("events")
		if (isset(self::$run[$event]) && is_array(self::$run[$event]) && !empty(self::$run[$event]))
			foreach (self::$run[$event] as $callable)
				call_user_func($callable);
	}
};