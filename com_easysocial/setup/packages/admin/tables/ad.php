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

class SocialTableAd extends SocialTable
{
	public $id = null;
	public $advertiser_id = null;
	public $intro = null;
	public $title = null;
	public $content = null;
	public $priority = null;
	public $button_type = null;
	public $cover = null;
	public $link = null;
	public $state = null;
	public $created = null;
	public $start_date = null;
	public $end_date = null;
	public $click = null;
	public $view = null;
	public $log = null;
	public $params = null;

	public function __construct($db)
	{
		parent::__construct('#__social_ads', 'id', $db);
	}

	/**
	 * Update click count
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function click()
	{
		$this->click++;

		$this->store();
	}

	/**
	 * Override the implementation of delete as we also need to delete the cover
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if (!$state) {
			return $state;
		}

		// If deletion was successful, we need to delete the cover file
		$storagePath = JPATH_ROOT . '/' . ltrim($this->getCoverStorage(), '/');

		$exists = JFolder::exists($storagePath);

		if ($exists) {
			JFolder::delete($storagePath);
		}

		return $state;
	}

	/**
	 * Retrieves the path to the cover storage
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getCoverStorage($withId = true)
	{
		$config = ES::config();
		$storage = $config->get('ads.storage') . '/covers';

		if ($withId) {
			$storage .= '/' . $this->id;
		}

		$storage = rtrim($storage, '/');

		return $storage;
	}

	/**
	 * Retrive cover photo
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getCover()
	{
		$storage = $this->getCoverStorage(false);
		$url = rtrim(JURI::root(), '/') . $storage . '/' . $this->cover;

		return $url;
	}

	/**
	 * Normalize the method so caller know what object is this
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getType()
	{
		return SOCIAL_TYPE_ADVERTISEMENT;
	}

	/**
	 * Retrieve priority text
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getPriority()
	{
		$priorities = array('1' => 'COM_ES_ADS_FORM_PRIORITY_LOW',
							'2' => 'COM_ES_ADS_FORM_PRIORITY_MED',
							'3' => 'COM_ES_ADS_FORM_PRIORITY_HIGH',
							'4' => 'COM_ES_ADS_FORM_PRIORITY_HIGHEST');

		return JText::_($priorities[$this->priority]);
	}

	/**
	 * Retrieve button text
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getButtonText()
	{
		$buttons = array('1' => 'COM_ES_ADS_FORM_BUTTON_LISTEN_NOW',
							'2' => 'COM_ES_ADS_FORM_BUTTON_SHOP_NOW',
							'3' => 'COM_ES_ADS_FORM_BUTTON_SIGN_UP',
							'4' => 'COM_ES_ADS_FORM_BUTTON_SUBSCRIBE',
							'5' => 'COM_ES_ADS_FORM_BUTTON_LEARN_MORE');

		return JText::_($buttons[$this->button_type]);
	}

	/**
	 * Retrieve ads link
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getLink($showLink = true)
	{
		$url = $this->link;

		// Do not render a link in ads stream if this ads doesn't set any website link
		if (!$url && !$showLink) {
			return $url;
		}

		// Do not show linkable link if this ads doesn't set any website link
		if (!$url) {
			$url = 'javascript:void(0);';
			return $url;
		}

		if (stristr($url, 'http://') === false && stristr($url, 'https://') === false) {
			$url = 'http://' . $url;
		}

		return $url;
	}

	/**
	 * Get advertiser
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getAdvertiser()
	{
		$table = ES::table('Advertiser');
		$table->load($this->advertiser_id);

		return $table;
	}

	/**
	 * Render advertisement stream html
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function html()
	{
		$advertiser = ES::table('Advertiser');
		$advertiser->load($this->advertiser_id);

		$theme = ES::themes();
		$theme->set('advertisement', $this);
		$theme->set('advertiser', $advertiser);
		$output = $theme->output('site/stream/advertisement/default');

		return $output;
	}

	/**
	 * Determines if this ads has button or not
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function hasButton()
	{
		return $this->button_type;
	}

	/**
	 * Method to export the stream data for REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData($viewer)
	{
		$advertiser = $this->getAdvertiser();

		$item = new stdClass();
		$item->type = 'ads';
		$item->id = uniqid('ads_');
		$item->advertiserName = $advertiser->name;
		$item->advertiserLogo = $advertiser->getLogo();
		$item->intro = $this->intro;
		$item->title = $this->title;
		$item->link = $this->getLink(false);
		$item->content = $this->content;
		$item->hasButton = $this->hasButton();
		$item->buttonText = $item->hasButton ? $this->getButtonText() : '';
		$item->cover = $this->getCover();

		return $item;
	}

	/**
	 * Uploads a cover for the advertisement
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function uploadCover($file)
	{
		jimport('joomla.filesystem.file');

		if (!isset($file['tmp_name']) || (isset($file['error']) && $file['error'] != 0)) {
			$this->setError('COM_ES_ADS_UPLOADED_FILE_ERROR');
			return false;
		}

		$image = ES::image();
		$image->load($file['tmp_name']);

		// If a file previously exist, delete it first
		$existingCover = null;

		if ($this->cover) {
			$existingCover = JPATH_ROOT . '/' . ltrim($this->getCoverStorage(false), '/') . '/' . $this->cover;
		}

		// Generate a file title
		$fileName = md5($this->id . JFactory::getDate()->toSql()) . $image->getExtension();

		// Copy the file into the icon emoji folder
		$config = ES::config();
		$storage = JPATH_ROOT . $this->getCoverStorage();

		if (!JFolder::exists($storage)) {
			JFolder::create($storage);
		}

		$state = JFile::copy($file['tmp_name'], $storage . '/' . $fileName);

		if (!$state) {
			$this->setError('Error copying image file into ' . $storage);
			return false;
		}

		$this->cover = $this->id . '/' . $fileName;

		if ($existingCover && file_exists($existingCover)) {
			JFile::delete($existingCover);
		}

		return $this->store();
	}

	/**
	 * Update view count
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function view()
	{
		$this->view++;

		$this->store();
	}
}
