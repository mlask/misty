<?php
namespace misty;
new class
{
	private $view = null;
	
	public function __construct ()
	{
		$mod = null;
		
		// no output buffering AT ALL!
		while (ob_get_level())
			ob_end_clean();
		
		// "do, or do not, there's no..."
		try
		{
			core::run('main', [
				'request'	=> request::load()
			]);
		}
		catch (\Exception $e)
		{
			// exception message
			printf("⚠️  \x1b[41;97;1m EXCEPTION \x1b[0;1m %s\x1b[0m in \x1b[1m%s\x1b[0m (line %d)\n",
				$e->getMessage(),
				$e->getFile(),
				$e->getLine());
		}
		catch (\Throwable $t)
		{
			// throwable to view
			printf("⚠️  \x1b[41;97;1m THROWABLE \x1b[0;1m %s\x1b[0m in \x1b[1m%s\x1b[0m (line %d)\n",
				$t->getMessage(),
				$t->getFile(),
				$t->getLine());
		}
		
		// update i18n data
		i18n::reload();
		
		// debug log
		if ((int)request::load()->param('debug', 0) === 1)
		{
			printf("📃 \x1b[45;1m DEBUG LOG \x1b[0m\n");
			foreach (core::log() as $log)
				printf("🔹 \x1b[44;94m  %1\$s  \x1b[0;1m  %4\$s\x1b[0m\n   \x1b[44;94;4m %2\$s.%3\$03d \x1b[0;90;3m  %5\$s (line %6\$d)\x1b[0m\n",
					date('Y-m-d', (int)$log['time']),
					date('H:i:s', (int)$log['time']),
					($log['time'] - (int)$log['time']) * 1000,
					$log['message'],
					$log['source']['file'],
					$log['source']['line']);
		}
	}
};