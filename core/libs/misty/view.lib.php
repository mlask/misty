<?php
namespace misty;
class view
{
	const PAGE = 'page';
	const MENU = 'menu';
	
	private $stack = null;
	private $assets = null;
	private $smarty = null;
	private static $instance = null;
	
	public static function load (): ?self
	{
		if (self::$instance === null)
			self::$instance = new static;
		return self::$instance;
	}
	
	private function __construct ()
	{
		// create cache directory, if not exists
		if (!file_exists(core::env()->path->cache . '/views'))
			mkdir(core::env()->path->cache . '/views', 0777, true);
		
		// Smarty engine
		$this->smarty = new \Smarty;
		$this->smarty->setTemplateDir(core::env()->path->absolute . '/' . core::env()->path->workspace . '/views/');
		$this->smarty->setCompileDir(core::env()->path->cache . '/views');
		$this->smarty->setCacheDir(core::env()->path->cache . '/views');
		$this->smarty->setCaching(\Smarty::CACHING_OFF);
		
		// map some of standard PHP functions as modifiers (due to deprecation in Smarty)
		foreach (['basename', 'dirname', 'filemtime', 'floatval', 'json_encode', 'ltrim', 'print_r', 'rtrim', 'sprintf', 'trim', 'urlencode', 'memory_get_usage', 'memory_get_peak_usage'] as $fn)
			$this->smarty->registerPlugin('modifier', $fn, fn(...$args) => $fn(...$args));
		
		// custom modifiers
		$this->smarty->registerPlugin('modifier', 'fsize', function ($input, $decimals = 2, $class = null) {
			$input = max(0, (int)$input);
			$units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
			$power = $input > 0 ? floor(log($input, 1024)) : 0;
			return sprintf($class !== null ? '%1$s <span class="%3$s">%2$s</span>' : '%1$s %2$s', number_format($input / pow(1024, $power), $decimals, ',', ' '), $units[$power], $class);
		});
		$this->smarty->registerPlugin('modifier', 'ftime', function ($input, $format = 'Y-m-d H:M:S') {
			return (new \DateTime($input))->format($format);
		});
		$this->smarty->registerPlugin('modifier', 'ftimes', function ($input) {
			return sprintf('%0d:%02d:%02d', $i = floor($input / 3600), floor(($input - ($i * 3600)) / 60) % 60, $input % 60);
		});
		$this->smarty->registerPlugin('modifier', 'if_true', function ($input, $output = '') {
			return (bool)$input ? $output : null;
		});
		
		// custom functions
		$this->smarty->registerPlugin('function', 'form_class', function ($params, $smarty) {
			if (isset($params['form'], $params['field']) && $params['form'] instanceof form)
			{
				$class = [];
				if ($params['form']->is_sent && !$params['form']->{$params['field'] . '__valid'})
					$class[] = 'is-danger';
				elseif ($params['form']->is_sent && $params['form']->{$params['field'] . '__valid'})
					$class[] = 'is-success';
				return !empty($class) ? (!isset($params['space']) || $params['space'] === true ? ' ' : '') . implode(' ', $class) : '';
			}
			return '';
		});
		$this->smarty->registerPlugin('function', 'form_error', function ($params, $smarty) {
			if (isset($params['form'], $params['template']) && $params['form'] instanceof form)
			{
				$output = '';
				if (isset($params['field']))
				{
					if ($params['form']->is_sent && !$params['form']->{$params['field'] . '__valid'})
						if (is_array($params['form']->{$params['field'] . '__errors'}))
							foreach ($params['form']->{$params['field'] . '__errors'} as $error_msg)
								$output .= sprintf($params['template'], $error_msg);
				}
				return $output;
			}
			return '';
		});
		
		// debug/production mode
		if (core::env()->request->getd('debug', false, REQUEST_VALUE_TYPE::BOOL))
		{
			$this->smarty->clearCompiledTemplate();
		}
		else
		{
			$this->smarty->registerFilter('post', function ($output, \Smarty_Internal_Template $template) {
				$output = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $output);
				$output = preg_replace("/[\r\n]+?(\<\?php)/", '$1', $output);
				$output = preg_replace("/^\t+/m", '', $output);
				$output = str_replace('<ul>\s*?</ul>', '', $output);
				$output = preg_replace("/[\r\n]/", '', $output);
				$output = preg_replace('/(\<\?php)([^\s])/', '$1 $2', $output);
				return $output;
			});
		}
		
		// convert errors to exceptions -- yes, `NOTICE` IS AN ERROR!
		set_error_handler(function ($errno, $errstr, $errfile, $errline) {
			$errtype = [
				E_ERROR => 'E_ERROR',
				E_WARNING => 'E_WARNING',
				E_PARSE => 'E_PARSE',
				E_NOTICE => 'E_NOTICE',
				E_CORE_ERROR => 'E_CORE_ERROR',
				E_CORE_WARNING => 'E_CORE_WARNING',
				E_COMPILE_ERROR => 'E_COMPILE_ERROR',
				E_COMPILE_WARNING => 'E_COMPILE_WARNING',
				E_USER_ERROR => 'E_USER_ERROR',
				E_USER_WARNING => 'E_USER_WARNING',
				E_USER_NOTICE => 'E_USER_NOTICE',
				E_STRICT => 'E_STRICT',
				E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
				E_DEPRECATED => 'E_DEPRECATED',
				E_USER_DEPRECATED => 'E_USER_DEPRECATED',
			];
			throw new exception(sprintf('%s: %s', $errtype[$errno], $errstr), $errfile, $errline);
		});
		
		// enable output buffering
		ob_start();
	}
	
	public function assets (string ...$items): void
	{
		if (is_array($items) && !empty($items))
		{
			foreach ($items as $item)
			{
				$key = null;
				$path = null;
				
				$item = preg_replace_callback('/\[(.+?)\]/', function (array $m) use (& $path) { $path = $m[1]; return ''; }, $item);
				$item = preg_replace_callback('/\{(.+?)\}/', function (array $m) use (& $key) { $key = $m[1]; return ''; }, $item);
				
				$file_path = ($path === 'module' ? core::env()->instance->path . '/js' : core::env()->path->absolute . '/' . core::env()->path->workspace . '/js') . '/' . $item;
				$file_relpath = ($path === 'module' ? core::env()->instance->relpath . '/js' : core::env()->path->workspace . '/js') . '/' . $item;
				
				if (file_exists($file_path))
				{
					$info = pathinfo($file_path);
				
					if ($key === null && strtolower($info['extension']) === 'js')
						$key = 'js';
					elseif ($key === null && strtolower($info['extension']) === 'css')
						$key = 'css';
				
					if (!isset($this->assets[$key]))
						$this->assets[$key] = [];
				
					$this->assets[$key][md5($file_path)] = [
						'relpath'	=> $file_relpath,
						'path'		=> $file_path,
						'type'		=> $key,
						'ts'		=> filemtime($file_path)
					];
				}
			}
		}
	}
	
	public function assign (mixed $variable, mixed $value = null): void
	{
		$this->smarty->assign($variable, $value);
	}
	
	public function render (string $template, ?string $target = null): void
	{
		$key = null;
		$template = preg_replace_callback('/\{(.+?)\}/', function (array $m) use (& $key) { $key = $m[1]; return ''; }, $template);
		
		if ($key !== null)
			$this->stack[$target ?: 'content'][$key] = $template;
		else
			$this->stack[$target ?: 'content'][] = $template;
	}
	
	public function display (string $template): void
	{
		$this->_preflight();
		
		// disable output buffering
		$this->smarty->assign('core_buffer', ob_get_contents());
		ob_end_clean();
		
		// draw template
		$this->smarty->display($template);
	}
	
	public function fetch (string $template): ?string
	{
		$this->_preflight();
		
		return $this->smarty->fetch($template);
	}
	
	public function flush (string $template = 'index.tpl'): void
	{
		$views = [];
		$this->_preflight();
		
		// view stack
		if (is_array($this->stack) && !empty($this->stack))
		{
			foreach ($this->stack as $target => $tviews)
			{
				foreach ($tviews as $view)
				{
					if (!isset($views[$target]))
						$views[$target] = '';
					
					$views[$target] .= $this->smarty->fetch($view);
				}
			}
		}
		
		// output variables
		$this->smarty->assign([
			'core_view'		=> $views,
			'core_buffer'	=> ob_get_contents()
		]);
		
		// disable output buffering
		while (ob_get_length())
			ob_end_clean();
		
		// display output
		$this->smarty->display($template);
	}
	
	public function __debugInfo (): array
	{
		return [
			'stack'		=> $this->stack,
			'smarty'	=> [
				'template_dir'	=> $this->smarty->getTemplateDir(),
				'caching'		=> $this->smarty->caching,
				'debug'			=> (int)core::env()->request->getd('debug', false, REQUEST_VALUE_TYPE::BOOL),
				'vars'			=> array_keys($this->smarty->getTemplateVars())
			]
		];
	}
	
	private function _preflight (): void
	{
		// module views
		if (isset(core::env()->instance))
			$this->smarty->addTemplateDir(core::env()->instance->path . '/views', 'module');
		
		// global variables
		$this->smarty->assign([
			'core_env'		=> core::env(),
			'core_log'		=> core::log(),
			'core_debug'	=> core::env()->request->getd('debug', false, REQUEST_VALUE_TYPE::BOOL),
			'view_assets'	=> $this->assets,
			'i18n'			=> & core::env()->i18n
		]);
	}
};