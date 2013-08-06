<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * View class for install.
 *
 * @package    MG/Core
 * @category   View
 * @author     Modular Gaming Team
 * @copyright  (c) 2012-2013 Modular Gaming Team
 * @license    BSD http://modulargaming.com/license
 */
class MG_View_Install_Database extends Abstract_View_Install {

	/**
	 * @var Database_Exception
	 */
	public $database_error;

	/**
	 * @var bool
	 */
	public $is_writable;

	/**
	 * @var array
	 */
	protected $_post;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_post = Request::current()->post();
	}

	/**
	 * @return string
	 */
	public function database_error()
	{
		if ($this->database_error !== NULL)
		{
			return $this->database_error->getMessage();
		}
	}

	/**
	 * @return array
	 */
	public function post()
	{
		return $this->_post;
	}

	/**
	 * Format the post strings.
	 * @return string
	 */
	public function form()
	{
		return array(
			'hostname' => (isset($this->_post['hostname'])) ? $this->_post['hostname'] : 'localhost',
			'database' => (isset($this->_post['database'])) ? $this->_post['database'] : 'modulargaming',
			'username' => (isset($this->_post['username'])) ? $this->_post['username'] : '',
			'password' => (isset($this->_post['password'])) ? $this->_post['password'] : ''
		);
	}

}