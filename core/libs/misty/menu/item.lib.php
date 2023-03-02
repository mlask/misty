<?php
namespace misty\menu;
class item
{
	protected $name = null;
	protected $order = 0;
	protected $badge = null;
	protected $action = null;
	protected $submenu = null;
	
	public function __construct (string $name, ?string $action = null)
	{
		$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'];
		$this->name = $name;
		$this->action = $action === null ? (preg_match('/^misty\\\(.+?)_module$/', $caller, $match) ? $match[1] : './') : $action;
	}
	
	public function __get (string $name): mixed
	{
		return isset($this->{$name}) ? $this->{$name} : null;
	}
	
	public function __isset (string $name): bool
	{
		return isset($this->{$name});
	}
	
	public function add (\misty\menu\item ...$items): self
	{
		if (!empty($items))
			foreach ($items as & $item)
				if ($item instanceof \misty\menu\item)
					$this->submenu[] = $item;
		return $this;
	}
	
	public function clear (): self
	{
		$this->submenu = null;
		return $this;
	}
	
	public function reorder (): void
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
	
	public function get_order (): float
	{
		return (int)$this->order;
	}
	
	public function get_action (bool $split = false): mixed
	{
		if ($split)
		{
			$action = explode('/', $this->action);
			return count($action) > 1 ? ['module' => array_shift($action), 'action' => array_shift($action), 'params' => !empty($action) ? implode('/', $action) : null] : ['module' => $action[0], 'action' => null, 'params' => null];
		}
		return $this->action;
	}
	
	public function set_order (float $value = 0): self
	{
		$this->order = $value;
		return $this;
	}
	
	public function set_badge (mixed $value = null): self
	{
		$this->badge = $value;
		return $this;
	}
};