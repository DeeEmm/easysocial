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

class EasySocialViewBadges extends EasySocialAdminView
{
	/**
	 * Main method to display the badges view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_BADGES');

		JToolbarHelper::addNew();
		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::divider();
		JToolbarHelper::deleteList();

		// Default filters
		$options = array('initState' => true, 'namespace' => 'badges.listing');
		$model = ES::model('Badges', $options);
		$search = $model->getState('search');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'word');
		$direction = $this->input->get('direction', $model->getState('direction'), 'word');
		$extension = $this->input->get('extension', $model->getState('extension'), 'word');
		$state = $this->input->get('state', $model->getState('state'), 'default');
		$limit = $model->getState('limit');

		$badges = $model->getItemsWithState();
		$extensions = $model->getExtensions();

		$lang = ES::language();

		foreach ($extensions as $e) {
			$lang->load($e, JPATH_ADMINISTRATOR);
		}

		// Load front end's language file
		ES::language()->loadSite();

		// Get pagination
		$pagination = $model->getPagination();

		$this->set('limit', $limit );
		$this->set('extension', $extension );
		$this->set('search', $search );
		$this->set('ordering', $ordering );
		$this->set('direction', $direction );
		$this->set('state', $state );
		$this->set('badges', $badges );
		$this->set('pagination', $pagination );
		$this->set('extensions', $extensions );

		parent::display('admin/badges/default/default');
	}

	/**
	 * Displays the CSV upload form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function csv($tpl = null)
	{
		// Add heading here.
		$this->setHeading('COM_EASYSOCIAL_HEADING_UPLOAD_CSV_BADGES', 'COM_EASYSOCIAL_DESCRIPTION_UPLOAD_CSV_BADGES');

		JToolbarHelper::custom('massAssign', '', '', JText::_('Upload File'), false);
		
		parent::display('admin/badges/csv/default');
	}

	/**
	 * Main method to display the form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form($tpl = null)
	{
		// Get the id from the request.
		$id = $this->input->get('id', 0, 'int');

		// Get the table object
		$badge = ES::table('Badge');
		$state = $badge->load($id);

		if ($state && !empty($badge->extension) && $badge->extension != SOCIAL_COMPONENT_NAME) {
			ES::language()->load($badge->extension, JPATH_ROOT);
			ES::language()->load($badge->extension, JPATH_ADMINISTRATOR);
		}

		ES::language()->loadSite();

		// Add heading here.
		$this->setHeading('COM_ES_CREATE_NEW_BADGE');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		if ($id) {
			$this->setHeading($badge->get('title'), 'COM_EASYSOCIAL_DESCRIPTION_EDIT_BADGE');
		}

		// Default value for new badge
		if (!$badge->id) {
			$badge->extension = 'com_easysocial';
			$badge->command = 'custom.badge';
			$badge->achieve_type = 'frequency';
			$badge->frequency = 10;
			$badge->created = JFactory::getDate()->toSql();
			$badge->state = true;
		}
		

		// Get points selection
		$pointsModel = ES::model('Points');
		$points = $pointsModel->getItems();

		$pointsIncreaseSelection = array();
		$pointsDecreaseSelection = array();

		$pointsDecreaseSelection[] = array('value' => 'null', 'text' => 'COM_EASYSOCIAL_BADGES_POINTS_DECREASE_RULE_SELECTION_NONE');

		foreach ($points as $point) {
			$obj = new stdClass();
			$obj->value = $point->id;
			$obj->text = $point->command . ' (' . JText::sprintf('COM_EASYSOCIAL_BADGES_POINTS_VALUE', $point->points) . ')';

			$pointsIncreaseSelection[] = $obj;
			$pointsDecreaseSelection[] = $obj;
		}

		// Push the badge to the theme.
		$this->set('badge', $badge);
		$this->set('pointsIncreaseSelection', $pointsIncreaseSelection);
		$this->set('pointsDecreaseSelection', $pointsDecreaseSelection);

		parent::display('admin/badges/form/default');
	}


	/**
	 * Displays the installation layout for points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install( $tpl = null )
	{
		// Add heading here.
		$this->setHeading('COM_EASYSOCIAL_HEADING_INSTALL_BADGES', 'COM_EASYSOCIAL_DESCRIPTION_INSTALL_BADGES');

		JToolbarHelper::custom('upload', 'upload', '', JText::_('COM_EASYSOCIAL_UPLOAD_AND_INSTALL'), false);

		parent::display('admin/badges/install/default');
	}

	/**
	 * Displays the discover layout for points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_DISCOVER_BADGES', 'COM_EASYSOCIAL_DESCRIPTION_DISCOVER_BADGES');

		JToolbarHelper::custom('discover', 'download', '', JText::_('COM_EASYSOCIAL_DISCOVER_BUTTON'), false);
		
		parent::display('admin/badges/discover/default');
	}

	/**
	 * Post processing after uploading a badge file
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function upload()
	{
		return $this->redirect('index.php?option=com_easysocial&view=badges&layout=install');
	}

	/**
	 * Post process after a badge is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove($task = null, $badge = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=badges');
	}

	/**
	 * Post process after badges has been published / unpublished
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function togglePublish($task = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=badges');
	}

	/**
	 * Post process after a badge is stored
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($task = null, $badge = null)
	{
		$url = 'index.php?option=com_easysocial&view=badges';
		
		if ($task == 'apply') {
			return $this->redirect($url . '&layout=form&id=' . $badge->id);
		}

		return $this->redirect($url);
	}

	/**
	 * Post process after the mass assignment is completed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function massAssign($success = array(), $failed = array())
	{
		return $this->redirect('index.php?option=com_easysocial&view=badges&layout=csv');
	}
}
