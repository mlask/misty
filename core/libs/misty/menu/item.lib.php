<?php
namespace misty\menu;
class item
{
	private $name = null;
	private $order = 0;
	private $action = null;
	private $submenu = null;
	
	public function __construct ($name, $action = null)
	{
		$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'];
		$this->name = $name;
		$this->action = $action === null ? (preg_match('/^misty\\\(.+?)_module$/', $caller, $match) ? $match[1] : './') : $action;
	}
	
	public function __get ($name)
	{
		return isset($this->{$name}) ? $this->{$name} : null;
	}
	
	public function __isset ($name)
	{
		return isset($this->{$name});
	}
	
	public function add (...$items)
	{
		if (!empty($items))
			foreach ($items as & $item)
				if ($item instanceof \misty\menu\item)
					$this->submenu[] = $item;
		return $this;
	}
	
	public function clear ()
	{
		$this->submenu = null;
		return $this;
	}
	
	public function reorder ()
	{
		if (is_array($this->submenu) && !empty($this->submenu))
		{
			if (isset(\misty\core::env()->user) && \misty\core::env()->user->auth)
			{
				$this->submenu = array_filter($this->submenu, function ($item) {
					$item_action = $item->get_action(true);
					return \misty\core::env()->user->has_access($item_action['module'], $item_action['action']);
				});
			}
			
			usort($this->submenu, function ($item_a, $item_b) {
				return $item_a->get_order() > $item_b->get_order() ? 1 : -1;
			});
			foreach ($this->submenu as & $item)
				$item->reorder();
		}
	}
	
	public function get_order ()
	{
		return (int)$this->order;
	}
	
	public function get_action ($split = false)
	{
		if ($split)
		{
			$action = explode('/', $this->action);
			return count($action) > 1 ? ['module' => array_shift($action), 'action' => array_shift($action), 'params' => !empty($action) ? implode('/', $action) : null] : ['module' => $action[0], 'action' => null, 'params' => null];
		}
		return $this->action;
	}
	
	public function set_order ($value = 0)
	{
		$this->order = $value;
		return $this;
	}
	
	public function set_badge ($value = null)
	{
		$this->badge = $value;
		return $this;
	}
};