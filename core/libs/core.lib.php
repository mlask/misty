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
			if (file_exists($file = sprintf('%s/%s.lib.php', __DIR__, strtr($name, '\\', '/'))))
				require_once($file);
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
				'callee'	=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0],
				'message'	=> count($message) > 1 ? vsprintf(array_shift($message), $message) : array_shift($message)
			];
		}
		return self::$log;
	}
}