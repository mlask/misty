<?php
namespace misty;
class db
{
	const TYPE_MAP = [
		'NULL'		=> \PDO::PARAM_NULL,
		'string'	=> \PDO::PARAM_STR,
		'integer'	=> \PDO::PARAM_INT,
		'boolean'	=> \PDO::PARAM_BOOL
	];
	
	private static $instances = null;
	private $config = null;
	private $pdo = null;
	
	public function __construct (array $config)
	{
		$default = [
			'username'	=> '',
			'password'	=> '',
			'scheme'	=> false,
			'socket'	=> false,
			'host'		=> false,
			'port'		=> false,
			'engine'	=> false,
			'charset'	=> 'utf8',
			'timeout'	=> 15
		];
		
		$this->config = array_merge($default, $config);
		$this->_init_pdo();
	}
	
	public static function load (array $config)
	{
		$id = md5(json_encode($config));
		if (!isset(self::$instances[$id]))
			self::$instances[$id] = new static($config);
		return self::$instances[$id];
	}
	
	/* ---------- Funkcje publiczne ---------- */
	
	public function query (...$args)
	{
		return $this->pdo ? $this->pdo->query($this->_build_query(...$args)) : false;
	}
	
	public function query_simple (...$args)
	{
		return $this->pdo ? $this->pdo->exec($this->_build_query(...$args)) : false;
	}
	
	public function get_row (...$args)
	{
		if (!$this->pdo)
			return false;
		
		$query = $this->pdo->query($this->_build_query(...$args));
		$row = $query->fetch(\PDO::FETCH_ASSOC);
		$query = null;
		return $row;
	}
	
	public function get_col (...$args)
	{
		if (!$this->pdo)
			return false;
		
		$query = $this->pdo->query($this->_build_query(...$args));
		$cols = [];
		while (($col = $query->fetchColumn()) !== false) $cols[] = $col;
		$query = null;
		return $cols;
	}
	
	public function get_array (...$args)
	{
		if (!$this->pdo)
			return false;
		
		$query = $this->pdo->query($this->_build_query(...$args));
		$array = $query->fetchAll(\PDO::FETCH_ASSOC);
		$query = null;
		return $array;
	}
	
	public function get_field (...$args)
	{
		if (!$this->pdo)
			return false;
		
		$query = $this->pdo->query($this->_build_query(...$args));
		$row = $query->fetch(\PDO::FETCH_NUM);
		$query = null;
		return is_array($row) ? array_shift($row) : false;
	}
	
	public function preview_query (...$args)
	{
		return $this->_build_query(...$args);
	}
	
	public function transaction_start ($options = null)
	{
		$this->query_simple('START TRANSACTION' . ($options !== null ? ' ' . $options : ''));
	}
	
	public function transaction_commit ()
	{
		$this->query_simple('COMMIT');
	}
	
	public function transaction_rollback ()
	{
		$this->query_simple('ROLLBACK');
	}
	
	public function affected_rows (\PDOStatement $query)
	{
		return $query->rowCount();
	}
	
	/* ---------- Funkcje prywatne ---------- */
	
	private function _init_pdo ()
	{
		try
		{
			switch (strtolower($this->config['engine']))
			{
				case 'mysql':
				{
					$dsn = $this->_pdo_dsn([
						'unix_socket'	=> $this->config['socket'],
						'host'			=> $this->config['host'],
						'port'			=> $this->config['port'],
						'dbname'		=> $this->config['scheme']
					]);
					$this->pdo = new \PDO('mysql:' . $dsn, $this->config['username'], $this->config['password'], [
						\PDO::ATTR_TIMEOUT				=> (int)$this->config['timeout'],
						\PDO::MYSQL_ATTR_INIT_COMMAND	=> sprintf("SET NAMES '%s'", strtoupper($this->config['charset']))
					]);
					$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
					core::log('db connected to mysql');
					break;
				}
				default:
				{
					core::log('db engine not set');
				}
			}
		}
		catch (\PDOException $e)
		{
			core::log('db exception: %s', $e->getMessage());
			throw new exception($e->getMessage());
		}
	}
	
	private function _pdo_dsn (array $dsnc)
	{
		$dsn = null;
		foreach ($dsnc as $_dk => $_dv)
			if ($_dv !== false && $_dv !== null)
				$dsn[] = $_dk . '=' . $_dv;
		return implode(';', $dsn);
	}
	
	private function _flatten (array $array, $index = 0, $prefix = null)
	{
		foreach ($array as $_k => $_v)
		{
			$key = is_numeric($_k) && $index !== null ? $index . '.' . $_k : ($prefix !== null ? $prefix . '.' : '') . $_k;
			if (is_array($_v))
				yield from $this->_flatten($_v, null, $key);
			else
				yield $key => $_v;
		}
	}
	
	private function _quote ($input, $type = null)
	{
		if ($type === null && (gettype($input) === 'integer' || gettype($input) === 'boolean'))
			return (int)$input;
		
		return $this->pdo ? $this->pdo->quote($input, $type === null ? \PDO::PARAM_STR : $type) : sprintf('\'%s\'', addcslashes($input, '\''));
	}
	
	private function _build_query (...$args)
	{
		// if empty
		if (count($args) === 0)
			return false;
		
		// sql query string always as first argument
		$sql = array_shift($args);
		
		// process parameters
		if (!empty($args))
		{
			$params = null;
			foreach ($args as $_ai => $_av)
			{
				if (is_array($_av))
					foreach ($this->_flatten($_av, $_ai + 1) as $_avk => $_avv)
						$params[$_avk] = $_avv;
				$params[$_ai + 1] = $_av;
			}
			core::log('db parameters: %s', json_encode($params));
			$sql = preg_replace_callback('/\[::(.+?)\]/', function ($match) use ($params) { return $this->_replace_tag($match, $params); }, $sql);
		}
		
		// remove empty parameters
		$sql = preg_replace('/\[::(.+?)\]/', 'NULL', $sql);
		
		// process conditionals
		if (strpos($sql, '/*{{') !== false)
			$sql = $this->_conditionals($sql);
		
		core::log('sql: %s', $sql);
		return $sql;
	}
	
	private function _replace_tag ($tag, array $params = null)
	{
		$tag = explode('|', $tag[1]);
		$key = array_shift($tag);
		$noq = false;
		$out = isset($params[$key]) ? (is_array($params[$key]) ? json_encode($params[$key]) : $params[$key]) : null;
		
		if (!empty($tag))
		{
			foreach ($tag as $_t)
			{
				$_a = explode(':', $_t);
				$_t = array_shift($_a);
				switch (strtolower($_t))
				{
					case 'not_null':
					{
						$out = $out !== null && strlen($out) > 0 ? $out : '';
						break;
					}
					case 'default':
					{
						$out = ($out !== null && strlen($out) > 0) ? $out : (!empty($_a) ? array_shift($_a) : null);
						break;
					}
					case 'noesc':
					{
						$noq = true;
						break;
					}
					case 'join':
					{
						if (is_array($params[$key]))
						{
							$out = $params[$key];
							array_walk($out, function (& $item) { $item = !is_array($item) ? $this->_quote($item, \PDO::PARAM_STR) : null; });
							if (!empty(array_filter($out)))
							{
								$out = implode(',', array_filter($out));
								$noq = true;
							}
							else
								$out = null;
						}
						break;
					}
					case 'like':
					{
						if ($out !== null && strlen($out) > 0)
						{
							$out = preg_replace('/^\'(.*)\'$/', '$1', $this->_quote(strtr($out, ['%' => '\%', '_' => '\_']), \PDO::PARAM_STR));
							$noq = true;
						}
						break;
					}
					case 'null':
					{
						$out = $out !== null && strlen($out) === 0 ? null : $out;
						break;
					}
					case 'hex':
					{
						$out = 'X' . $this->_quote(bin2hex($out));
						$noq = true;
					}
					default:
					{
						if (function_exists($_t))
							$out = call_user_func($_t, $out);
					}
				}
			}
		}
		
		if ($out !== null && $noq === false)
			$out = $this->_quote($out);
		
		return $out === null ? 'NULL' : $out;
	}
	
	private function _conditionals ($input)
	{
		// prosty parser instrukcji warunkowych, obsługujący poprawnie zagnieżdżone warunki
		$tags = $repl = [];
		for ($i = 0, $l = 0, $t = null; $i < strlen($input); $i ++)
		{
			if (substr($input, $i, 4) === '/*{{')	{ $t[++ $l] = [$i, $l, null]; }
			if (substr($input, $i, 6) === '/*}}*/')	{ $t[$l][2] = substr($input, $t[$l][0], $i - $t[$l][0] + 6); $tags[] = $t[$l --]; }
		}
		usort($tags, function ($a, $b) { return $a[1] > $b[1] ? -1 : 1; });
		foreach ($tags as $tag)
			$repl[$tag[1]][$tag[2]] = preg_replace_callback('/^\/\*\{\{(.+?)\{\{\*\/(.+?)(\/\*\}\{\*\/(.+?)|)\/\*\}\}\*\/$/s', [$this, '_conditionals_replace'], isset($repl[$tag[1] + 1]) ? strtr($tag[2], $repl[$tag[1] + 1]) : $tag[2]);
		return isset($repl[1]) ? strtr($input, $repl[1]) : $input;
	}

	private function _conditionals_replace (array $input)
	{
		core::log('db conditional: %s', $input[1]);
		if (eval('return (' . $input[1] . ');'))
			return $input[2];
		return isset($input[4]) ? $input[4] : null;
	}
};