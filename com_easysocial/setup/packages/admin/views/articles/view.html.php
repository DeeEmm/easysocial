<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewArticles extends EasySocialAdminView
{
	/**
	 * This view is mainly to render article selection for custom fields
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function display($tpl = null)
	{
		// Get the model
		$model = ES::model('Articles', array('initState' => true));

		// Do not allow access if it doesn't contain tmpl=component
		$tmpl = $this->input->get('tmpl', '', 'word');

		if ($tmpl != 'component') {
			return $this->redirect('index.php?option=com_easysocial&view=articles&tmpl=component');
		}
		
		// Remember the states
		$search = $model->getState('search');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');

		$articles = $model->getItems(array('search' => $search));
		$pagination = $model->getPagination();

		$jscallback = $this->input->get('jscallback', '');

		$this->set('jscallback', $jscallback);
		$this->set('search', $search);
		$this->set('simple', $this->input->getString('tmpl') == 'component');
		$this->set('articles', $articles);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('pagination', $pagination);

		return parent::display('admin/articles/default');
	}
}
