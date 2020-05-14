<?php
namespace misty;
class view
{
	const PAGE = 'page';
	const MENU = 'menu';
	
	private $stack = null;
	private $smarty = null;
	private static $instance = null;
	
	public static function init ()
	{
		if (self::$instance === null)
			self::$instance = new self;
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
		$this->smarty->setCaching(core::env()->request->getd('debug', false, request::TYPE_BOOL) ? \Smarty::CACHING_OFF : \Smarty::CACHING_LIFETIME_CURRENT);
		
		// custom modifiers
		$this->smarty->registerPlugin('modifier', 'ftime', function ($input, $format = '%Y-%m-%d %H:%M:%S') {
			return strftime($format, $input);
		});
		$this->smarty->registerPlugin('modifier', 'ftimes', function ($input) {
			return sprintf('%0d:%02d:%02d', $i = floor($input / 3600), floor(($input - ($i * 3600)) / 60) % 60, $input % 60);
		});
		$this->smarty->registerPlugin('modifier', 'set_class', function (...$args) {
			$pair = array_chunk($args, 2);
			$outc = [];
			foreach ($pair as $_p)
				if ((bool)$_p[0] === true)
					$outc[] = $_p[1];
			if (!empty($outc))
				return sprintf(' class="%s"', implode(' ', $outc));
			return null;
		});
		
		// debug/production mode
		if (core::env()->request->getd('debug', false, request::TYPE_BOOL))
		{
			$this->smarty->clearAllCache();
		}
		else
		{
			$this->smarty->registerFilter('post', function ($output, \Smarty_Internal_Template $template) {
				$output = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $output);
				$output = preg_replace("/[\r\n]+?(\<\?php)/", '$1', $output);
				$output = preg_replace("/^\t+/m", '', $output);
				$output = preg_replace("/[\r\n]/", '', $output);
				$output = str_replace('<ul>\s*?</ul>', '', $output);
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
	
	public function assign ($variable, $value = null)
	{
		$this->smarty->assign($variable, $value);
	}
	
	public function render ($template, $target = null)
	{
		$this->stack[$target ?: 'content'][] = $template;
	}
	
	public function display ($template)
	{
		$this->_preflight();
		
		// disable output buffering
		$this->smarty->assign('core_buffer', ob_get_contents());
		ob_end_clean();
		
		// draw template
		$this->smarty->display($template);
	}
	
	public function fetch ($template)
	{
		$this->_preflight();
		
		return $this->smarty->fetch($template);
	}
	
	public function flush ($template = 'index.tpl')
	{
		$views = [];
		$this->_preflight();
		
		// view stack
		if (is_array($this->stack) && !empty($this->stack))
		{
			foreach ($this->stack as $target => $views)
			{
				foreach ($views as $view)
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
		ob_end_clean();
		
		// display output
		$this->smarty->display($template);
	}
	
	private function _preflight ()
	{
		// module views
		if (isset(core::env()->instance))
			$this->smarty->addTemplateDir(core::env()->instance->path . '/views', 'module');
		
		// global variables
		$this->smarty->assign([
			'core_env'		=> core::env(),
			'core_log'		=> core::log(),
			'core_debug'	=> core::env()->request->getd('debug', false, request::TYPE_BOOL),
			'translate'		=> core::env()->i18n
		]);
	}
}