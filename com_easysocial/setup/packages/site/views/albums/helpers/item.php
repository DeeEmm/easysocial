<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License ors
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewAlbumsItemHelper extends EasySocial
{
	/**
	 * Retrieve albums library
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAlbumsLibrary()
	{
		static $lib = null;

		if (is_null($lib)) {
			// Get necessary information
			$id = $this->getId();
			$uid = $this->getUid();
			$type = $this->getType();

			// Determine if data is valid
			if ($id && !$uid) {
				$album = ES::table('Album');
				$album->load($id);

				if (!$album->id) {
					ES::raiseError(404, JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED'));
				}

				$uid = $album->uid;
				$type = $album->type;
			}

			// Load up the albums library
			$lib = ES::albums($uid, $type, $id);
		}

		return $lib;
	}

	/**
	 * Retrieve the core albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCoreAlbums()
	{
		static $coreAlbums = null;

		if (is_null($coreAlbums)) {
			$lib = $this->getAlbumsLibrary();

			// Get a list of core albums
			$model = ES::model("Albums");
			$coreAlbums	= $model->getAlbums($lib->uid, $lib->type, array('coreAlbumsOnly' => true));
		}

		return $coreAlbums;
	}

	/**
	 * Retrieve my albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getMyAlbums()
	{
		static $myAlbums = null;

		if (is_null($myAlbums)) {
			$lib = $this->getAlbumsLibrary();
			$options = $this->getGeneralOptions();

			$myAlbums = false;

			// Only if this is view for albums in group/event
			if ($lib->showMyAlbums()) {
				// Get a list of current logged in user's album in group/event
				$options['userId'] = $this->my->id;

				// include the album limit from the user album sidebar area
				$options['limit'] = $this->config->get('photos.layout.albumlimit', 10);

				// Get a list of core albums
				$model = ES::model("Albums");
				$myAlbums = $model->getAlbums($lib->uid, $lib->type, $options);
			}
		}

		return $myAlbums;
	}

	/**
	 * Retrieve the albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAlbums()
	{
		static $albums = null;

		if (is_null($albums)) {

			$lib = $this->getAlbumsLibrary();
			$options = $this->getGeneralOptions();

			// Only if this is view for albums in group/event
			if ($lib->showMyAlbums()) {
				// Get a list of current logged in user's album in group/event
				$options['userId'] = $this->my->id;

				// We will get the other albums
				// that are not belong to the logged in user
				$options['othersAlbum'] = true;
				$options['userId'] = false;
			}

			// include the album limit from the user album sidebar area
			$options['limit'] = $this->config->get('photos.layout.albumlimit', 10);

			$model = ES::model('Albums');
			$albums = $model->getAlbums($lib->uid, $lib->type, $options);
		}

		return $albums;
	}

	/**
	 * Retrieve all the known shared options for albums query
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getGeneralOptions()
	{
		$lib = $this->getAlbumsLibrary();

		// Get a list of normal albums
		$options = array();
		$options['core'] = false;
		$options['order'] = 'assigned_date';
		$options['direction'] = 'DESC';
		$options['privacy'] = true;

		if ($lib->isClusterAlbum()) {
			$options['privacy'] = false;
		}

		return $options;
	}

	/**
	 * Retrieve the total albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalAlbums()
	{
		static $totalAlbums = null;

		if (is_null($totalAlbums)) {
			$uid = $this->getUid();
			$lib = $this->getAlbumsLibrary();

			$totalOptions = array('uid' => $uid, 'excludeCore' => true, 'type' => SOCIAL_TYPE_USER);

			if ($lib->isClusterAlbum()) {
				$totalOptions['type'] = $lib->type;

				// Retrieve album that is not belong to the current logged-in user
				$totalOptions['othersAlbum'] = true;
			}

			$model = ES::model('Albums');
			$totalAlbums = $model->getTotalAlbums($totalOptions);
		}

		return $totalAlbums;
	}

	/**
	 * Retrieve the total my albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalMyClusterAlbums()
	{
		static $totalAlbums = null;

		if (is_null($totalAlbums)) {
			$uid = $this->getUid();
			$lib = $this->getAlbumsLibrary();

			$totalAlbums = 0;

			// Only if this is view for albums in group/event
			if ($lib->showMyAlbums()) {

				$totalOptions = array('uid' => $uid,
									'excludeCore' => true,
									'type' => $lib->type,
									'userId' => $this->my->id
								);

				$model = ES::model('Albums');
				$totalAlbums = $model->getTotalAlbums($totalOptions);
			}
		}

		return $totalAlbums;
	}

	/**
	 * Get the id
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getId()
	{
		return $this->input->get('id', '', 'int');
	}

	/**
	 * Get the uid
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUid()
	{
		$utype = $this->getType();
		$uid = $this->input->get('uid', 0, 'int');

		if ($utype == SOCIAL_TYPE_USER && !$uid) {
			$uid = $this->my->id;
		}

		return $uid;
	}

	/**
	 * Get the type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->input->get('type', SOCIAL_TYPE_USER, 'string');
	}

	/**
	 * Get the layout type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLayout()
	{
		return $this->input->get('layout', 'recent', 'cmd');
	}

	/**
	 * Retrieve the unique id of the albums page
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function getUuid($id)
	{
		static $uuid = null;

		if (!isset($uuid[$id])) {
			$uuid[$id] = uniqid();
		}

		return $uuid[$id];
	}
}
