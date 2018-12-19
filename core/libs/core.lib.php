<?php
class core
{
	const version = 3.10;
	const version_date = 20181203;
	
	private static $env = null;
	private static $log = null;
	
	public function __construct ($path = null)
	{
		$this->_env($path);
		$this->_logic();
		$this->_view();
	}
	
	/* ---------- Publiczne funkcje statyczne ---------- */
	
	public static function log ($log)
	{
		$t = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
		self::$log[] = [
			'ts'	=> time(),
			'text'	=> $log,
			'call'	=> (isset($t[1]['class']) ? $t[1]['class'] . $t[1]['type'] : '') . $t[1]['function'],
			'file'	=> $t[0]['file'],
			'line'	=> $t[0]['line']
		];
		$t = null;
		unset($t);
	}
	
	public static function env ($include = null, $overwrite = false)
	{
		if ($include !== null && is_array($include))
		{
			foreach ($include as $ik => $iv)
				if ($overwrite || !isset(self::$env[$ik]))
					self::$env[$ik] = $iv;
		}
		return self::$env;
	}
	
	public static function config ($key)
	{
		if (file_exists(self::$env['path']['absolute'] . '/core/config/config.php'))
		{
			ob_start();
			$config = include(self::$env['path']['absolute'] . '/core/config/config.php');
			ob_end_clean();
			if (isset($config[$key]))
				return $config[$key];
		}
		return false;
	}
	
	/* ---------- Funkcje systemowe ---------- */
	
	private function _env ($path)
	{
		// konfiguracja
		self::$env = [
			'core'		=> sprintf('Misty Core v%0.2f-d%d', self::version, self::version_date),
			'uuid'		=> sprintf('misty3-%04x', crc32(realpath(dirname(dirname(dirname(__FILE__)))))),
			'root'		=> realpath(dirname(dirname(dirname(__FILE__)))),
			'local'		=> isset($_SERVER['SERVER_ADDR']) && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'],
			'path'		=> [
				'workspace'	=> $path !== null ? $path : basename(dirname($_SERVER['PHP_SELF']) !== '.' ? dirname($_SERVER['PHP_SELF']) : getcwd()),
				'relative'	=> dirname($_SERVER['SCRIPT_NAME']),
				'core'		=> dirname(dirname(__FILE__)),
				'absolute'	=> realpath(dirname(dirname(dirname(__FILE__)))),
				'data'		=> realpath(dirname(dirname(dirname(__FILE__)))) . '/data',
				'files'		=> realpath(dirname(dirname(dirname(__FILE__)))) . '/data/files',
				'cache'		=> realpath(dirname(dirname(dirname(__FILE__)))) . '/data/cache'
			],
			'cli'		=> php_sapi_name() === 'cli',
			'request'	=> null,
			'instance'	=> null,
			'exception'	=> false
		];
		
		// sprawdzenie katalogów
		if (!file_exists(self::$env['path']['cache']) || !is_writable(self::$env['path']['cache']))
			throw new exception('Cache directory not found or not writable: ' . self::$env['path']['cache']);
		
		// autoload, obsługa błędów i wyjątków
		spl_autoload_register([$this, '_autoload']);
		set_exception_handler([$this, 'handle_exception']);
		
		// konfiguracja - obiekty
		self::$env['request'] = new request;
		
		// identyfikator sesji
		session_name(self::$env['uuid']);
		
		// bufor wyjścia
		if (!self::$env['cli'])
			ob_start();
	}
	
	private function _autoload ($name, $ext = null)
	{
		// wyszukanie pliku w bibliotekach
		if (file_exists($n = sprintf('%s/core/libs/%s.lib.php', self::$env['path']['absolute'], $name)) ||
			file_exists($n = sprintf('%s/core/models/%s.model.php', self::$env['path']['absolute'], basename($name, '_model'))))
			require_once($n);
	}
	
	private function _logic ()
	{
		// inicjalizacja
		$mod = null;
		
		// przeszukanie modułów
		foreach (glob(self::$env['path']['absolute'] . '/' . self::$env['path']['workspace'] . '/modules/*/*.module.php') as $_mf)
		{
			require_once($_mf);
			$_mm = basename($_mf, '.module.php');
			$_mn = $_mm . '_module';
			
			if (class_exists($_mn))
			{
				$_mr = new ReflectionClass($_mn);
				$mod[$_mm] = [
					'ref'		=> $_mr,
					'file'		=> $_mf,
					'name'		=> $_mm,
					'default'	=> defined($_mn . '::default_action') ? $_mn::default_action : null,
					'fallback'	=> defined($_mn . '::default_fallback') ? $_mn::default_fallback : false
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
		
		// przeszukanie dołączeń
		foreach (array_merge((array)glob(self::$env['path']['absolute'] . '/core/extensions/*.ext.php'), (array)glob(self::$env['path']['absolute'] . '/' . self::$env['path']['workspace'] . '/extensions/*.ext.php')) as $_if)
		{
			require_once($_if);
			$_in = basename($_if, '.ext.php') . '_ext';
			if (class_exists($_in))
				new $_in;
			$_in = null;
			unset($_in);
		}
		
		// wykonanie modułu
		$_mn = self::$env['request']->module('main');
		if (isset($mod[$_mn]))
		{
			$_mm = $mod[$_mn];
			self::$env['instance'] = [
				'name'		=> $_mm['name'],
				'file'		=> $_mm['file'],
				'action'	=> self::$env['request']->action($_mm['default']),
				'params'	=> [],
				'object'	=> $_mm['ref']->newInstance()
			];
			
			if (!isset(self::$env['instance']['object']->__break) || self::$env['instance']['object']->__break === false)
			{
				if ($_mm['fallback'] && !$_mm['ref']->hasMethod(self::$env['instance']['action']))
				{
					self::$env['instance']['action_requested'] = self::$env['instance']['action'];
					self::$env['instance']['action'] = $_mm['default'];
				}
				
				if (!$_mm['ref']->hasMethod(self::$env['instance']['action']))
				{
					$_me = $_mm['ref']->getMethods(ReflectionMethod::IS_PUBLIC);
					if (!empty($_me))
						self::$env['instance']['action'] = array_shift($_me)->name;
					else
						throw new exception('Module does not have any public methods: ' . $_mn);
				}
				
				if ($_mm['ref']->hasMethod(self::$env['instance']['action']))
				{
					$_me = $_mm['ref']->getMethod(self::$env['instance']['action']);
					core::log('call ' . $_mn . '::' . $_me->name);
				
					foreach ($_me->getParameters() as $_p)
						self::$env['instance']['params'][$_p->getPosition()] = self::$env['request']->param($_p->getPosition(), self::$env['request']->param($_p->getName(), $_p->isDefaultValueAvailable() ? $_p->getDefaultValue() : null));
					$_me->invokeArgs(self::$env['instance']['object'], self::$env['instance']['params']);
				}
				else
					throw new exception('Action not found: ' . $_mn . '::' . self::$env['instance']['action']);
			}
		}
		else
			throw new exception('Module not found: ' . $_mn);
		
		// aktualizacja plików językowych
		i18n::reload();
		
		// porządki
		$mod = null;
		unset($mod);
	}
	
	private function _view ()
	{
		if (self::$env['cli'])
			return;
		$view = view::init();
		$view->assign([
			'core' => [
				'env'		=> self::env(),
				'log'		=> self::$log,
				'self'		=> self::$env['request']->self,
				'debug'		=> self::$env['request']->getd('debug', false, request::type_bool),
				'buffer'	=> ob_get_clean(),
				'version'	=> self::$env['core'],
				'instance'	=> self::$env['instance'],
				'exception'	=> self::$env['exception'],
			],
			'translate'	=> i18n::init()
		]);
		$view->flush(self::$env['request']->xhr ? 'xhr' : 'main');
	}
	
	public function handle_exception ($error)
	{
		if ($error instanceof Error || $error instanceof Exception)
		{
			if (self::$env['cli'])
				die(sprintf("(!) CORE EXCEPTION in %s, line %d\n%s\n '\n", $error->getFile(), $error->getLine(), preg_replace('/^(.*?)$/m', " |\t$1", $error->getMessage())));
			self::$env['exception'] = [
				'code'		=> $error->getCode(),
				'file'		=> $error->getFile(),
				'line'		=> $error->getLine(),
				'trace'		=> $error->getTrace(),
				'message'	=> $error->getMessage()
			];
		}
		$this->_view();
	}
}