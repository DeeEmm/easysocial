<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerBackground extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');
	}

	/**
	 * Removes badge from the site
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('Invalid background id provided');
		}

		foreach ($ids as $id) {
			$table = ES::table('Background');
			$table->load((int) $id);
			$table->delete();
		}
		
		$message = 'COM_ES_BACKGROUND_DELETED';
		$this->info->set(null, JText::_($message), SOCIAL_MSG_SUCCESS);

		$redirect = 'index.php?option=com_easysocial&view=stream&layout=background';

		return ES::redirect($redirect);
	}

	/**
	 * Toggles the publish state for the badges
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('Invalid background id provided');
		}

		$task = $this->getTask();

		foreach ($ids as $id) {
			$table = ES::table('Background');
			$table->load((int) $id);

			$table->$task();
		}

		$message = 'COM_ES_BACKGROUND_PUBLISHED';

		if ($task == 'unpublish') {
			$message = 'COM_ES_BACKGROUND_UNPUBLISHED';
		}

		$this->info->set(null, JText::_($message), SOCIAL_MSG_SUCCESS);

		$redirect = 'index.php?option=com_easysocial&view=stream&layout=background';

		return ES::redirect($redirect);
	}

	/**
	 * Stores the custom background
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Background');
		$table->load($id);

		$post = $this->input->post->getArray();

		$params = new JRegistry($this->input->get('params', array(), 'array'));

		$table->bind($post);
		$table->params = $params->toString();

		$state = $table->store();

		$message = 'COM_ES_BACKGROUND_UPDATED_SUCCESS';

		if (!$id) {
			$message = 'COM_ES_BACKGROUND_CREATED_SUCCESS';
		}

		$this->info->set(null, JText::_($message), SOCIAL_MSG_SUCCESS);


		$task = $this->getTask();

		$redirect = 'index.php?option=com_easysocial&view=stream&layout=background';
		
		if ($task == 'apply') {
			$redirect = 'index.php?option=com_easysocial&view=stream&layout=backgroundForm&id=' . $table->id;
		}

		return ES::redirect($redirect);
	}
}