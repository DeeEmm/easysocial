<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewActivities extends EasySocialSiteView
{
	/**
	 * Returns an ajax chain.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function toggle($id, $curState)
	{
		// Determine if there's any errors on the form.
		$error = $this->getError();

		if ($error) {
			return $this->ajax->reject($error);
		}

		$isHidden = $curState ? 0 : 1;
		$message = $curState ? JText::_('COM_ES_ACTIVITY_SHOW_SUCCESSFULLY') : JText::_('COM_ES_ACTIVITY_HIDE_SUCCESSFULLY');

		return $this->ajax->resolve($isHidden, $message);
	}

	public function delete()
	{
		// Determine if there's any errors on the form.
		$error = $this->getError();

		if ($error) {
			return $this->ajax->reject($error);
		}

		$html = JText::_('COM_EASYSOCIAL_ACTIVITY_ITEM_DELETED');

		return $this->ajax->resolve($html);
	}

	public function getActivities($filterType, $data, $nextlimit, $isloadmore = false)
	{
		// Determine if there's any errors on the form.
		$error = $this->getError();

		if ($error) {
			return $this->ajax->reject($error);
		}

		$theme = ES::get('Themes');
		$theme->set('activities', $data);

		$theme->set('nextlimit', $nextlimit);

		$activeType = $this->input->get('type', 'all', 'default');
		switch ($activeType) {
			case 'hiddenapp':
				$title = JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS');
				break;

			case 'hidden':
				$title = JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTIVITIES');
				break;

			case 'hiddenactor':
				$title = JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS');
				break;

			case 'all':
				$title = JText::_('COM_EASYSOCIAL_ACTIVITY_ALL_ACTIVITIES');
				break;

			default:
				$title = JText::sprintf('COM_EASYSOCIAL_ACTIVITY_ITEM_TITLE', ucfirst($activeType));
				break;
		}

		$theme->set('title', $title);

		$output = '';
		if ($isloadmore) {
			if ($data) {

				$filename = "site/activities/items/";

				if ($filterType == 'hiddenapp') {
					$filename .= 'hiddenapp';
				} else if ($filterType == 'hiddenactor') {
					$filename .= 'hiddenactor';
				} else {
					$filename .= 'default';
				}

				$options = array('items' => $data, 'nextlimit' => $nextlimit, 'active' => $activeType);
				$output = $theme->loadTemplate($filename, $options);
			}

			return $this->ajax->resolve($output, $nextlimit);
		} else {

			$theme->set('active', $activeType);
			$theme->set('filterType', $filterType);
			$output = $theme->output('site/activities/default/content');

			$count = $data ? count($data) : 0;

			return $this->ajax->resolve($output, $count);
		}
	}

	/**
	 * Confirmation for deleting an activity item
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$contents = $theme->output('site/activities/dialog.delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Retrieves a list of hidden apps from the stream
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getHiddenApps($data)
	{
		// Determine if there's any errors on the form.
		$error = $this->getError();

		if ($error) {
			return $this->ajax->reject($error);
		}

		$theme = ES::get('Themes');
		$theme->set('title', JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS'));
		$theme->set('activities', $data);
		$theme->set('filtertype', 'hiddenapp');

		$output = $theme->output('site/activities/default/content');
		return $this->ajax->resolve($output, count($data));
	}

	/**
	 * Retrieves a list of hidden apps from the stream
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getHiddenActors($data)
	{
		// Determine if there's any errors on the form.
		$error = $this->getError();

		if ($error) {
			return $this->ajax->reject($error);
		}

		$theme = ES::get('Themes');
		$theme->set('title', JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS'));
		$theme->set('activities', $data);
		$theme->set('filtertype', 'hiddenactor');

		$output = $theme->output('site/activities/default/content');

		return $this->ajax->resolve($output, count($data));

	}

	public function unhideapp()
	{
		$error = $this->getError();

		if ($error) {
			return $this->ajax->reject($error);
		}

		$message = JText::_('COM_EASYSOCIAL_ACTIVITY_APPS_UNHIDE_SUCCESSFULLY');
		return $this->ajax->resolve($message);
	}

	public function unhideactor()
	{
		$error = $this->getError();

		if ($error) {
			return $this->ajax->reject($error);
		}

		$message = JText::_('COM_EASYSOCIAL_ACTIVITY_USERS_UNHIDE_SUCCESSFULLY');

		return $this->ajax->resolve($message);
	}

}
