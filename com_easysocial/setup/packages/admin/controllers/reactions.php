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

class EasySocialControllerReactions extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('unpublish', 'togglePublish');
		$this->registerTask('publish', 'togglePublish');
	}

	/**
	 * Toggles publishing state
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$cid = $this->input->get('cid', array(), 'array');
		$task = $this->getTask();

		foreach ($cid as $id) {

			$reaction = ES::table('Reaction');
			$reaction->load([
				'id' => (int) $id
			]);

			$reaction->published = $task == 'publish' ? 1 : 0;
			$reaction->store();
		}

		$message = $task == 'publish' ? 'COM_ES_REACTION_PUBLISHED_SUCCESSFULLY' : 'COM_ES_REACTION_UNPUBLISHED_SUCCESSFULLY';

		$this->view->setMessage($message);
		return $this->redirectToView('reactions');
	}
}
