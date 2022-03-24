<?php
namespace misty;
class menu
{
	private static $instance = null;
	private $menu = null;
	
	public static function load (): ?self
	{
		if (self::$instance === null)
			self::$instance = new static;
		return self::$instance;
	}
	
	public static function __callStatic (string $name, array $args = null): mixed
	{
		if (class_exists($class = '\\misty\\menu\\' . $name))
			return new $class(...$args);
	}
	
	public function __construct ()
	{
		// register in core
		core::env()->set(['menu' => $this]);
	}
	
	public function add (\misty\menu\item ...$items): self
	{
		if (!empty($items))
			foreach ($items as & $item)
				if ($item instanceof \misty\menu\item)
					$this->menu[] = $item;
		return $this;
	}
	
	public function get (): ?self
	{
		if (is_array($this->menu) && !empty($this->menu))
		{
			if (isset(core::env()->user) && core::env()->user->auth)
			{
				$this->menu = array_filter($this->menu, function ($item) {
					$item_action = $item->get_action(true);
					return core::env()->user->has_access($item_action['module'], $item_action['action']);
				});
			}
			
			usort($this->menu, function ($item_a, $item_b) {
				return $item_a->get_order() > $item_b->get_order() ? 1 : -1;
			});
			
			foreach ($this->menu as & $item)
				$item->reorder();
		}
		
		return $this->menu;
	}
	
	public function clear (): self
	{
		$this->menu = null;
		
		return $this;
	}
};