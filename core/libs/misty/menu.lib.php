<?php
class menu
{
	private $menu = null;
	private static $instance = null;
	
	public static function init ()
	{
		if (self::$instance === null)
			self::$instance = new self;
		return self::$instance;
	}
	
	private function __construct ()
	{
		$this->menu = null;
	}
	
	public function add_item ($name, $action = false, $order = 0, $badge = false, array $submenu = null)
	{
		if ($action === false)
		{
			$dbg = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			$action = basename($dbg[1]['class'], '_module');
			unset($dbg);
		}
		if ($submenu !== null)
		{
			foreach ($submenu as $_n => & $_s)
			{
				$_s = [
					'name'		=> isset($_s['name']) ? $_s['name'] : $_n,
					'action'	=> isset($_s['action']) ? $_s['action'] : $_s
				];
			}
			$submenu = array_values($submenu);
		}
		$this->menu[] = [
			'name'		=> $name,
			'action'	=> $action,
			'order'		=> $order,
			'badge'		=> $badge,
			'submenu'	=> !empty($submenu) ? $submenu : false
		];
		usort($this->menu, function ($a, $b) { return $a['order'] < $b['order'] ? -1 : 1; });
		return $this;
	}
	
	public function clear ()
	{
		$this->menu = null;
		return $this;
	}
	
	public function get ()
	{
		return $this->menu;
	}
}