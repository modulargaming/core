<?php defined('SYSPATH') OR die('No direct script access.');

class MG_Abstract_View_Install {

	/**
	 * @return string
	 */
	public function bootstrap()
	{
		$file = Kohana::find_file('assets', 'css/bootstrap.min', 'css');

		if ($file !== NULL)
		{
			return file_get_contents($file);
		}
	}

}