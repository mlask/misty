<?php
namespace misty;
class view
{
	const PAGE = 'content_page';
	const MENU = 'content_menu';
	
	private $i18n = null;
	private $vars = null;
	private $stack = null;
	
	public function __construct ()
	{
		ob_start();
		$this->i18n = i18n::init();
		
		set_exception_handler(function ($exception) {
			$this->vars['core_exception'][] = $exception;
		});
	}
	
	public function __destruct ()
	{
		$this->flush();
	}
	
	public function assign ($variable, $value = null)
	{
		if (is_array($variable) && $value === null)
		{
			foreach ($variable as $name => $value)
				$this->vars[$name] = $value;
		}
		else
			$this->vars[$variable] = $value;
	}
	
	public function display ($view, $target = null)
	{
		$this->stack[$target ?: 'content'][] = $view;
	}
	
	/*
	public function display_single ($view, $module = null)
	{
		$this->tpl->draw(str_replace('.tpl', '', $module ? core::env()->path->absolute . '/' . core::env()->path->workspace . '/modules/' . $module . '/views/' . $view : $view));
	}
	
	public function fetch ($view)
	{
		return $this->tpl->draw(str_replace('.tpl', '', $view), true);
	}
	
	public function fetch_mod ($view, $module = null)
	{
		return $this->tpl->draw(str_replace('.tpl', '', $module ? core::env()->path->absolute . '/' . core::env()->path->workspace . '/modules/' . $module . '/views/' . $view : $view), true);
	}
	*/
	
	public function flush ($view = 'index.html')
	{
		// default loader for workspace views
		$loader = new \Twig\Loader\FilesystemLoader(core::env()->path->absolute . '/' . core::env()->path->workspace . '/views');
		
		// module views
		if (isset(core::env()->instance))
			$loader->addPath(core::env()->instance->path . '/views', 'module');
		
		// initialize Twig
		$twig = new \Twig\Environment($loader, [
			'debug'			=> core::env()->request->getd('debug', false, request::TYPE_BOOL),
			'cache'			=> core::env()->path->cache . '/views',
		]);
		
		// filters
		$twig->addFunction(new \Twig\TwigFunction('_', [$this->i18n, '_']));
		$twig->addFunction(new \Twig\TwigFunction('_s', [$this->i18n, '_s']));
		$twig->addFunction(new \Twig\TwigFunction('_sl', [$this->i18n, '_sl']));
		
		// global vars
		$this->vars['core_env'] = core::env();
		$this->vars['core_log'] = core::log();
		$this->vars['core_debug'] = core::env()->request->getd('debug', false, request::TYPE_BOOL);
		$this->vars['core_buffer'] = ob_get_contents();
		
		// output vars
		$vars = is_array($this->vars) ? $this->vars : [];
		
		if (is_array($this->stack))
		{
			foreach ($this->stack as $target => $views)
			{
				foreach ($views as $template)
				{
					if (!isset($vars['view'][$target]))
						$vars['view'][$target] = '';
					
					$vars['view'][$target] .= $twig->render($template, is_array($this->vars) ? $this->vars : []);
				}
			}
		}
		
		ob_end_clean();
		
		$twig->display($view, $vars);
		/*
		
		
		
		try
		{
			$stack_content = null;
			foreach ((array)$this->stack as $target => $views)
			{
				$stack_content[$target] = null;
				foreach ($views as $_view)
					$stack_content[$target] .= $this->tpl->draw(str_replace('.tpl', '', $_view), true);
			}
			$this->tpl->assign(['view' => $stack_content]);
		}
		catch (exception $ex)
		{
			$this->tpl->assign([
				'core' => [
					'exception' => [
						'message'	=> $ex->getMessage(),
						'code'		=> $ex->getCode(),
						'file'		=> $ex->getFile(),
						'line'		=> $ex->getLine(),
						'trace'		=> $ex->getTrace()
					]
				]
			], null, true);
		}
		$this->tpl->draw(str_replace('.tpl', '', $view));
		*/
	}
	
	public function clear ($target = null)
	{
		if ($target !== null)
		{
			$target = is_array($target) ? $target : array($target);
			foreach ($target as $_target)
			{
				$this->stack[$_target] = null;
				unset($this->stack[$_target]);
			}
		}
		else
			$this->stack = null;
	}
	
	/* ---------- Funkcje prywatne ---------- */
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

class view_functions
{
	public function ftime ($time, $format = '%Y-%m-%d %H:%M:%S')
	{
		return strftime($format, $time);
	}
	
	public function ftimes ($s)
	{
		return sprintf('%0d:%02d:%02d', $i = floor($s / 3600), floor(($s - ($i * 3600)) / 60) % 60, $s % 60);
	}
	
	public function set_class (...$args)
	{
		$pair = array_chunk($args, 2);
		$outc = [];
		foreach ($pair as $_p)
			if ((bool)$_p[0] === true)
				$outc[] = $_p[1];
		if (!empty($outc))
			return sprintf(' class="%s"', implode(' ', $outc));
		return null;
	}
}