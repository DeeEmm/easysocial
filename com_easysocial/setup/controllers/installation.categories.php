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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationCategories extends EasySocialSetupController
{
	/**
	 * Install alert rules
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$type = $this->input->get('type', '', 'cmd');

		$this->engine();

		return $this->$type();
	}

	/**
	 * Creates default group categories
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function group()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories');
		$sql->column('COUNT(1)');
		$sql->where('type', SOCIAL_TYPE_GROUP);

		$db->setQuery($sql);
		$total 	= $db->loadResult();

		// There are categories already, we shouldn't be doing anything here.
		if ($total) {
			$result = $this->getResultObj('Skipping default group category creation as there are already categories created on the site.', true);

			return $this->output($result);
		}

		$categories = array('general','automobile','technology','business','music');

		foreach ($categories as $categoryKey) {
			$results[] = $this->createGroupCategory($categoryKey);
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class 	= $obj->state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}

	/**
	 * Creates default page categories
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function page()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories');
		$sql->column('COUNT(1)');
		$sql->where('type', SOCIAL_TYPE_PAGE);

		$db->setQuery($sql);
		$total = $db->loadResult();

		// There are categories already, we shouldn't be doing anything here.
		if ($total) {
			$result = $this->getResultObj('Skipping default page category creation as there are already categories created on the site.', true);
			return $this->output($result);
		}

		$categories = array('general','automobile','brand','business','artist', 'organization');

		foreach ($categories as $categoryKey) {
			$results[] = $this->createPageCategory($categoryKey);
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach($results as $obj) {
			$class = $obj->state ? 'success' : 'error';
			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}

	/**
	 * Creates default group categories
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function event()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories');
		$sql->column('COUNT(1)');
		$sql->where('type', SOCIAL_TYPE_EVENT);

		$db->setQuery($sql);
		$total = $db->loadResult();

		// There are categories already, we shouldn't be doing anything here.
		if ($total) {
			$result = $this->getResultObj('Skipping default event category creation as there are already categories created on the site.', true);

			return $this->output($result);
		}

		$categories = array('general', 'meeting');

		foreach ($categories as $categoryKey) {
			$results[] = $this->createEventCategory($categoryKey);
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class = $obj->state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}

	/**
	 * Creates default video categories
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function video()
	{
		$db = ES::db();
		$sql = $db->sql();

		// Check if there are any video categories already exists on the site
		$sql->select('#__social_videos_categories');
		$sql->column('COUNT(1)');

		$db->setQuery($sql);
		$total = $db->loadResult();

		// There are categories already, we shouldn't be doing anything here.
		if ($total) {
			$result = $this->getResultObj('Skipping default video category creation as there are already categories created on the site.', true);

			return $this->output($result);
		}

		$categories = array('General', 'Music', 'Sports', 'News', 'Gaming', 'Movies', 'Documentary', 'Fashion', 'Travel', 'Technology');
		$i = 0;

		foreach ($categories as $categoryKey) {
			$results[] = $this->createVideoCategory($categoryKey, $i);
			$i++;

		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class = $obj->state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}

	/**
	 * Creates default marketplace categories
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function marketplace()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_marketplaces_categories');
		$sql->column('COUNT(1)');

		$db->setQuery($sql);
		$total 	= $db->loadResult();

		// There are categories already, we shouldn't be doing anything here.
		if ($total) {
			$result = $this->getResultObj('Skipping default marketplace category creation as there are already categories created on the site.', true);

			return $this->output($result);
		}

		$categories = array('vehicles','apparel','electronics','entertainment','hobbies');

		foreach ($categories as $categoryKey) {
			$results[] = $this->createMarketplaceCategory($categoryKey);
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class 	= $obj->state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}

	/**
	 * Creates default audio genres
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function audio()
	{
		$db = ES::db();
		$sql = $db->sql();

		// Check if there are any audio genre already exists on the site
		$sql->select('#__social_audios_genres');
		$sql->column('COUNT(1)');

		$db->setQuery($sql);
		$total = $db->loadResult();

		// There are genres already, we shouldn't be doing anything here.
		if ($total) {
			$result = $this->getResultObj('Skipping default audio genre creation as there are already genres created on the site.', true);

			return $this->output($result);
		}

		$genres = array('Country', 'Rock', 'Disco', 'Pop', 'Classical', 'Instrumental', 'Techno', 'Alternative', 'Jazz', 'Blues');
		$i = 0;

		foreach ($genres as $genreKey) {
			$results[] = $this->createAudioGenre($genreKey, $i);
			$i++;

		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class = $obj->state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}


	public function createGroupCategory($categoryTitle)
	{
		$key = strtoupper($categoryTitle);
		$title = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_GROUP_CATEGORY_' . $key);
		$desc = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_GROUP_CATEGORY_' . $key . '_DESC');

		$category = ES::table('GroupCategory');
		$category->alias = strtolower($categoryTitle);
		$category->title = $title;
		$category->description = $desc;
		$category->type = SOCIAL_TYPE_GROUP;
		$category->created = ES::date()->toSql();
		$category->uid = ES::user()->id;
		$category->state = SOCIAL_STATE_PUBLISHED;

		$category->store();
		$category->assignWorkflow();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('Created group category <b>%1$s</b>', $title);

		return $result;
	}

	public function createPageCategory($categoryTitle)
	{
		$key = strtoupper($categoryTitle);
		$title = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_PAGE_CATEGORY_' . $key);
		$desc = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_PAGE_CATEGORY_' . $key . '_DESC');

		$category = ES::table('PageCategory');
		$category->alias = strtolower($categoryTitle);
		$category->title = $title;
		$category->description = $desc;
		$category->type = SOCIAL_TYPE_PAGE;
		$category->created = ES::date()->toSql();
		$category->uid = ES::user()->id;
		$category->state = SOCIAL_STATE_PUBLISHED;

		$category->store();
		$category->assignWorkflow();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('Created page category <b>%1$s</b>', $title);

		return $result;
	}

	public function createEventCategory($categoryTitle)
	{
		$key = strtoupper($categoryTitle);
		$title = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_EVENT_CATEGORY_' . $key);
		$desc = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_EVENT_CATEGORY_' . $key . '_DESC');

		$category = ES::table('EventCategory');
		$category->alias = strtolower($categoryTitle);
		$category->title = $title;
		$category->description = $desc;
		$category->type = SOCIAL_TYPE_EVENT;
		$category->created = ES::date()->toSql();
		$category->uid = ES::user()->id;
		$category->state = SOCIAL_STATE_PUBLISHED;

		$category->store();
		$category->assignWorkflow();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('Created event category <b>%1$s</b>', $title);

		return $result;
	}

	public function createVideoCategory($categoryTitle, $i = 0)
	{
		$key = strtoupper($categoryTitle);
		$title = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_VIDEO_CATEGORY_' . $key);
		$desc = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_VIDEO_CATEGORY_' . $key . '_DESC');

		$category = ES::table('VideoCategory');
		$category->title = ucfirst($title);
		$category->alias = strtolower($title);
		$category->description = $desc;

		if ($i == 0) {
			$category->default = true;
		}

		// Get the current user's id
		$category->user_id = ES::user()->id;

		$category->state = true;
		$category->store();


		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('Created video category <b>%1$s</b>', $title);

		return $result;
	}

	public function createAudioGenre($genreTitle, $i = 0)
	{
		$key = strtoupper($genreTitle);
		$title = JText::_('COM_ES_INSTALLATION_DEFAULT_AUDIO_GENRE_' . $key);
		$desc = JText::_('COM_ES_INSTALLATION_DEFAULT_AUDIO_GENRE_' . $key . '_DESC');

		$genre = ES::table('AudioGenre');
		$genre->title = ucfirst($title);
		$genre->alias = strtolower($title);
		$genre->description = $desc;

		if ($i == 0) {
			$genre->default = true;
		}

		// Get the current user's id
		$genre->user_id = ES::user()->id;

		$genre->state = true;
		$genre->store();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('Created audio genre <b>%1$s</b>', $title);

		return $result;
	}

	public function createMarketplaceCategory($categoryTitle)
	{
		$key = strtoupper($categoryTitle);
		$title = JText::_('COM_ES_INSTALLATION_DEFAULT_MARKETPLACE_CATEGORY_' . $key);
		$desc = JText::_('COM_ES_INSTALLATION_DEFAULT_MARKETPLACE_CATEGORY_' . $key . '_DESC');

		$category = ES::table('MarketplaceCategory');
		$category->alias = strtolower($categoryTitle);
		$category->title = $title;
		$category->description = $desc;
		$category->created = ES::date()->toSql();
		$category->uid = ES::user()->id;
		$category->state = SOCIAL_STATE_PUBLISHED;

		$category->store();
		$category->assignWorkflow();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('Created marketplace category <b>%1$s</b>', $title);

		return $result;
	}
}
