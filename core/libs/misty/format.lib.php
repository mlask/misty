<?php
namespace misty;
class format
{
	const ISO_3166_1 = [
		'AD'	=> 'Andorra',
		'AE'	=> 'United Arab Emirates',
		'AF'	=> 'Afghanistan',
		'AG'	=> 'Antigua and Barbuda',
		'AI'	=> 'Anguilla',
		'AL'	=> 'Albania',
		'AM'	=> 'Armenia',
		'AO'	=> 'Angola',
		'AQ'	=> 'Antarctica',
		'AR'	=> 'Argentina',
		'AS'	=> 'American Samoa',
		'AT'	=> 'Austria',
		'AU'	=> 'Australia',
		'AW'	=> 'Aruba',
		'AX'	=> 'Åland Islands',
		'AZ'	=> 'Azerbaijan',
		'BA'	=> 'Bosnia and Herzegovina',
		'BB'	=> 'Barbados',
		'BD'	=> 'Bangladesh',
		'BE'	=> 'Belgium',
		'BF'	=> 'Burkina Faso',
		'BG'	=> 'Bulgaria',
		'BH'	=> 'Bahrain',
		'BI'	=> 'Burundi',
		'BJ'	=> 'Benin',
		'BL'	=> 'Saint Barthélemy',
		'BM'	=> 'Bermuda',
		'BN'	=> 'Brunei Darussalam',
		'BO'	=> 'Bolivia, Plurinational State of',
		'BQ'	=> 'Bonaire, Sint Eustatius and Saba',
		'BR'	=> 'Brazil',
		'BS'	=> 'Bahamas',
		'BT'	=> 'Bhutan',
		'BV'	=> 'Bouvet Island',
		'BW'	=> 'Botswana',
		'BY'	=> 'Belarus',
		'BZ'	=> 'Belize',
		'CA'	=> 'Canada',
		'CC'	=> 'Cocos (Keeling) Islands',
		'CD'	=> 'Congo, the Democratic Republic of the',
		'CF'	=> 'Central African Republic',
		'CG'	=> 'Congo',
		'CH'	=> 'Switzerland',
		'CI'	=> 'Côte d\'Ivoire',
		'CK'	=> 'Cook Islands',
		'CL'	=> 'Chile',
		'CM'	=> 'Cameroon',
		'CN'	=> 'China',
		'CO'	=> 'Colombia',
		'CR'	=> 'Costa Rica',
		'CU'	=> 'Cuba',
		'CV'	=> 'Cape Verde',
		'CW'	=> 'Curaçao',
		'CX'	=> 'Christmas Island',
		'CY'	=> 'Cyprus',
		'CZ'	=> 'Czechia',
		'DE'	=> 'Germany',
		'DJ'	=> 'Djibouti',
		'DK'	=> 'Denmark',
		'DM'	=> 'Dominica',
		'DO'	=> 'Dominican Republic',
		'DZ'	=> 'Algeria',
		'EC'	=> 'Ecuador',
		'EE'	=> 'Estonia',
		'EG'	=> 'Egypt',
		'EH'	=> 'Western Sahara',
		'ER'	=> 'Eritrea',
		'ES'	=> 'Spain',
		'ET'	=> 'Ethiopia',
		'FI'	=> 'Finland',
		'FJ'	=> 'Fiji',
		'FK'	=> 'Falkland Islands (Malvinas)',
		'FM'	=> 'Micronesia, Federated States of',
		'FO'	=> 'Faroe Islands',
		'FR'	=> 'France',
		'GA'	=> 'Gabon',
		'GB'	=> 'United Kingdom',
		'GD'	=> 'Grenada',
		'GE'	=> 'Georgia',
		'GF'	=> 'French Guiana',
		'GG'	=> 'Guernsey',
		'GH'	=> 'Ghana',
		'GI'	=> 'Gibraltar',
		'GL'	=> 'Greenland',
		'GM'	=> 'Gambia',
		'GN'	=> 'Guinea',
		'GP'	=> 'Guadeloupe',
		'GQ'	=> 'Equatorial Guinea',
		'GR'	=> 'Greece',
		'GS'	=> 'South Georgia and the South Sandwich Islands',
		'GT'	=> 'Guatemala',
		'GU'	=> 'Guam',
		'GW'	=> 'Guinea-Bissau',
		'GY'	=> 'Guyana',
		'HK'	=> 'Hong Kong',
		'HM'	=> 'Heard Island and McDonald Islands',
		'HN'	=> 'Honduras',
		'HR'	=> 'Croatia',
		'HT'	=> 'Haiti',
		'HU'	=> 'Hungary',
		'ID'	=> 'Indonesia',
		'IE'	=> 'Ireland',
		'IL'	=> 'Israel',
		'IM'	=> 'Isle of Man',
		'IN'	=> 'India',
		'IO'	=> 'British Indian Ocean Territory',
		'IQ'	=> 'Iraq',
		'IR'	=> 'Iran, Islamic Republic of',
		'IS'	=> 'Iceland',
		'IT'	=> 'Italy',
		'JE'	=> 'Jersey',
		'JM'	=> 'Jamaica',
		'JO'	=> 'Jordan',
		'JP'	=> 'Japan',
		'KE'	=> 'Kenya',
		'KG'	=> 'Kyrgyzstan',
		'KH'	=> 'Cambodia',
		'KI'	=> 'Kiribati',
		'KM'	=> 'Comoros',
		'KN'	=> 'Saint Kitts and Nevis',
		'KP'	=> 'Korea, Democratic People\'s Republic of',
		'KR'	=> 'Korea, Republic of',
		'KW'	=> 'Kuwait',
		'KY'	=> 'Cayman Islands',
		'KZ'	=> 'Kazakhstan',
		'LA'	=> 'Lao People\'s Democratic Republic',
		'LB'	=> 'Lebanon',
		'LC'	=> 'Saint Lucia',
		'LI'	=> 'Liechtenstein',
		'LK'	=> 'Sri Lanka',
		'LR'	=> 'Liberia',
		'LS'	=> 'Lesotho',
		'LT'	=> 'Lithuania',
		'LU'	=> 'Luxembourg',
		'LV'	=> 'Latvia',
		'LY'	=> 'Libyan Arab Jamahiriya',
		'MA'	=> 'Morocco',
		'MC'	=> 'Monaco',
		'MD'	=> 'Moldova, Republic of',
		'ME'	=> 'Montenegro',
		'MF'	=> 'Saint Martin (French part)',
		'MG'	=> 'Madagascar',
		'MH'	=> 'Marshall Islands',
		'MK'	=> 'Macedonia, the former Yugoslav Republic of',
		'ML'	=> 'Mali',
		'MM'	=> 'Myanmar',
		'MN'	=> 'Mongolia',
		'MO'	=> 'Macao',
		'MP'	=> 'Northern Mariana Islands',
		'MQ'	=> 'Martinique',
		'MR'	=> 'Mauritania',
		'MS'	=> 'Montserrat',
		'MT'	=> 'Malta',
		'MU'	=> 'Mauritius',
		'MV'	=> 'Maldives',
		'MW'	=> 'Malawi',
		'MX'	=> 'Mexico',
		'MY'	=> 'Malaysia',
		'MZ'	=> 'Mozambique',
		'NA'	=> 'Namibia',
		'NC'	=> 'New Caledonia',
		'NE'	=> 'Niger',
		'NF'	=> 'Norfolk Island',
		'NG'	=> 'Nigeria',
		'NI'	=> 'Nicaragua',
		'NL'	=> 'Netherlands',
		'NO'	=> 'Norway',
		'NP'	=> 'Nepal',
		'NR'	=> 'Nauru',
		'NU'	=> 'Niue',
		'NZ'	=> 'New Zealand',
		'OM'	=> 'Oman',
		'PA'	=> 'Panama',
		'PE'	=> 'Peru',
		'PF'	=> 'French Polynesia',
		'PG'	=> 'Papua New Guinea',
		'PH'	=> 'Philippines',
		'PK'	=> 'Pakistan',
		'PL'	=> 'Poland',
		'PM'	=> 'Saint Pierre and Miquelon',
		'PN'	=> 'Pitcairn',
		'PR'	=> 'Puerto Rico',
		'PS'	=> 'Palestinian Territory, Occupied',
		'PT'	=> 'Portugal',
		'PW'	=> 'Palau',
		'PY'	=> 'Paraguay',
		'QA'	=> 'Qatar',
		'RE'	=> 'Réunion',
		'RO'	=> 'Romania',
		'RS'	=> 'Serbia',
		'RU'	=> 'Russian Federation',
		'RW'	=> 'Rwanda',
		'SA'	=> 'Saudi Arabia',
		'SB'	=> 'Solomon Islands',
		'SC'	=> 'Seychelles',
		'SD'	=> 'Sudan',
		'SE'	=> 'Sweden',
		'SG'	=> 'Singapore',
		'SH'	=> 'Saint Helena, Ascension and Tristan da Cunha',
		'SI'	=> 'Slovenia',
		'SJ'	=> 'Svalbard and Jan Mayen',
		'SK'	=> 'Slovakia',
		'SL'	=> 'Sierra Leone',
		'SM'	=> 'San Marino',
		'SN'	=> 'Senegal',
		'SO'	=> 'Somalia',
		'SR'	=> 'Suriname',
		'SS'	=> 'South Sudan',
		'ST'	=> 'Sao Tome and Principe',
		'SV'	=> 'El Salvador',
		'SX'	=> 'Sint Maarten (Dutch part)',
		'SY'	=> 'Syrian Arab Republic',
		'SZ'	=> 'Swaziland',
		'TC'	=> 'Turks and Caicos Islands',
		'TD'	=> 'Chad',
		'TF'	=> 'French Southern Territories',
		'TG'	=> 'Togo',
		'TH'	=> 'Thailand',
		'TJ'	=> 'Tajikistan',
		'TK'	=> 'Tokelau',
		'TL'	=> 'Timor-Leste',
		'TM'	=> 'Turkmenistan',
		'TN'	=> 'Tunisia',
		'TO'	=> 'Tonga',
		'TR'	=> 'Turkey',
		'TT'	=> 'Trinidad and Tobago',
		'TV'	=> 'Tuvalu',
		'TW'	=> 'Taiwan, Province of China',
		'TZ'	=> 'Tanzania, United Republic of',
		'UA'	=> 'Ukraine',
		'UG'	=> 'Uganda',
		'UM'	=> 'United States Minor Outlying Islands',
		'US'	=> 'United States',
		'UY'	=> 'Uruguay',
		'UZ'	=> 'Uzbekistan',
		'VA'	=> 'Holy See (Vatican City State)',
		'VC'	=> 'Saint Vincent and the Grenadines',
		'VE'	=> 'Venezuela, Bolivarian Republic of',
		'VG'	=> 'Virgin Islands, British',
		'VI'	=> 'Virgin Islands, U.S.',
		'VN'	=> 'Viet Nam',
		'VU'	=> 'Vanuatu',
		'WF'	=> 'Wallis and Futuna',
		'WS'	=> 'Samoa',
		'YE'	=> 'Yemen',
		'YT'	=> 'Mayotte',
		'ZA'	=> 'South Africa',
		'ZM'	=> 'Zambia',
		'ZW'	=> 'Zimbabwe'
	];
	const LATIN_ACCENTS = [
		'ą' => 'a˛',
		'ć' => 'c´',
		'ę' => 'e˛',
		'ł' => 'l~',
		'ń' => 'n´',
		'ó' => 'o´',
		'ś' => 's´',
		'ź' => 'z´',
		'ż' => 'z˙',
		'Ą' => 'A˛',
		'Ć' => 'C´',
		'Ę' => 'E˛',
		'Ł' => 'L~',
		'Ń' => 'N´',
		'Ó' => 'O´',
		'Ś' => 'S´',
		'Ź' => 'Z´',
		'Ż' => 'Z˙'
	];
	
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
	
	public static function explode_string_values ($input = '', $item_separator = ';', $value_separator = '=', array $array_map = null, $array_map_separator = '|')
	{
		$output = null;
		if (strlen($input) > 0)
			foreach (explode($item_separator, $input) as $input_item)
				if (count($input_item = explode($value_separator, $input_item, 2)) === 2)
					$output[$input_item[0]] = $array_map !== null ? array_combine($array_map, explode($array_map_separator, $input_item[1])) : $input_item[1];
		return $output;
	}
	
	public static function country_code (string $code): string
	{
		return isset(self::ISO_3166_1[$code]) ? i18n::load()->_(self::ISO_3166_1[$code] . ' ~~' . $code) : $code;
	}
	
	public static function country_codes (): array
	{
		$output = [];
		foreach (self::ISO_3166_1 as $code => $name)
			$output[$code] = i18n::load()->_($name . ' ~~' . $code);
		uasort($output, function ($a, $b) {
			return self::strnatcasecmp($a, $b);
		});
		return $output;
	}
	
	private static function strnatcasecmp (string $str1, string $str2): int
	{
		return strnatcasecmp(strtr($str1, self::LATIN_ACCENTS), strtr($str2, self::LATIN_ACCENTS));
	}
};