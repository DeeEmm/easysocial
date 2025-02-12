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

class EasySocialViewComments extends EasySocialAdminView
{
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_HEADING_COMMENTS');

		JToolbarHelper::deleteList();

		// Default filters
		$options = array('initState' => true, 'namespace' => 'comments.listing');
		$model = ES::model('Comments', $options);
		$search = $model->getState('search');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'word');
		$direction = $this->input->get('direction', $model->getState('direction'), 'word');
		$limit = $model->getState('limit');

		$comments = $model->getItemsWithState();
		$pagination = $model->getPagination();

		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('comments', $comments);
		$this->set('pagination', $pagination);

		parent::display('admin/comments/default/default');
	}

	/**
	 * Post process after a comment is deleted
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function remove($task = null, $badge = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=comments');
	}
}
