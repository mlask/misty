<?php
namespace misty;
class version
{
	public static function get (): ?string
	{
		return self::_git_rev()['hash_short'] ?? null;
	}
	
	private static function _git_rev (): ?array
	{
		$git_dir = dirname(dirname(dirname(__DIR__))) . '/.git';
		
		if (file_exists($git_dir . '/HEAD'))
		{
			$ref = substr(trim(file_get_contents($git_dir . '/HEAD')), 5);
			$hash = trim(file_get_contents($git_dir . '/' . $ref));
			
			foreach (glob($git_dir . '/refs/tags/v*') as $tag_id)
				if (trim(file_get_contents($tag_id)) === $hash)
					$tag = basename($tag_id);
			
			return [
				'ref' => $ref,
				'tag' => $tag ?? null,
				'date' => filemtime($git_dir . '/index'),
				'hash' => $hash,
				'hash_short' => substr($hash, 0, 7)
			];
		}
		
		return null;
	}
};