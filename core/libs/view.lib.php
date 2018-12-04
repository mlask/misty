<?php
require_once('Rain/autoload.php');
use Rain\Tpl;

class view
{
	const page = 'content_page';
	const menu = 'content_menu';
	
	private static $instance = null;
	private $tpl = null;
	private $stack = null;
	
	public static function init ()
	{
		if (self::$instance === null)
			self::$instance = new self;
		return self::$instance;
	}
	
	/* ---------- Obsługa zmiennych i powiadomień ---------- */
	
	public function assign ($variable, $value = null)
	{
		$this->tpl->assign($variable, $value);
	}
	
	public function message ($class, $text, $delay = false)
	{
		session_start();
		$_SESSION['viewmsg'] = [
			'class'	=> $class,
			'text'	=> $text,
			'delay'	=> $delay
		];
		session_write_close();
	}
	
	/* ---------- Obsługa widoków ---------- */
	
	public function display ($view, $module = null, $target = null)
	{
		$this->stack[$target ?: 'content'][] = $module ? core::env()['path']['absolute'] . '/' . core::env()['path']['workspace'] . '/modules/' . $module . '/views/' . $view : $view;
	}
	
	public function display_single ($view, $module = null)
	{
		$this->tpl->draw(str_replace('.tpl', '', $module ? core::env()['path']['absolute'] . '/' . core::env()['path']['workspace'] . '/modules/' . $module . '/views/' . $view : $view));
	}
	
	public function fetch ($view)
	{
		return $this->tpl->draw(str_replace('.tpl', '', $view), true);
	}
	
	public function fetch_mod ($view, $module = null)
	{
		return $this->tpl->draw(str_replace('.tpl', '', $module ? core::env()['path']['absolute'] . '/' . core::env()['path']['workspace'] . '/modules/' . $module . '/views/' . $view : $view), true);
	}
	
	public function flush ($view = 'index')
	{
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
	
	private function __construct ()
	{
		// usunięcie starych plików
		foreach (glob(core::env()['path']['cache'] . '/' . core::env()['path']['workspace'] . '/views/*.rtpl.php') as $rt)
		{
			if (filemtime($rt) < time() - 3600)
			{
				core::log('view cache removed: ' . $rt);
				unlink($rt);
			}
		}
		
		// utworzenie instancji
		session_start();
		if (!core::env()['request']->getd('debug', false, request::type_bool))
			Tpl::registerPlugin(new Tpl\Plugin\Cleanup);
		Tpl::configure([
			'tpl_ext'			=> 'tpl',
			'tpl_dir'			=> core::env()['path']['absolute'] . '/' . core::env()['path']['workspace'] . '/views/',
			'cache_dir'			=> core::env()['path']['cache'] . '/views/',
			'sandbox'			=> false,
			'remove_comments'	=> true,
			'debug'				=> core::env()['request']->getd('debug', false, request::type_bool)
		]);
		$this->tpl = new Tpl;
		$this->tpl->assign([
			'vf'		=> new view_functions,
			'viewmsg'	=> isset($_SESSION['viewmsg']) ? $_SESSION['viewmsg'] : false,
			'translate'	=> i18n::init()
		]);
		if (isset($_SESSION['viewmsg']))
		{
			$_SESSION['viewmsg'] = null;
			unset($_SESSION['viewmsg']);
		}
		session_write_close();
	}
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
	
	public function set_class ()
	{
		$pair = array_chunk(func_get_args(), 2);
		$outc = [];
		foreach ($pair as $_p)
			if ((bool)$_p[0] === true)
				$outc[] = $_p[1];
		if (!empty($outc))
			return sprintf(' class="%s"', implode(' ', $outc));
		return null;
	}
}