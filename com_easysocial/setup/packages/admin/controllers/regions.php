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

class EasySocialControllerRegions extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('savenew', 'store');
	}

	public function init()
	{
		return ES::redirect(FRoute::url(array('view' => 'regions', 'layout' => 'init')));
	}

	public function form()
	{
		return ES::redirect(FRoute::url(array('view' => 'regions', 'layout' => 'form')));
	}

	public function initialise()
	{
		$key = $this->input->get('key', '', 'string');

		if ($key === 'clear') {
			ES::model('Regions')->clearDB();
			$this->actionlog->log('COM_ES_ACTION_LOG_REGION_CLEAR', 'region');
		} else {
			ES::model('Regions')->initDB($key);
			$this->actionlog->log('COM_ES_ACTION_LOG_REGION_INIT_' . strtoupper($key), 'region');
		}

		return $this->view->call(__FUNCTION__);
	}

	public function delete()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', '', 'var');

		foreach ($ids as $id) {
			$region = ES::table('Region');
			$region->load($id);

			$state = $region->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_REGION_DELETED', 'region', ['name' => $region->name, 'type' => $region->type]);
			}
		}

		$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGIONS_DELETED_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	public function togglePublish()
	{
		ES::checkToken();

		$action = $this->getTask();

		$ids = $this->input->get('cid', [], 'default');
		$ids = ES::makeArray($ids);

		foreach ($ids as $id) {
			$table = ES::table('Region');
			$table->load($id);
			$table->$action();

			$this->actionlog->log('COM_ES_ACTION_LOG_REGION_' . strtoupper($action), 'region', [
					'name' => $table->name,
					'type' => $table->type,
					'link' => 'index.php?option=com_easysocial&view=regions&layout=form&id=' . $table->id
				]);
		}

		$message = JText::_($action === 'publish' ? 'COM_EASYSOCIAL_REGIONS_PUBLISHED_SUCCESS' : 'COM_EASYSOCIAL_REGIONS_UNPUBLISHED_SUCCESS');

		$this->view->setMessage($message, SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}

	public function getParents()
	{
		ES::checkToken();

		$type = $this->input->get('type', '', 'string');

		$parents = ES::model('Regions')->getRegions(array('type' => $type, 'state' => SOCIAL_STATE_PUBLISHED));

		return $this->view->call(__FUNCTION__, $parents);
	}

	public function store()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$type = $this->input->get('type', '', 'string');
		$parent_uid = $this->input->get('parent_uid', 0, 'int');
		$parent_type = $this->input->get('parent_type', '', 'string');
		$name = $this->input->get('name', '', 'string');
		$code = $this->input->get('code', '', 'string');
		$state = $this->input->get('state', 0, 'int');

		$region = ES::table('Region');
		$region->load($id);

		$region->type = $type;
		$region->name = $name;
		$region->code = $code;
		$region->state = $state;
		$region->parent_type = $parent_type;
		$region->parent_uid = !empty($parent_type) ? $parent_uid : 0;
		$region->uid = 0;
		$region->params = '';

		$region->store();

		$log = $id ? 'COM_ES_ACTION_LOG_REGION_UPDATED' : 'COM_ES_ACTION_LOG_REGION_CREATED';

		$this->actionlog->log($log, 'region', [
					'name' => $region->name,
					'type' => $region->type,
					'link' => 'index.php?option=com_easysocial&view=regions&layout=form&id=' . $region->id
				]);

		$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGIONS_STORED_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $this->getTask(), $region);
	}

	public function moveUp()
	{
		return $this->move(-1);
	}

	public function moveDown()
	{
		return $this->move(1);
	}

	private function move($index)
	{
		$layout = $this->input->getString('layout');

		if (empty($layout)) {
			$layout = 'country';
		}

		$ids = $this->input->get('cid', '', 'var');

		$db = ES::db();

		foreach ($ids as $id) {
			$table = ES::table('Region');
			$table->load($id);

			$filter = $db->nameQuote('type') . ' = ' . $db->quote($layout) . ' AND ' . $db->nameQuote('parent_uid') . ' = ' . $db->quote($table->parent_uid);

			$table->move($index, $filter);
		}

		$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGIONS_ORDERED_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}
}
