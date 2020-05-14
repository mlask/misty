<?php
namespace misty;
class format
{
	public static function num ($num, array $text, $wn = false)
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
}