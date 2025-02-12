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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelAccess extends EasySocialModel
{
	private $data = null;

	public function __construct()
	{
		parent::__construct('access');
	}

	public function getPagination($reload = null)
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	/**
	 * Given the access uid and type, load the params
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getParams($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_access');
		$sql->column('params');
		$sql->where('uid', $uid);
		$sql->where('type', $type);

		$db->setQuery($sql);
		$params	= $db->loadResult();

		return $params;
	}

	/**
	 * Renders the access form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getForm($uid, $type = SOCIAL_TYPE_USERGROUP, $prefix = '', $activeTab = '', $processActiveTab = true)
	{
		$model = ES::model('accessrules');

		// Convert the type to group type
		$group = SOCIAL_TYPE_USER;

		switch ($type) {
			case SOCIAL_TYPE_USER:
			case SOCIAL_TYPE_PROFILES:
				$group = SOCIAL_TYPE_USER;
			break;

			case SOCIAL_TYPE_GROUP:
				$group = SOCIAL_TYPE_GROUP;
				$type = SOCIAL_TYPE_CLUSTERS;
			break;

			case SOCIAL_TYPE_PAGE:
				$group = SOCIAL_TYPE_PAGE;
				$type = SOCIAL_TYPE_CLUSTERS;
			break;

			case SOCIAL_TYPE_EVENT:
				$group = SOCIAL_TYPE_EVENT;
				$type = SOCIAL_TYPE_CLUSTERS;
			break;
		}

		$rules = $model->getAllRules(array(
			'group' => $group,
			'state' => SOCIAL_STATE_PUBLISHED
		));

		if (empty($rules)) {
			return '';
		}

		$forms 	= array();

		$requiredMaxUploadCheck = array('photos.maxsize', 'files.maxsize', 'videos.maxsize', 'photos.uploader.maxsize', 'audios.maxsize');
		$iniMaxUpload = (int) ini_get('upload_max_filesize');

		if (substr(ini_get('upload_max_filesize'), -1) == 'G') {
			$iniMaxUpload = $iniMaxUpload * 1024;
		}

		// Group rules by element
		foreach ($rules as $rule) {

			// Load app language file
			ES::language()->loadApp($rule->group, $rule->element);

			if (!isset($forms[$rule->element])) {
				$forms[$rule->element] = (object) array(
					'title' => JText::_('COM_EASYSOCIAL_ACCESS_TAB_' . strtoupper($rule->element)),
					'desc' => JText::_('COM_EASYSOCIAL_ACCESS_TAB_' . strtoupper($rule->element) . '_DESC'),
					'fields' => array()
				);
			}

			// Init some data to comply with ES::form();

			$rule->label = JText::_($rule->title);
			$rule->tooltip = JText::_($rule->description);

			$rule->maxupload = 0;
			$rule->maxuploadDisplay = '';

			// let check if this rule need to apply the max upload size or not.
			if (in_array($rule->name, $requiredMaxUploadCheck)) {
				$rule->maxupload = $iniMaxUpload; // in mb
				$rule->maxuploadDisplay = JText::sprintf('COM_EASYSOCIAL_ACL_FILEUPLOAD_MAX_NOTICE', $iniMaxUpload);
			}

			if (empty($rule->type)) {
				$rule->type = 'boolean';
			}

			if (!isset($rule->default)) {
				$rule->default = true;
			}

			$forms[$rule->element]->fields[] = $rule;
		}

		// Sort acl groups by alphabetical order
		usort($forms, function($a, $b) {
			$al = strtolower(JText::_($a->title));
			$bl = strtolower(JText::_($b->title));

			if ($al == $bl) {
				return 0;
			}

			return ($al > $bl) ? +1 : -1;
		});

		$form = ES::form();
		$form->load($forms);

		$registry = ES::registry();

		// Get the access data.
		$access	= ES::table('Access');
		$state = $access->load(array('uid' => $uid, 'type' => $type));

		if ($state) {
			$registry = ES::registry($access->params);
		}

		$form->bind($registry);

		$output	= $form->render(true, true, $activeTab, $prefix, $processActiveTab);

		return $output;
	}
}
