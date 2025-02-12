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

ES::import('admin:/tables/table');
ES::import('admin:/includes/stream/dependencies');

class SocialTableShare extends SocialTable implements ISocialStreamItemTable
{
	public $id = null;
	public $uid = null;
	public $element = null;
	public $user_id = null;
	public $content = null;
	public $created = null;
	public $share_as = null;
	public $params = null;

	static $_shares = array();

	public function __construct(& $db)
	{
		parent::__construct('#__social_shares' , 'id' , $db);
	}

	public function setSharesBatch($data)
	{
		$model = ES::model('Stream');
		$uids = array();

		foreach ($data as $item) {
			// Get related items
			$related = $model->getBatchRalatedItem($item->id);
			if (!$related) {
				continue;
			}

			// Get the item's element.
			$element = $item->context_type;

			// Get the stream item
			$streamItem = $related[ 0 ];

			$key = $streamItem->context_id;

			// If it hasn't been set yet, we need to initialize the array
			if (!isset(self::$_shares[ $key ])) {
				// We skip this if context_id isn't set.
				if (!$streamItem->context_id) {
					continue;
				}

				$uids[] = $streamItem->context_id;

				self::$_shares[ $key ] = array();
			}
		}


		if ($uids) {
			$db = ES::db();
			$sql = $db->sql();

			$ids = implode(',' , $uids);

			$query = 'select * from `#__social_shares` where id IN (' . $ids . ')';
			$sql->raw($query);

			$db->setQuery($sql);
			$result = $db->loadObjectList();

			if ($result) {
				foreach ($result as $row) {
					$new = ES::table('Share');
					$new->bind($row);

					self::$_shares[ $row->id ] = $new;
				}
			}

		}

	}

	public function load($id = null , $reset = true)
	{
		if (is_array($id)) {
			return parent::load($id, $reset);
		}

		if (! isset(self::$_shares[ $id ])) {
			parent::load($id);
			self::$_shares[ $id ] = $this;
		} else {
			$this->bind(self::$_shares[ $id ]);
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		$isNew = true;

		if ($this->id) {
			$isNew = false;
		}

		if (empty($this->created)) {
			$this->created = ES::date()->toMySQL();
		}

		$state = parent::store($updateNulls);

		if ($state) {
			// TODO: do any triggering here.
		}

		return $state;
	}

	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if ($state) {
			// TODO: do any triggering here.
		}

		return $state;
	}

	public function toJSON()
	{
		return array('id' => $this->id ,
					 'uid' => $this->uid ,
					 'element' => $this->element,
					 'user_id' => $this->user_id,
					 'content' => $this->content,
					 'created' => $this->created
		);
	}

	public function addStream($verb)
	{
		// moved to controller.
	}

	public function removeStream()
	{
		$stream	= ES::stream();
		$stream->delete($this->id, SOCIAL_TYPE_SHARE);
	}

	/**
	 * Since elements are stored in element.group, we need to extract the element correctly
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getElement()
	{
		list($element, $group) = explode('.', $this->element);
		return $element;
	}

	/**
	 * Exports share stream data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer)
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$source = explode('.', $this->element);
		$element = $source[0];

		$data = false;

		$photo = null;
		$album = null;

		if ($element == 'photos') {

			$photo = ES::table('Photo');
			$photo->load($this->uid);

			$album = $photo->getAlbum();
		}

		if ($element == 'albums') {

			$photo = ES::table('Photo');

			$album = ES::table('Album');
			$album->load($this->uid);

			if ($album->cover_id) {
				$photo->load($album->cover_id);
			}

			// photo not availaible
			if (!$photo->id || !$photo->state) {

				if ($album->hasPhotos()) {

					$options = array('limit' => 1);

					// cover albums
					if ($album->core == 2) {
						$options = array('nocover' => false);
					}

					$result = $album->getPhotos($options);
					$photo = $result['photos'][0];
				}
			}
		}


		switch ($element) {
			case 'stream' :

				$options = array(
					'defaultEvent' => 'onPrepareRestStream'
				);

				$stream = ES::stream();
				$results = $stream->getItem($this->uid, '', '', false, array(), $options);

				if ($results === true || !$results) {
					$data = new StdClass();
					$data->restricted = true;

					$table = ES::table('stream');
					$table->load($this->uid);

					$data->actor = ES::user($table->actor_id)->toExportData($viewer);

					// do not process further.
					$cache[$key] = $data;
					return $cache[$key];
				}

				$streamItem = $results[0];
				$data = $streamItem->toExportData($viewer);

				$data->likes = false;
				$data->comments = false;

				$data->isFavourite = false;
				$data->isPin = false;

				// need this flag for stream hide feature
				$data->isHidden = false;

				break;
			case 'photos' :
			case 'albums' :

				$my = ES::user();
				$privacy = $my->getPrivacy();

				if (!$privacy->validate('albums.view', $album->id, SOCIAL_TYPE_ALBUM, $album->user_id) || !$privacy->validate('photos.view', $photo->id, SOCIAL_TYPE_PHOTO, $photo->user_id)) {

					$data = new StdClass();
					$data->restricted = true;

					// get minimal set of data to fullfull the stream item in mobile.
					$data->album = new stdClass();
					$data->actor = $photo->getCreator()->toExportData($viewer);
					$data->album->author = $album->getCreator()->toExportData($viewer);

					// do not process further.
					$cache[$key] = $data;
					return $cache[$key];
				}

				$photoObject = $photo->toExportData($viewer);

				$photoObject->width = $photo->getWidth();
				$photoObject->height = $photo->getHeight();

				$orientation = 'landscape';

				if ($photoObject->width < $photoObject->height) {
					$orientation = 'portrait';
				}

				$photoObject->orientation = $orientation;
				$photoObject->extension = $photo->getExtension('large');

				$photoMessage = '';

				$params = ES::registry($this->params);
				$streamId = $params->get('streamId', '');

				if ($streamId) {
					$tbl = ES::table('stream');
					$tbl->load($streamId);

					$photoMessage = $tbl->content;
				}

				$items = array($photoObject);

				$source = new stdClass();
				$source->total = 1;
				$source->previewTotal = 1;
				$source->remainingPhotoCount = 0;
				$source->album = $album->toExportData($viewer);
				$source->previewItems = $items;
				$source->playlistIds = array($photo->id);
				$source->photoMessage = $photoMessage;

				$data = $source;

				break;

			default:
				break;
		}

		$cache[$key] = $data;

		return $cache[$key];
	}
}
