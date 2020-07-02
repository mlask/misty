<?php
namespace misty;
class format
{
	public static function page (int $page_number = 1, int $total_items = 1)
	{
		$page_items = 25;
		$pages_margin = 2;
		$pages_around = 2;
		
		$total_items = max(1, $total_items);
		$total_pages = ceil($total_items / $page_items);
		$page_number = min($total_pages, max(1, $page_number));
		
		$page_out = [];
		$page_tmp = [];
		
		foreach ([range(1, $pages_margin), range(max(1, $page_number - $pages_around), min($page_number + $pages_around, $total_pages)), range($total_pages - $pages_margin + 1, $total_pages)] as $array)
			$page_tmp += array_combine($array, $array);
		foreach ($page_tmp as $key => $value)
		{
			if ($key > 1 && !isset($page_tmp[$key - 1]))
			{
				$page_out[] = [
					'page' => null,
					'separator' => true
				];
			}
			
			$page_out[] = [
				'page' => $value,
				'active' => (int)$value === (int)$page_number
			];
		}
		
		$page_tmp = null;
		unset($page_tmp);
		
		if ($total_pages > 1)
		{
			return new obj([
				'current'	=> $page_number,
				'pages'		=> $page_out,
				'prev'		=> $page_number > 1 ? $page_number - 1 : false,
				'next'		=> $page_number < $total_pages ? $page_number + 1 : false,
				'db'		=> [
					'db_limit'	=> $page_items,
					'db_offset'	=> (int)(($page_number - 1) * $page_items)
				]
			]);
		}
		return null;
	}
	
	public static function numeral (int $num, array $text, $wn = false)
	{
		$out = $wn ? [] : [$num];
		if ((int)$num === 1 && isset($text[1]))
			$out[] = $text[1];
		elseif (((int)$num === 0 || (($t0 = (int)$num % 10) >= 0 && $t0 <= 1) || ($t0 >= 5 && $t0 <= 9) || (($t1 = (int)$num % 100) > 10 && $t1 < 20)) && isset($text[0]))
			$out[] = $text[0];
		elseif ((($t1 < 10 || $t1 > 20) && $t0 >= 2 && $t0 <= 4) && isset($text[2]))
			$out[] = $text[2];
		return implode(' ', $out);
	}
	
	public static function filter_recursive (array $input, $callback = null, int $flag = 0)
	{
		foreach ($input as & $value)
			if (is_array($value))
				$value = self::filter_recursive($value, $callback, $flag);
		return $callback !== null ? array_filter($input, $callback, $flag) : array_filter($input);
	}
	
	public static function explode_string_values ($input = '', $item_separator = ';', $value_separator = '=')
	{
		$output = null;
		if (strlen($input) > 0)
			foreach (explode($item_separator, $input) as $input_item)
				if (count($input_item = explode($value_separator, $input_item, 2)) === 2)
					$output[$input_item[0]] = $input_item[1];
		return $output;
	}
};