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

class EasySocialControllerHoneypot extends EasySocialController
{
	/**
	 * Deletes a list of provided points
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('Invalid Id provided');
		}

		foreach ($ids as $id) {
			$table = ES::table('Honeypot');
			$table->load((int) $id);

			$table->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_HONEYPOT_DELETE', 'alerts', [
				'link' => 'index.php?option=com_easysocial&view=languages'
			]);
		}

		$this->view->setMessage('COM_ES_HONEYPOT_LOG_DELETED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Publishes a point
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function purge()
	{
		ES::checkToken();

		$model = ES::model('Honeypot');
		$model->purge();

		$this->view->setMessage('COM_ES_HONEYPOT_LOGS_PURGED');

		$this->actionlog->log('COM_ES_ACTION_LOG_HONEYPOT_PURGE', 'alerts', [
			'link' => 'index.php?option=com_easysocial&view=languages'
		]);

		return $this->view->call(__FUNCTION__);
	}
}
