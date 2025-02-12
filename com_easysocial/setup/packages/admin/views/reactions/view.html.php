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

class EasySocialViewReactions extends EasySocialAdminView
{
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_REACTIONS');
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');

		$model = ES::model('Likes', ['initState' => true, 'namespace' => 'reactions.listing']);
		$result = $model->getReactionsWithState();

		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$state = $model->getState('state');
		$pagination = $model->getPagination();
		$reactions = [];

		if ($result) {
			foreach ($result as &$row) {
				$reaction = ES::reaction($row);

				$reactions[] = $reaction;
			}
		}

		$this->set('pagination', $pagination);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('reactions', $reactions);

		parent::display('admin/reactions/default/default');
	}
}
