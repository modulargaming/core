<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Controller for installing the database and creating configuration files.
 * Extends Kohana_Controller to avoid the auth check caused by MG Controller.
 *
 * @package    MG/Core
 * @category   Controller
 * @author     Modular Gaming Team
 * @copyright  (c) 2012-2013 Modular Gaming Team
 * @license    BSD http://modulargaming.com/license
 */
class MG_Controller_Install extends Kohana_Controller {

	protected $_view;

	/**
	 * The path to the config file.
	 *
	 * @var string
	 */
	protected $_config_path = 'config/database.php';

	/**
	 * Ensure we are calling this controller from the install.php by checking the MG_INSTALL constant.
	 * And throw a HTTP 404 exception if that is not the case.
	 *
	 * @throws HTTP_Exception
	 */
	public function before()
	{
		// Ensure we are in the install.php file.
		if ( ! defined('MG_INSTALL') or MG_INSTALL !== TRUE)
		{
			throw HTTP_Exception::factory(404, 'File not found!');
		}
	}

	/**
	 * Check the database config, and allow the user to generate a database.php config.
	 */
	public function action_database()
	{
		$this->_view = new View_Install_Database;

		$this->_view->is_writable = is_writable($this->_full_config_path());

		$config = Kohana::$config->load('database.default');

		// Overwrite the config object with the post variables.
		if ($this->request->method() == HTTP_Request::POST)
		{
			$config['connection']['hostname'] = $this->request->post('hostname');
			$config['connection']['database'] = $this->request->post('database');
			$config['connection']['username'] = $this->request->post('username');
			$config['connection']['password'] = $this->request->post('password');
		}

		try {
			// Attempt to connect to the database with our new config.
			$db = Database::instance('install', $config);
			$db->connect();

			// The information is correct, so just continue.
			if ($this->request->method() !== HTTP_Request::POST)
			{
				$this->redirect('?p=import');
			}
			else
			{
				$view = new View_Install_Download;
				$view->config = $config['connection'];

				$renderer = Kostache::factory();

				// Write to the file.
				if ($this->_view->is_writable)
				{
					file_put_contents($this->_full_config_path(), $renderer->render($view));
					$this->redirect('?p=import');
				}
				else
				{
					// Fallback to downloading the file.
					$this->response->body($renderer->render($view));

					// Send the headers for downloading.
					$this->response->send_file(TRUE, 'database.php');
				}
			}
		}
		catch (Database_Exception $e)
		{
			$this->_view->database_error = $e;
		}
	}

	public function action_import()
	{
		$this->_view = new View_Install_Import;

		if ($this->request->method() == HTTP_Request::POST)
		{
			Minion_Task::factory(array(
				'task' => 'migrations:run',
				'quiet' => TRUE
			))->execute();

			$this->_view->imported = TRUE;
		}
	}

	public function after()
	{
		$renderer = Kostache_Layout::factory();
		$renderer->set_layout('install/layout');
		$this->response->body($renderer->render($this->_view));
	}

	/**
	 * Get the full path to config file, including APPPATH.
	 *
	 * @return string
	 */
	protected function _full_config_path()
	{
		return APPPATH.$this->_config_path;
	}

}