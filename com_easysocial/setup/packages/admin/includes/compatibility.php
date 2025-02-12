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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Users\Site\Model\RegistrationModel;
use Joomla\Component\Users\Site\Model\ProfileModel;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Finder\Site\Model\SuggestionsModel;
use Joomla\Component\Finder\Site\Model\SearchModel;
use Joomla\CMS\User\UserHelper;
use Joomla\Event\Event;

if (!defined('SOCIAL_COMPONENT_CLI')) {
	if (!ESUtility::isJoomla4()) {
		require_once(JPATH_ROOT . '/components/com_content/helpers/route.php');
		require_once(JPATH_ROOT . '/components/com_finder/models/search.php');

		// before we can include this file, we need to supress the notice error of this key FINDER_PATH_INDEXER due to the way this key defined in /com_finder/models/search.php
		@require_once(JPATH_ROOT . '/components/com_finder/models/suggestions.php');
	}
}

class ESUtility {

	/**
	 * Retrieves Joomla version
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getJoomlaVersion()
	{
		static $version = null;

		if (is_null($version)) {
			$jVerArr = explode('.', JVERSION);
			$version = $jVerArr[0] . '.' . $jVerArr[1];
		}

		return $version;
	}

	/**
	 * Determines if the site is on Joomla 3
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function isJoomla31()
	{
		$state = false;

		if (ESUtility::getJoomlaVersion() >= '3.1' && !ESUtility::isJoomla4()) {
			$state = true;
		}

		return $state;
	}

	/**
	 * Determines if the site is on Joomla 4
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function isJoomla4()
	{
		static $isJoomla4 = null;

		if (is_null($isJoomla4)) {
			$currentVersion = ESUtility::getJoomlaVersion();
			$isJoomla4 = version_compare($currentVersion, '4.0') !== -1;

			return $isJoomla4;
		}

		return $isJoomla4;
	}

	/**
	 * Determines if the site is on Joomla 4
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function isJoomla42()
	{
		static $isJoomla42 = null;

		if (is_null($isJoomla42)) {
			$currentVersion = ESUtility::getJoomlaVersion();
			$isJoomla42 = version_compare($currentVersion, '4.2') !== -1;

			return $isJoomla42;
		}

		return $isJoomla42;
	}

	/**
	 * Determines if the site is on Joomla 5
	 *
	 * @since	1.1
	 * @access	public
	 */
	public static function isJoomla5()
	{
		static $isJoomla5 = null;

		if (is_null($isJoomla5)) {
			$currentVersion = ESUtility::getJoomlaVersion();
			$isJoomla5 = version_compare($currentVersion, '5.0') !== -1;

			return $isJoomla5;
		}

		return $isJoomla5;
	}
}

if (!ESUtility::isJoomla4()) {
	class ESStringBase extends JString
	{
	}
}

if (ESUtility::isJoomla4()) {
	class ESStringBase extends Joomla\String\StringHelper
	{
	}
}

class ESJString extends ESStringBase
{
	/**
	 * Override the parent method to add additional checking
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public static function trim($str, $charlist = false)
	{
		// Backward compatibility for PHP 8.1
		if (!$str) {
			return '';
		}

		return parent::trim($str, $charlist);
	}
}

class ESCompat
{
	/**
	 * Determines if this is from the Joomla backend
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function isFromAdmin()
	{
		if (ESUtility::isJoomla4()) {
			$app = JFactory::getApplication();
			$admin = $app->isClient('administrator');

			return $admin;
		}

		$app = JFactory::getApplication();
		$admin = $app->isAdmin();

		return $admin;
	}

	/**
	 * Render Joomla editor since J4 and J3 does it differently
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getEditor($editorType = null)
	{
		if (!$editorType) {
			$jconfig = ES::jconfig();

			$editorType = $jconfig->get('editor');
		}

		if (ESUtility::isJoomla4()) {
			$editor = Joomla\CMS\Editor\Editor::getInstance($editorType);

			return $editor;
		}

		$editor = JFactory::getEditor($editorType);

		if ($editorType == 'none' || $editorType == 'codemirror') {
			JHtml::_('behavior.core');
		}

		return $editor;
	}

	/**
	 * Abstract method to generate a crypted password for Joomla 3 and Joomla 4
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false)
	{
		// Joomla 3 and below
		if (!ESUtility::isJoomla4()) {
			$result = JUserHelper::getCryptedPassword($plaintext, $salt, $encryption, $show_encrypt);

			return $result;
		}

		$result = UserHelper::hashPassword($plaintext);

		return $result;
	}

	/**
	 * Similar to getCryptedPassword but we prepend the :salt
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getCryptedPasswordWithSalt($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false)
	{
		// Joomla 3 and below
		if (!ESUtility::isJoomla4()) {
			$result = JUserHelper::getCryptedPassword($plaintext, $salt, $encryption, $show_encrypt);
			$result .= ':' . $salt;

			return $result;
		}

		// For Joomla 4, we do not need to do anything here
		$result = UserHelper::hashPassword($plaintext);

		return $result;
	}

	/**
	 * Load JQuery from Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function renderJQueryFramework()
	{
		if (ESUtility::isJoomla4()) {
			HTMLHelper::_('jquery.framework');

			return;
		}

		JHTML::_('jquery.framework');
	}

	/**
	 * Renders color picker library from Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function renderColorPicker()
	{
		if (ESUtility::isJoomla4()) {
			HTMLHelper::_('jquery.framework');
			HTMLHelper::_('script', 'vendor/minicolors/jquery.minicolors.min.js', array('version' => 'auto', 'relative' => true));
			HTMLHelper::_('stylesheet', 'vendor/minicolors/jquery.minicolors.css', array('version' => 'auto', 'relative' => true));
			HTMLHelper::_('script', 'system/fields/color-field-adv-init.min.js', array('version' => 'auto', 'relative' => true));
			return;
		}

		JHTML::_('behavior.colorpicker');
	}

	/**
	 * Renders modal library from Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function renderModalLibrary()
	{
		if (ESUtility::isJoomla4()) {
			HTMLHelper::_('bootstrap.framework');
			return;
		}

		JHTML::_('behavior.modal');
	}
}

class ESFactory
{
	/**
	 * Returns a query variable by name.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getApplication()
	{
		if (ESUtility::isJoomla31()) {
			$app = JFactory::getApplication();
		}

		if (ESUtility::isJoomla4()) {
			$app = Joomla\CMS\Factory::getApplication();
		}

		return $app;
	}
}

class ESFinderHelper
{
	/**
	 * Method to get extra data for a content before being indexed.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getContentExtras($item)
	{
		if (ESUtility::isJoomla31()) {
			require_once(JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');
			$data = FinderIndexerHelper::getContentExtras($item);
		}

		if (ESUtility::isJoomla4()) {
			$data = Joomla\Component\Finder\Administrator\Indexer\Helper::getContentExtras($item);
		}

		return $data;
	 }
}

use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;


if (ESUtility::isJoomla4()) {
	class ESFinderIndexerAdapterBase extends Adapter{

		protected function index(Result $item)
		{
			$data = $this->proxyIndex($item);
			return $data;
		}

		protected function setup()
		{
			return parent::setup();
		}
	}
}

if (!ESUtility::isJoomla4() && !defined('SOCIAL_COMPONENT_CLI')) {
	require_once(JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

	class ESFinderIndexerAdapterBase extends FinderIndexerAdapter{

		protected function index(FinderIndexerResult $item, $format = 'html')
		{
			$data = $this->proxyIndex($item, $format);
			return $data;
		}

		protected function setup()
		{
			return parent::setup();
		}
	}
}

if (!defined('SOCIAL_COMPONENT_CLI')) {
	if (!ESUtility::isJoomla4()) {
		class ESFinderModelSearchBase extends FinderModelSearch
		{
		}
	}

	if (ESUtility::isJoomla4()) {
		class ESFinderModelSearchBase extends SearchModel
		{
		}
	}

	class ESFinderModelSearch extends ESFinderModelSearchBase
	{
		/**
		 * Method to get the results of the query.
		 *
		 * @since   4.0.0
		 * @access  public
		 */
		public function getOutCome()
		{
			if (ESUtility::isJoomla4()) {
				return $this->getItems();
			}

			return $this->getResults();
		}
	}

	if (!ESUtility::isJoomla4()) {
		class ESFinderModelSuggestionsBase extends FinderModelSuggestions
		{
		}
	}

	if (ESUtility::isJoomla4()) {
		class ESFinderModelSuggestionsBase extends SuggestionsModel
		{
		}
	}

	class ESFinderModelSuggestions extends ESFinderModelSuggestionsBase
	{
	}
}

class ESApplicationHelper
{
	/**
	 * Load up ApplicationHelper
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function load()
	{
		if (ESUtility::isJoomla4()) {
			$app = new Joomla\CMS\Application\ApplicationHelper;

			return $app;
		}

		$app = new JApplicationHelper();

		return $app;
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getHash($seed)
	{
		$app = self::load();

		return $app::getHash($seed);
	}
}

class ESJLanguage
{
	/**
	 * Retrieves a list of known languages
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getKnownLanguages()
	{
		if (ESUtility::isJoomla4()) {
			$language = LanguageHelper::getKnownLanguages();

			return $language;
		}

		$language = JLanguage::getKnownLanguages();

		return $language;
	}
}

class ESUserModel
{
	/**
	 * Load joomla's user forms
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function load()
	{
		if (ESUtility::isJoomla4()) {
			$model = new Joomla\Component\Users\Administrator\Model\UserModel();

			return $model;
		}

		require_once(JPATH_ADMINISTRATOR . '/components/com_users/models/user.php');
		$model = new UsersModelUser();

		return $model;
	}
}

if (!defined('SOCIAL_COMPONENT_CLI')) {

	if (ESUtility::isJoomla4()) {
		class ESUserModelRegistrationBase extends RegistrationModel
		{
			public function __construct()
			{
				// load com_user model form from frontend.
				Form::addFormPath(JPATH_ROOT . '/components/com_users/forms');
			}
		}
	}

	if (!ESUtility::isJoomla4()) {
		require_once(JPATH_ROOT . '/components/com_users/models/registration.php');

		class ESUserModelRegistrationBase extends UsersModelRegistration
		{
		}
	}

	class ESUsersModelRegistration extends ESUserModelRegistrationBase
	{
		/**
		 * Get user custom fields if available
		 *
		 * @since	4.0.0
		 * @access	public
		 */
		public function getForm($data = array(), $loadData = true)
		{
			if (!$this->isEnabled()) {
				return false;
			}

			// add com_users forms and fields path
			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_users/models/forms');
			JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_users//models/fields');
			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_users//model/form');
			JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_users//model/field');

			$form = parent::getForm($data, $loadData);
			return $form;
		}

		/**
		 * Checks if custom fields supported or not.
		 *
		 * @since	4.0.0
		 * @access	public
		 */
		public function isEnabled()
		{
			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

			// Only joomla 3.7.x and above have custom fields
			if (!class_exists('FieldsHelper')) {
				return false;
			}

			return true;
		}
	}

	if (ESUtility::isJoomla4()) {
		class ESUsersModelProfileBase extends ProfileModel
		{
			public function __construct()
			{
				// load com_user model form from frontend.
				Form::addFormPath(JPATH_ROOT . '/components/com_users/forms');
			}
		}
	}
	if (!ESUtility::isJoomla4()) {
		require_once(JPATH_SITE.'/components/com_users/models/profile.php');
		class ESUsersModelProfileBase extends UsersModelProfile {}
	}

	class ESUsersModelProfile extends ESUsersModelProfileBase {}
}

class ESArchive
{
	/**
	 * Load Joomla's Archive
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function load()
	{
		if (ESUtility::isJoomla4()) {
			$archive = new Joomla\Archive\Archive();

			return $archive;
		}

		$archive = new JArchive();

		return $archive;
	}

	/**
	 * Perform extract method from Joomla Archive
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function extract($destination, $extracted)
	{
		$archive = self::load();

		if (!ESUtility::isJoomla4()) {
			$state = $archive::extract($destination, $extracted);

			return $state;
		}

		$state = $archive->extract($destination, $extracted);

		return $state;
	}

	/**
	 * Get a file compression adapter from Joomla Archive
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getAdapter($type)
	{
		$archive = self::load();

		if (!ESUtility::isJoomla4()) {
			$adapter = $archive::getAdapter($type);

			return $adapter;
		}

		$adapter = $archive->getAdapter($type);

		return $adapter;
	}
}

class ESArrayHelper
{
	/**
	 * Utility function to map an object to an array
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function fromObject($data)
	 {
		if (ESUtility::isJoomla4()) {
			$data = Joomla\Utilities\ArrayHelper::fromObject($data);
			return $data;
		}


		$data = JArrayHelper::fromObject($data);
		return $data;
	 }

	/**
	 * Utility function to return a value from a named array or a specified default
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getValue($array, $name, $default = null, $type = '')
	{
		if (ESUtility::isJoomla4()) {
			$data = Joomla\Utilities\ArrayHelper::getValue($array, $name, $default, $type);
			return $data;
		}

		$data = JArrayHelper::getValue($array, $name, $default, $type);
		return $data;
	}

	/**
	 * Method to convert array to integer values
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function toInteger($array, $default = null)
	{
		if (ESUtility::isJoomla4()) {
			$data = Joomla\Utilities\ArrayHelper::toInteger($array, $default);

			return $data;
		}

		$data = JArrayHelper::toInteger($array, $default);
		return $data;
	}

	/**
	 * Method to determine if an array is an associative array.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function isAssociative($array)
	{
		if (ESUtility::isJoomla4()) {
			$isAssociative = Joomla\Utilities\ArrayHelper::isAssociative($array);

			return $isAssociative;
		}

		$isAssociative = JArrayHelper::isAssociative($array);
		return $isAssociative;
	}
}

class ESDispatcher
{
	/**
	 * Load the Joomla Dispacther
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function load()
	{
		if (ESUtility::isJoomla5()) {
			$dispatcher = ESFactory::getApplication()->getDispatcher();
			return $dispatcher;
		}

		if (ESUtility::isJoomla4()) {
			$dispatcher = ESFactory::getApplication();
			return $dispatcher;
		}

		$dispatcher = JDispatcher::getInstance();

		return $dispatcher;
	}

	/**
	 * Triggers an event
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function trigger($eventName, $data = array())
	{
		$dispatcher = self::load();

		if (ESUtility::isJoomla5()) {

			$data = new Joomla\Event\Event($eventName, $data);
			$result = $dispatcher->dispatch($eventName, $data);

            return !isset($result['result']) || \is_null($result['result']) ? [] : $result['result'];	
		}

		if (ESUtility::isJoomla4()) {
			return $dispatcher->triggerEvent($eventName, $data);
		}

		if (ESUtility::isJoomla31()) {
			return $dispatcher->trigger($eventName, $data);
		}

		// if (ESUtility::isJoomla4()) {

		// 	if (!($data instanceof Event)) {
		// 		$data = new Event($eventName, $data);
		// 	}

		// 	$result = $dispatcher->dispatch($eventName, $data);

		// 	// TODO: This is referencing the way J4 handles the result. Need to be updated when J4 is updated to the stable
		// 	$result = !isset($result['result']) || \is_null($result['result']) ? [] : $result['result'];

		// 	return $result;
		// }

		// return $dispatcher->trigger($eventName, $data);
	}
}

class ESRouter
{
	/**
	 * Determine whether the site enable SEF.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getMode()
	{
		static $mode = null;

		if (is_null($mode)) {
			$jConfig = ES::jConfig();
			$mode = $jConfig->get('sef');

			if (ES::isFromAdmin()) {
				$mode = false;
			}
		}

		return $mode;
	}
}

class ESContentHelperRoute
{
	/**
	 * Get the article route.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getArticleRoute($id, $catid = 0, $language = 0, $layout = null)
	{
		if (ESUtility::isJoomla4()) {
			return RouteHelper::getArticleRoute($id, $catid, $language, $layout);
		}

		return ContentHelperRoute::getArticleRoute($id, $catid, $language, $layout);
	}

	/**
	 * Get the category route.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getCategoryRoute($catid, $language = 0, $layout = null)
	{
		if (ESUtility::isJoomla4()) {
			return RouteHelper::getCategoryRoute($catid, $language, $layout);
		}

		return ContentHelperRoute::getCategoryRoute($catid, $language, $layout);
	}

	/**
	 * Get the form route.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getFormRoute($id)
	{
		if (ESUtility::isJoomla4()) {
			return RouteHelper::getFormRoute($id);
		}

		return ContentHelperRoute::getFormRoute($id);
	}
}

class ESComponentHelper
{
	/**
	 * Checks if the component is enabled
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function isEnabled($option)
	{
		if (ESUtility::isJoomla31()) {
			jimport('joomla.application.component.helper');
			$isEnabled = JComponentHelper::isEnabled($option);
		}

		if (ESUtility::isJoomla4()) {
			$isEnabled = Joomla\CMS\Component\ComponentHelper::isEnabled($option);
		}

		return $isEnabled;
	 }
}

class ESUserHelper
{
	/**
	 * Generates an activation token object that can be used for password resets
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function generateActivationToken()
	{
		$obj = new stdClass();
		$obj->token = null;
		$obj->salt = null;
		$obj->hashedToken = null;

		if (!ESUtility::isJoomla4()) {
			// Set the confirmation token.
			$obj->token = JApplication::getHash(JUserHelper::genRandomPassword());
			$obj->salt = JUserHelper::getSalt('crypt-md5');
			$obj->hashedToken = md5($obj->token . $obj->salt) . ':' . $obj->salt;

			return $obj;
		}

		// Set the confirmation token.
		$obj->token = ESApplicationHelper::getHash(UserHelper::genRandomPassword());
		$obj->hashedToken = UserHelper::hashPassword($obj->token);

		return $obj;
	}

	/**
	 * Verify password reset
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function verifyResetPassword($token, $activation)
	{
		if (ESUtility::isJoomla4()) {
			return UserHelper::verifyPassword($token, $activation);
		}

		// Joomla 3.x
		// Split the crypt and salt
		$parts = explode(':', $activation);
		$crypt = $parts[0];

		if (!isset($parts[1])) {
			throw ES::exception('COM_EASYSOCIAL_USERS_NO_SUCH_USER_WITH_EMAIL');
		}

		$salt = $parts[1];

		// Manually pass in crypt type as md5-hex because when we generate the activation token, it is crypted with crypt-md5, and due to Joomla 3.2 using bcrypt by default, this part fails. We revert back to Joomla 3.0's default crypt format, which is md5-hex.
		$test = ESCompat::getCryptedPassword($token, $salt);

		if ($crypt != $test) {
			throw ES::exception('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_INVALID_CODE');
		}

		return true;
	}

	/**
	 * Abstract layer for verifying a user's password
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function verifyUserPassword($id, $storedPasswordHash, $enteredPassword)
	{
		static $cache = [];

		if (!isset($cache[$id])) {

			// Default to false
			$cache[$id] = false;

			// Joomla 4
			if (ESUtility::isJoomla4()) {
				$cache[$id] = UserHelper::verifyPassword($enteredPassword, $storedPasswordHash);

				return $cache[$id];
			}

			// Joomla 3
			if (strpos($storedPasswordHash, '$P$') === 0) {
				$phpass = new PasswordHash(10, true);

				$match = $phpass->CheckPassword($storedPasswordHash, $storedPasswordHash);
			} elseif (substr($storedPasswordHash, 0, 4) == '$2y$') {
				$storedPasswordHash60 = substr($storedPasswordHash, 0, 60);

				$match = false;

				if (JCrypt::hasStrongPasswordSupport()) {
					$match = password_verify($enteredPassword, $storedPasswordHash60);
				}
			} elseif (substr($storedPasswordHash, 0, 8) == '{SHA256}') {
				$parts = explode(':', $storedPasswordHash);
				$crypt = $parts[0];
				$salt = @$parts[1];

				$testcrypt = JUserHelper::getCryptedPassword($storedPasswordHash, $salt, 'sha256', false);

				$match = $storedPasswordHash == $testcrypt;
			} else {
				$parts = explode(':', $storedPasswordHash);
				$salt = @$parts[1];

				// Compile the hash to compare
				// If the salt is empty AND there is a ':' in the original hash, we must append ':' at the end
				$testcrypt = md5($enteredPassword . $salt) . ($salt ? ':' . $salt : (strpos($storedPasswordHash, ':') !== false ? ':' : ''));
				$match = JCrypt::timingSafeCompare($storedPasswordHash, $testcrypt);
			}

			$cache[$id] = $match;
		}

		return $cache[$id];
	}
}

if (!defined('SOCIAL_COMPONENT_CLI')) {
	if (ESUtility::isJoomla31()) {
		require_once(JPATH_ROOT . '/administrator/components/com_menus/helpers/menus.php');

		class ESMenuHelperBase extends MenusHelper
		{
		}
	}

	if (ESUtility::isJoomla4()) {
		class ESMenuHelperBase extends Joomla\Component\Menus\Administrator\Helper\MenusHelper
		{
		}
	}

	class ESMenuHelper extends ESMenuHelperBase
	{

	}
}