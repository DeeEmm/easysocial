<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewPhotosItemHelper extends EasySocial
{
	/**
	 * Retrieve the uid of the photo
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUid()
	{
		$uid = $this->input->get('uid', null, 'int');

		return $uid;
	}

	/**
	 * Retrieve the id of the photo
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getId()
	{
		$id = $this->input->get('id', null, 'int');

		return $id;
	}

	/**
	 * Retrieve the type of the photo
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getType()
	{
		$type = $this->input->get('type', '', 'word');

		return $type;
	}

	/**
	 * Retrieve the library for the photo
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLib()
	{
		static $lib = null;

		if (is_null($lib)) {
			$uid = $this->getUid();
			$id = $this->getId();
			$type = $this->getType();

			$lib = ES::photo($uid, $type, $id);
		}

		return $lib;
	}

	/**
	 * Method to retrieve photos from the albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPhotos()
	{
		static $photos = null;

		if (is_null($photos)) {
			$lib = $this->getLib();
			$limit = $this->getLimit();

			if (!$lib->adapter) {
				return [];
			}

			$photos = [$lib->data];

			if ($lib->albumViewable()) {
				$photos = $lib->getAlbumPhotos(['limit' => $limit]);
			}
		}

		return $photos;
	}

	/**
	 * Method to retrieve total photos in the albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotal()
	{
		static $total = null;

		if (is_null($total)) {
			$lib = $this->getLib();

			$total = 0;

			if (!$lib->adapter) {
				return $total;
			}

			if ($lib->albumViewable()) {
				$total = $lib->getTotalAlbumPhotos();
			}
		}

		return $total;
	}

	/**
	 * Method to retrieve the album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAlbum()
	{
		static $album = null;

		if (is_null($album)) {
			$lib = $this->getLib();

			$album = $lib->albumLib->data;
		}

		return $album;
	}

	/**
	 * Method to retrieve the limit for the number of photos in the album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLimit()
	{
		return $this->config->get('photos.layout.sidebarlimit');
	}
}
