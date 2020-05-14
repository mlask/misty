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