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

class EasySocialViewExports extends EasySocialSiteView
{
	/**
	 * Displays a list of users on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::checkCompleteProfile();
		ES::setMeta();

		$profileId = $this->input->get('id', 0, 'int');

		$menu = JFactory::getApplication()->getMenu('site');
		$activeMenu = $menu->getActive();

		// we need to ensure this menu item is belong to export view.
		$valid = false;
		if ($activeMenu && isset($activeMenu->query) && isset($activeMenu->query['view']) && $activeMenu->query['view'] == 'exports') {
			$valid = true;
		}

		if (!$valid) {
			return parent::display('site/exports/default/empty');
		}

		$params = $activeMenu->getParams();
		$ids = $params->get('fields', '');

		// dump($ids);

		$pageHeader = $params->get('page_heading', 'COM_ES_PAGE_TITLE_USERS_EXPORT');

		if (!$ids) {
			return parent::display('site/exports/default/empty');
		}

		$fieldIds = explode(',', $ids);

		$limit = ES::getLimit();
		$limitstart = $this->input->get('limitstart', 0, 'int');

		// debug
		// $limit = 2;

		$options = array('limit' => $limit, 'limitstart' => $limitstart);

		$model = ES::model('Users');
		$data = $model->exportTable($profileId, $fieldIds, $options);
		$pagination = $model->getPagination();

		if (!$data) {
			return parent::display('site/exports/default/empty');
		}

		// $limitstart = count($data) < $limit ? 0 : $limit;

		$this->set('data', $data);
		$this->set('pageHeader', $pageHeader);
		$this->set('pagination', $pagination);

		return parent::display('site/exports/default/default');
	}
}
