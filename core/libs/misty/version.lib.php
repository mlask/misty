<?php
namespace misty;
enum version
{
	public static function date (): int
	{
		var_dump(self::_git_rev());die;
		
		return (int)self::_git_rev('date');
	}
	
	public static function number (): float
	{
		return (float)self::_git_rev('tag');
	}
	
	public static function revision (): string
	{
		return self::_git_rev('revision');
	}
	
	private static function _git_rev (): ?array
	{
		$git_dir = dirname(dirname(dirname(__DIR__))) . '/.git';
		
		if (file_exists($git_dir . '/HEAD'))
		{
			return [
				'ref' => $ref = substr(trim(file_get_contents($git_dir . '/HEAD')), 5),
				'date' => filemtime($git_dir . '/HEAD'),
				'hash' => $hash = trim(file_get_contents($git_dir . '/' . $ref)),
				'hash_short' => substr($hash, 0, 7)
			];
			return sprintf('%s@%s', str_replace('refs/heads/', '', $ref), substr(trim(file_get_contents(_ROOT . '/.git/' . $ref)), 0, 7));
		}
		else
			die('no file: ' . $git_dir);
		return null;
	}
};