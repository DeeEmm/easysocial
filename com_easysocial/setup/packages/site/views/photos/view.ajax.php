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

class EasySocialViewPhotos extends EasySocialSiteView
{
	/**
	 * Post processing after creating an avatar from a photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createAvatar($photo = null)
	{
		$userObj = new stdClass();
		$userObj->avatars = new stdClass();

		// Initialize values
		$userObj->avatars->small = $this->my->getAvatar(SOCIAL_AVATAR_SMALL);
		$userObj->avatars->medium = $this->my->getAvatar(SOCIAL_AVATAR_MEDIUM);
		$userObj->avatars->large = $this->my->getAvatar(SOCIAL_AVATAR_LARGE);
		$userObj->avatars->square = $this->my->getAvatar(SOCIAL_AVATAR_SQUARE);

		$photoObj = (object) $photo;

		return $this->ajax->resolve($photoObj, $userObj, $this->my->getPermalink(false));
	}

	/**
	 * This method is used for rendering the rest of the photos
	 * on photo item page in the sidebar
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function loadSidebarPhotos()
	{
		$id = $this->input->get('albumId', 0, 'int');
		$current = $this->input->get('current', 0, 'int');

		$table = ES::table('Album');
		$table->load($id);

		if (!$table->id || !$table->viewable()) {
			return $this->ajax->reject();
		}

		$album = ES::albums($table->uid, $table->type, $table);
		$data = $album->getPhotos($table->id, array('start' => $current));
		$photos = $data['photos'];

		if ($photos && is_array($photos)) {
			$current = $current + count($photos);
		}

		$theme = ES::themes();
		$theme->set('photos', $photos);
		$theme->set('id', null);

		$contents = $theme->output('site/photos/sidebar.photos');

		return $this->ajax->resolve($contents, $current);
	}

	/**
	 * Renders a single photo item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function item()
	{
		// Get photo object
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Photo');
		$table->load($id);

		// If id is not given or photo does not exist
		if (!$id || !$table->id) {
			throw ES::exception('Invalid photo', 500);
		}

		// Load up photo library
		$lib = ES::photo($table->uid, $table->type, $table->id);

		// Check if the album is viewable
		if (!$lib->viewable()) {
			return $this->restricted($lib);
		}

		// Assign a badge for the user
		$lib->data->assignBadge('photos.browse' , $this->my->id);

		// Render options
		$options = array(
			'viewer' => $this->my->id,
			'size' => SOCIAL_PHOTOS_LARGE,
			'showNavigation' => true,
			'showResponse' => true,
			'resizeUsingCss' => false,
			'resizeMode' => 'contain'
		);

		// Determines if the user is allowed to comment on the photo or not
		$comments = $lib->comments();
		$likes = $lib->likes();

		if (!$comments->canComment() && !$likes->canReact()) {
			$options['showResponse'] = false;
		}

		$popup = $this->input->get('popup', false, 'bool');

		if ($popup) {
			$options['template'] = 'site/photos/popup/item';
		}

		// Render the photo output
		$output = $lib->renderItem($options);

		// Wrap the content in a photo browser if required
		$browser = $this->input->get('browser', null, 'int');

		if ($browser) {
			$output	= $this->renderBrowser($output);
		}

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the html wrapper for photos
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function renderBrowser($content = '')
	{
		// Get current photo
		$id = $this->input->get('id' , null, 'int');
		$photo = ES::table('photo');
		$photo->load($id);

		// Load up photo's library
		$lib = ES::photo( $photo->uid , $photo->type , $photo );

		// If the photo id is invalid, throw deleted
		if (!$id || !$photo->id) {
			throw ES::exception('Invalid photo', 500);
		}

		// Test if the user can view the entire album
		$photos = array($photo);

		if ($lib->albumViewable()) {
			$photos = $lib->getAlbumPhotos(array('limit' => 2048));
		}

		// Generate photo browser template
		$theme = ES::themes();
		$theme->set('id', $photo->id);
		$theme->set('album', $lib->albumLib->data);
		$theme->set('photos', $photos);
		$theme->set('lib', $lib);
		$theme->set('heading', false);
		$theme->set('content', $content);
		$theme->set('uuid', uniqid());

		return $theme->output('site/photos/default');
	}

	/**
	 * Responsible to display the restricted area
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function restricted(SocialPhoto $lib)
	{
		$theme = ES::themes();
		$theme->set('lib', $lib);

		$output = $theme->output('site/photos/restricted');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post process after a photo has been featured
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function feature($isFeatured = false)
	{
		$message = 'COM_EASYSOCIAL_PHOTOS_PHOTO_UNFEATURED_SUCCESS';

		if ($isFeatured) {
			$message = 'COM_EASYSOCIAL_PHOTOS_PHOTO_FEATURED_SUCCESS';
		}

		$this->setMessage($message);

		return $this->ajax->resolve($this->getMessage(), $isFeatured);
	}

	/**
	 * Confirm deletion of photo
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$ids = $this->input->get('ids', array(), 'array');
		$message = 'COM_EASYSOCIAL_PHOTO_DELETE_CONFIRMATION';

		if ($ids && count($ids) > 1) {
			$message = 'COM_ES_PHOTOS_DELETE_CONFIRMATION';
		}

		$theme = ES::themes();
		$theme->set('message', $message);

		$html = $theme->output('site/photos/dialogs/delete');

		return $this->ajax->resolve($html);
	}

	public function confirmMove()
	{
		$ids = $this->input->get('ids', 0, 'int');
		$albumId = $this->input->get('albumId', 0, 'int');
		$uid = $this->input->get('uid', '', 'default');
		$type = $this->input->get('type', '', 'default');

		// If photo id is invalid, reject this.
		if (!$ids) {
			return $this->ajax->reject();
		}

		// Get albums
		$model = ES::model('Albums');
		$albums = $model->getAlbums($uid, $type, array('exclusion' => $albumId, 'core' => false));

		// Get dialog
		$theme = ES::themes();

		if (!$albums || empty($albums)) {
			$html = $theme->output('site/photos/dialogs/empty');
			return $this->ajax->resolve($html);
		}

		$theme->set('albums', $albums);
		$html = $theme->output('site/photos/dialogs/move');

		return $this->ajax->resolve($html);
	}

	/**
	 * Post processing after a tag is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteTag()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after a photo is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($newCover = false)
	{
		if ($newCover) {
			$this->ajax->setCover($newCover->export());
		}

		$message = JText::_('COM_ES_PHOTO_DELETED_SUCCESS');

		return $this->ajax->resolve($message, SOCIAL_MSG_SUCCESS);
	}

	/**
	 * Post processing after photo is moved
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function move()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the move to album dialog
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function moveToAnotherAlbum()
	{
		$id = $this->input->get('id', 0, 'int');

		$photo = ES::table('Photo');
		$photo->load($id);

		// If photo id is invalid, reject this.
		if (!$photo->id || !$id) {
			return $this->ajax->reject();
		}

		// Load up the photo lib
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		// Check if the user is really allowed to move the photo
		if (!$lib->canMovePhoto()) {
			return $this->ajax->reject();
		}

		// Get albums
		$model = ES::model('Albums');
		$albums	= $model->getAlbums($photo->uid, $photo->type, array('exclusion' => $photo->album_id, 'core' => false));

		// Get dialog
		$theme = ES::themes();

		if (!$albums || empty($albums)) {
			$html = $theme->output('site/photos/dialogs/empty');
			return $this->ajax->resolve($html);
		}

		$theme->set('albums', $albums);
		$theme->set('photo', $photo);
		$html = $theme->output('site/photos/dialogs/move');

		return $this->ajax->resolve($html);
	}

	/**
	 * Returns a list of tags for a particular photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTags($tags)
	{
		return $this->ajax->resolve($tags);
	}

	/**
	 * Processes after storing a tag object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createTag($tag, $photo)
	{
		$tags = $photo->getTags();
		$comma = (count($tags) > 1 ) ? true : false;

		$theme = ES::themes();
		$theme->set('tag'  , $tag);
		$theme->set('photo', $photo);
		$theme->set('comma', $comma);
		$tagItem = $theme->output('site/photos/tags.item');
		$tagListItem = $theme->output('site/photos/taglist.item');
		$infoTagListItem = $theme->output('site/photos/info.taglist.item');

		return $this->ajax->resolve($tag, $tagItem, $tagListItem, $infoTagListItem);
	}

	/**
	 * If there are only a single photo in the popup, this function needs to be executed so that proper display
	 * is given to the user
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function processDeleteLastPhoto()
	{
		$this->info->set(false, 'COM_ES_PHOTO_DELETED_SUCCESS', 'success');

		$url = ESR::dashboard();
		return $this->ajax->resolve($url);
	}

	/**
	 * Post processing after a photo is rotated.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function rotate($photo, $paths)
	{
		$result = $photo->getTags();
		$tags = array();

		if (!$result) {
			return $this->ajax->resolve($photo->export(), $tags);
		}

		foreach ($result as $row) {
			$obj = new stdClass();
			$obj->id = $row->id;
			$obj->width = $row->width;
			$obj->height = $row->height;
			$obj->left = $row->left;
			$obj->top = $row->top;

			$tags[]	= $obj;
		}

		return $this->ajax->resolve($photo->export(), $tags);
	}

	/**
	 * Post process after a photo is saved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function update($photo)
	{
		$data = $photo->export();
		$user = ES::user($photo->uid);

		$theme = ES::themes();
		$theme->set('userAlias', $user->getAlias());
		$theme->set('photo', $photo);

		$info = $theme->output('site/photos/info');

		return $this->ajax->resolve($data, $info);
	}
}
