<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/controller');

class EasySocialController extends EasySocialControllerMain
{
	public $doc = null;
	public $my = null;
	public $config = null;
	public $jconfig = null;
	public $actionlog = null;
	
	/**
	 * Checks if the current viewer can really access this section
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function checkAccess($rule)
	{
		if (!$this->my->authorise($rule , 'com_easysocial')) {
			ES::setMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return ES::redirect('index.php?option=com_easysocial');
		}
	}

	/**
	 * Processes all view requests
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$type = $this->doc->getType();
		$name = $this->input->get('view', $this->getName(), 'cmd');
		$layout = $this->input->get('layout', 'default', 'cmd');

		$view = $this->getView($name, $type, '');
		$view->setLayout($layout);

		if ($layout != 'default') {
			if (!method_exists($view, $layout)) {
				$view->display();
			} else {
				call_user_func_array(array($view, $layout), array());
			}

			return;
		}

		$view->display();

		return;
	}

	/**
	 * Allows a caller to check if a task exist since we're able to access $taskMap from this derived class.
	 *
	 * @since	1.0
	 * @param	string	The name of the task.
	 */
	public function taskAliasExist($task)
	{
		$keys = array_keys($this->taskMap);

		return in_array($task, $keys);
	}
}
