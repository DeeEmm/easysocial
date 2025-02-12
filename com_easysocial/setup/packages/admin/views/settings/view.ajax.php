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

require_once(JPATH_ROOT . '/administrator/components/com_easysocial/includes/vendor/autoload.php');

use Nahid\JsonQ\Jsonq;

class EasySocialViewSettings extends EasySocialAdminView
{
	/**
	 * Display confirmation box to remove login image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmRemoveImage()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/settings/dialogs/delete.login.image');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays dialog to confirm reset settings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmReset()
	{
		$section = $this->input->get( 'section', '', 'default');

		$theme = ES::themes();
		$theme->set('section', $section);
		$contents = $theme->output('admin/settings/dialogs/reset');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display confirmation box to purge text based avatars
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmPurgeTextAvatars()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/settings/dialogs/purge.textavatar');

		return $this->ajax->resolve($output);
	}

	/**
	 * Display confirmation box to remove email logo
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmRestoreImage()
	{
		$type = $this->input->get('type');

		$default = explode('_', $type);
		$defaults = array('avatar', 'cover');

		if (in_array($default[1], $defaults)) {
			$type = 'default_' . $default[1];
		}

		$theme = ES::themes();
		$theme->set('type', $type);
		$output = $theme->output('admin/settings/dialogs/restore.image');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows user to import a .json file
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function import()
	{
		$theme = ES::themes();
		$page = $this->input->get('page', '', 'default');

		$theme->set('page', $page);
		$contents = $theme->output('admin/settings/dialogs/import');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Rebuilds the search for settings
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function rebuildSearch()
	{
		$str = $this->input->get('dataString', '', 'raw');


		$jsonObject = json_decode($str);


		foreach ($jsonObject->items as &$item) {

			$item->keywords = array();
			$item->keywords = ES::extractKeyWords($item->label);

			if (isset($item->description)) {
				$item->keywords = array_merge($item->keywords, ES::extractKeyWords($item->description));
			}

			if ($item->keywords) {
				$item->keywords = implode(' ', $item->keywords);
			}
		}

		$jsonString = json_encode($jsonObject);
		$cacheFile = SOCIAL_ADMIN_DEFAULTS . '/cache.json';

		JFile::write($cacheFile, $jsonString);

		$this->info->set('Cache file updated successfully');

		return $this->ajax->resolve();
	}

	/**
	 * Searches for a settings
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function search()
	{
		$query = $this->input->get('text', '', 'word');
		$query = strtolower($query);

		$jsonString = file_get_contents(SOCIAL_ADMIN_DEFAULTS . '/cache.json');
		$jsonString = strtolower($jsonString);

		$jsonq = new Jsonq();
		$jsonq->json($jsonString);

		$result = @$jsonq->from('items')
				->where('keywords', 'contains', $query)
				->groupBy('page')
				->get();

		$theme = ES::themes();
		$theme->set('result', $result);
		$contents = $theme->output('admin/settings/search.result');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display confirmation box to check for valid api key
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function validateApiKey()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/settings/dialogs/verify.api');

		return $this->ajax->resolve($contents);
	}
}
