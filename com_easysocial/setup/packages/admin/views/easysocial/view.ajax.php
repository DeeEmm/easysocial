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

class EasySocialViewEasySocial extends EasySocialAdminView
{
	/**
	 * Renders a confirmation screen before deleting events
	 *
	 * @since   3.2.16
	 * @access  public
	 */
	public function renderConfirmationDialog()
	{
		$title = $this->input->get('title', '', 'default');
		$message = $this->input->get('message', '', 'default');

		$theme = ES::themes();
		$theme->set('title', $title);
		$theme->set('message', $message);

		$contents = $theme->output('admin/dialogs/confirmation');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Retrieves metadata about EasySocial
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getMetaData()
	{
		// Get the current version.
		$local = ES::getLocalVersion();
		$latest = ES::getOnlineVersion();
		$outdated = (version_compare($local, $latest)) === -1;

		$model = ES::model('News');
		$news = $model->getNews();

		if ($news === false) {
			return $this->ajax->reject();
		}

		if ($news->apps) {
			foreach ($news->apps as &$appItem) {
				$date = ES::date($appItem->updated);

				$appItem->lapsed = $date->toLapsed();
				$appItem->day = $date->format('d');
				$appItem->month = $date->format('M');
			}
		}

		$theme = ES::themes();
		$theme->set('items', $news->apps);
		$appNews = $theme->output('admin/news/apps');

		return $this->ajax->resolve($appNews, $local, $outdated);
	}

	/**
	 * Retrieves a list of countries
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getCountries($countries)
	{
		$result = array();

		foreach ($countries as $country) {
			$result[] = $country->country;
		}

		$theme = ES::themes();
		$theme->set('countries', $countries);
		$output = $theme->output('admin/easysocial/widgets/map.table');

		return $this->ajax->resolve($result, $output);
	}

	/**
	 * Clear sef lang check warning message
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function hideLangCheckWarning()
	{
		$config = ES::config();

		// clear the cache warning message
		$config->set('seo.langcheck.error', '0');

		// Convert the config object to a json string.
		$jsonString = $config->toString();

		$configTable = ES::table('Config');
		if (!$configTable->load('site')) {
			$configTable->type  = 'site';
		}

		$configTable->set('value' , $jsonString);
		$state = $configTable->store();

		$this->ajax->resolve($state);
	}
}
