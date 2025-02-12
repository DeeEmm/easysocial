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

ES::import('admin:/tables/table');

class SocialTableBadge extends SocialTable
{
	public $id = null;
	public $command = null;
	public $extension = null;
	public $title = null;
	public $description = null;
	public $howto = null;
	public $alias = null;
	public $avatar = null;
	public $created = null;
	public $state = null;
	public $frequency = null;

	public $achieve_type = null;
	public $points_increase_rule = 0;
	public $points_decrease_rule = 0;
	public $points_threshold = null;

	// Variables stored internally
	public $achieved_date = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_badges' , 'id' , $db);
	}

	/**
	 * Retrieves the extension translation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getExtensionTitle()
	{
		$extension = 'COM_EASYSOCIAL';

		if ($this->extension != SOCIAL_COMPONENT_NAME) {
			$extension = strtoupper($this->extension);

			// Load custom language
			ES::language()->load($this->extension , JPATH_ROOT);
			ES::language()->load($this->extension , JPATH_ADMINISTRATOR);
		}

		$text = $extension . '_BADGES_EXTENSION_' . strtoupper($this->extension);

		return JText::_($text);
	}

	/**
	 * Retrieve a number of users who achieved this badge.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalAchievers()
	{
		$model = ES::model('Badges');
		$total = $model->getTotalAchievers($this->id);

		return $total;
	}

	/**
	 * Override parent's get behavior so that we can load admin's language file.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function get($key, $default = '')
	{
		ES::language()->loadAdmin();

		return parent::get($key, $default);
	}

	/**
	 * Retrieve a users who has unlocked this badge.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAchievers($options = array())
	{
		$model = ES::model('Badges');

		$users = $model->getAchievers($this->id, $options);

		return $users;
	}

	/**
	 * Retrieve the badge permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermalink($xhtml = false , $external = false)
	{
		$url = ESR::badges(array('id' => $this->getAlias() , 'external' => $external , 'layout' => 'item') , $xhtml);

		return $url;
	}

	/**
	 * Retrieves the alias for this badge
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlias()
	{
		$alias = $this->id . ':' . $this->alias;

		return $alias;
	}

	/**
	 * Override parent's delete implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		// Delete the image as well
		if ($this->command == 'custom.badge') {
			jimport('joomla.filesystem.file');

			$storage = JPATH_ROOT . '/' . $this->avatar;

			if (JFile::exists($storage)) {
				JFile::delete($storage);
			}
		}

		$state = parent::delete();

		// Get the model
		$model = ES::model('Badges');

		// Delete the user's badge associations
		$model->deleteAssociations($this->id);

		// Delete the user's badge history
		$model->deleteHistory($this->id);

		// Delete any stream related items for this badge
		$stream = ES::stream();
		$stream->delete($this->id , SOCIAL_TYPE_BADGES);

		return $state;
	}

	/**
	 * Retrieves the avatar of the badge
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAvatar($relative = false)
	{
		jimport('joomla.filesystem.file');

		// Allow template overrides for badges
		$app = JFactory::getApplication();
		$avatar = basename($this->avatar);
		$override = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/com_easysocial/badges/' . $avatar;

		$uriRoot = rtrim(JURI::root(), '/') . '/';

		if (JFile::exists($override)) {
			$url = 'templates/' . $app->getTemplate() . '/html/com_easysocial/badges/' . $avatar;

			if (!$relative) {
				$url = $uriRoot . $url;
			}

			return $url;
		}

		// Construct the avatar file.
		$file = JPATH_ROOT . '/' . $this->avatar;

		// Test if the file exists.
		if (!JFile::exists($file)) {

			// Default icon
			$default = 'media/com_easysocial/badges/empty.png';

			if (!$relative) {
				$default = $uriRoot . $default;
			}

			return $default;
		}

		$url = $this->avatar;

		if (!$relative) {
			$url = $uriRoot . $url;
		}

		return $url;
	}

	/**
	 * Retrieves the path to the avatar storage
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	public function getAvatarStorage()
	{
		$config = ES::config();
		$storage = JPATH_ROOT . $config->get('badges.storage');
		$storage = rtrim($storage, '/');

		return $storage;
	}

	/**
	 * Loads the point record given the composite indices.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadByCommand($extension , $command)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select($this->_tbl);

		$sql->where('command', $command);
		$sql->where('extension', $extension);

		$db->setQuery($query);

		$row = $db->loadObject();

		if (!$row) {
			return false;
		}

		return parent::bind($row);
	}

	/**
	 * Retrieves the achievement date
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAchievedDate()
	{
		$date = ES::date($this->achieved_date);

		return $date;
	}

	/**
	 * Loads the badge language based on the extension
	 * @since	1.0
	 * @access	public
	 *
	 */
	public function loadLanguage()
	{
		if (empty($this->extension)) {
			return;
		}

		$lang = ES::language();

		$lang->load($this->extension, JPATH_ROOT);
		$lang->load($this->extension, JPATH_ADMINISTRATOR);
	}

	/**
	 * Get badge title
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTitle()
	{
		$title = JText::_($this->title);

		return $title;
	}

	/**
	 * Generate a random hash title for the avatar file name
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	public function generateAvatarFileName($extension)
	{
		$title = $this->id;
		$hash = md5($title) . $extension;

		return $hash;
	}

	/**
	 * Uploads a new avatar for the badge
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	public function uploadAvatar($file)
	{
		jimport('joomla.filesystem.file');

		$image = ES::image();
		$image->load($file['tmp_name']);

		// Generate a file title
		$fileName = $this->generateAvatarFileName($image->getExtension());

		// Copy the file into the badges folder
		$config = ES::config();
		$storage = $this->getAvatarStorage() . '/' . $fileName;

		$state = JFile::copy($file['tmp_name'], $storage);

		if (!$state) {
			$this->setError('Error copying image file into ' . $storage);
			return false;
		}

		$this->avatar = ltrim($config->get('badges.storage'), '/') . '/' . $fileName;
		return $this->store();
	}

	/**
	 * Converts a user object into an object that can be exported
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $extended = false)
	{
		ES::language()->loadSite();
		$model = ES::model('Badges');

		$achievers = $model->getAchievers($this->id, array('limit' => 21, 'start' => 0));
		$users = array();

		foreach ($achievers as $achiever) {
			$user = $achiever->toExportData($viewer);

			$users[] = $user;
		}

		$data = array(
			'id' => $this->id,
			'command' => $this->command,
			'title' => JText::_($this->title),
			'description' => JText::_($this->description),
			'howto' => JText::_($this->howto),
			'avatar' => ES::getUrl($this->avatar),
			'created' => $this->created,
			'state' => $this->state,
			'achieved_date' => $this->getAchievedDate()->format(JText::_('DATE_FORMAT_LC1'))
		);

		if ($extended) {
			$data['totalAchievers'] = $model->getTotalAchievers($this->id);
			$data['achievers'] = $users;
		}

		return $data;
	}
}
