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

trait EasySocialViewVerificationsTrait
{
	/**
	 * Retrieves list of verification requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function verifications()
	{
		$type = $this->getViewType();
		$heading = 'COM_ES_' . strtoupper($type) . '_VERIFICATION_REQUESTS';

		$this->setHeading($heading);

		JToolbarHelper::custom('approve', 'publish', 'social-publish-hover', JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'), true);
		JToolbarHelper::custom('reject', 'unpublish', 'social-unpublish-hover', JText::_('COM_EASYSOCIAL_REJECT_BUTTON'), true);

		$model = ES::model('Verifications', ['initState' => true]);
		$result = $model->getVerificationList($this->verificationType);
		$items = [];

		if ($result) {
			foreach ($result as $item) {
				$obj = ES::cluster($item->type, $item->uid);

				$obj->request = $item;
				$items[] = $obj;
			}
		}

		$limit = $model->getState('limit');
		$search = $model->getState('search');
		$pagination = $model->getPagination();

		$this->set('type', $this->getViewType());
		$this->set('pagination', $pagination);
		$this->set('items', $items);
		$this->set('limit', $limit);
		$this->set('search', $search);

		parent::display('admin/verifications/default/default');
	}
}
