<?php
namespace misty;
class i18n
{
	const DEFAULT_LANG = null;
	
	private static $instance = null;
	private $cache = null;
	private $data = null;
	private $lang = null;
	
	public static function load (): ?self
	{
		if (self::$instance === null)
			self::$instance = new static;
		return self::$instance;
	}
	
	public static function reload (): void
	{
		if (self::$instance !== null)
			self::$instance->_reload();
	}
	
	public static function lang (string|bool $lang = false): mixed
	{
		if ($lang === false)
		{
			if (self::$instance !== null)
				return self::$instance->lang;
		}
		else
		{
			if (self::$instance === null)
				self::$instance = new static;
			
			self::$instance->lang = $lang;
			return self::$instance;
		}
		
		return false;
	}
	
	public static function detect (bool $primary = false): mixed
	{
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) > 0)
		{
			$l = array_map(function ($_l) { $_l = explode(';', $_l, 2); return [strtolower(substr($_l[0], 0, 2)), isset($_l[1]) ? (float)substr($_l[1], 2) : 1]; }, explode(',', trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
			if (count($l) > 0)
			{
				usort($l, function ($a, $b) { return $a[1] < $b[1] ? 1 : -1; });
				if ($primary)
					return $l[0][0];
				else
					return array_column($l, 1, 0);
			}
		}
		
		return null;
	}
	
	/* ---------- Funkcje publiczne ---------- */
	
	public function _ (mixed ...$a): string
	{
		$l = [
			'label'	=> (count($a) > 0 ? array_shift($a) : null),
			'lang'	=> (count($a) > 0 ? array_shift($a) : null) ?: $this->lang
		];
		return $l['lang'] === null ? $this->_cleanup($l['label']) : (isset($this->data[$l['lang']][$l['label']]) ? $this->data[$l['lang']][$l['label']] : $this->_cleanup($l['label']));
	}
	
	public function _s (mixed ...$a): string
	{
		$l = [
			'label'	=> (count($a) > 0 ? array_shift($a) : null),
			'lang'	=> ($this->lang),
			'vars'	=> (count($a) > 0 ? $a : null)
		];
		return vsprintf($l['lang'] === null ? $this->_cleanup($l['label']) : (isset($this->data[$l['lang']][$l['label']]) ? $this->data[$l['lang']][$l['label']] : $this->_cleanup($l['label'])), $l['vars']);
	}
	
	public function _sl (mixed ...$a): string
	{
		$l = [
			'label'	=> (count($a) > 0 ? array_shift($a) : null),
			'lang'	=> (count($a) > 0 ? array_shift($a) : null) ?: $this->lang,
			'vars'	=> (count($a) > 0 ? $a : null)
		];
		return vsprintf($l['lang'] === null ? $this->_cleanup($l['label']) : (isset($this->data[$l['lang']][$l['label']]) ? $this->data[$l['lang']][$l['label']] : $this->_cleanup($l['label'])), $l['vars']);
	}
	
	/* ---------- Funkcje prywatne lub wykonywane automatycznie ---------- */
	
	private function __construct () 
	{
		$this->lang = self::DEFAULT_LANG ?: self::detect(true);
		$this->_reload();
		core::log('initialized i18n with language set to: %s', $this->lang);
	}
	
	private function _cleanup (string $input): string
	{
		if (($pos = strpos($input, ' ~~')) !== false)
			return substr($input, 0, $pos);
		return $input;
	}
	
	protected function _reload (): void
	{
		// globalne pliki językowe
		foreach (glob(core::env()->path->core . '/i18n/*.lang.php') as $lang)
		{
			if (!isset($this->cache[md5($lang)]))
			{
				$ltmp = include($lang);
				if (is_array($ltmp))
				{
					$this->cache[md5($lang)] = $lang;
					$this->data[basename($lang, '.lang.php')] = $ltmp;
					core::log('loaded global i18n file: %s', $lang);
				}
				$ltmp = null;
			}
		}
		
		// pliki językowe aktualnego modułu
		if (core::env()->instance)
		{
			foreach (glob(core::env()->instance->path . '/i18n/*.lang.php') as $lang)
			{
				if (!isset($this->cache[md5($lang)]))
				{
					$ltmp = include($lang);
					if (is_array($ltmp))
					{
						$this->cache[md5($lang)] = $lang;
						$this->data[basename($lang, '.lang.php')] = isset($this->data[basename($lang, '.lang.php')]) ? array_merge($this->data[basename($lang, '.lang.php')], $ltmp) : $ltmp;
						core::log('loaded instance i18n file: %s', $lang);
					}
					$ltmp = null;
				}
			}
		}
		
		// usunięcie podpowiedzi z etykiet
		if (is_array($this->data) && !empty($this->data))
			foreach ($this->data as $lang => & $data)
				$data = array_map([$this, '_cleanup'], $data);
	}
};