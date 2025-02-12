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

class EasySocialViewAlbums extends EasySocialSiteView
{
	/**
	 * Displays the exceeded notice
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function exceeded(SocialAlbums $lib)
	{
		$output = $lib->getExceededHTML();

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the restricted page
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function restricted($uid = null, $type = SOCIAL_TYPE_USER)
	{
		if ($type == SOCIAL_TYPE_USER) {
			$node = ES::user($uid);
		}

		if ($type == SOCIAL_TYPE_GROUP) {
			$node = ES::group($uid);
		}

		$theme = ES::themes();

		$theme->set('showProfileHeader', false);
		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('node', $node);

		$html = $theme->output('site/albums/restricted');
		return $this->ajax->resolve($html);
	}

	/**
	 * Post process after retrieving albums
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getAlbums($albums, $photos, $pagination)
	{
		$lib = ES::albums($this->my->id, SOCIAL_TYPE_USER);

		$theme = ES::themes();

		$filter = $this->input->get('filter', 'all');

		$theme->set('lib', $lib);
		$theme->set('albums', $albums );
		$theme->set('photos', $photos );
		$theme->set('pagination', $pagination );
		$theme->set('filter', $filter);

		// Wrap it with the albums wrapper.
		$contents = $theme->output('site/albums/items/default');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the single album view
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function item()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$album = ES::table('Album');
		$album->load($id);

		// Empty id or invalid id is not allowed.
		if (!$id || !$album->id) {
			throw ES::exception('Invalid album id', 404);
		}

		// Load up the albums library
		$lib = ES::albums($album->uid, $album->type, $album->id);

		// Check if the album is viewable
		if (!$lib->viewable()) {
			return $this->restricted($lib->data->uid, $lib->data->type);
		}

		$verifyPassword = $lib->verifyPassword();
		$hasPassword = $album->hasPassword();
		$isMine = $album->isMine();

		if (($hasPassword && !$verifyPassword) && !$isMine) {
			$protected = true;
		}

		// Get the rendering options
		$options = $this->input->get('renderOptions', array(), 'array');
		$options['ordering'] = $this->config->get('photos.layout.ordering');
		$options['viewer'] = $this->my->id;
		$options['privacy'] = true;
		if (isset($protected) && $protected) {
			$options['protected'] = $protected;
		}

		// Render the album item
		$output = $lib->renderItem($options);

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders an album browser in a dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function dialog()
	{
		ES::requireLogin();

		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		// Load up the album library
		$lib = ES::albums($uid, $type);

		// Check if the current viewer can really browse items here.
		if (!$lib->allowMediaBrowser()) {
			return $this->ajax->reject();
		}

		// Browser menu
		$id = $this->input->get('id', 0, 'int');

		// Retrieve the albums now.
		$model = ES::model('Albums');
		$albums = $model->getAlbums($uid, $type, array('order' => 'assigned_date', 'direction' => 'DESC'));
		$content = '<div class="es-content-hint">' . JText::_('COM_EASYSOCIAL_ALBUMS_SELECT_ALBUM_HINT') . '</div>';
		$layout = "item";

		$theme = ES::themes();
		$theme->set('id', $id );
		$theme->set('lib', $lib);
		$theme->set('content', $content);
		$theme->set('albums', $albums);
		$theme->set('uuid', uniqid());
		$theme->set('layout', $layout);

		$html = $theme->output('site/albums/dialogs/dialog');

		return $this->ajax->resolve($html);
	}

	/**
	 * Returns album object to the caller.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlbum($album = null)
	{
		$output = $album->export(array('cover', 'photos'));

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing when creating a new album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($album = null)
	{
		// Load up the library
		$lib = ES::albums( $album->uid , $album->type , $album->id );
		$output = $lib->renderItem();

		return $this->ajax->resolve($album->export(), $output);
	}

	/**
	 * Post process after an album is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($state)
	{
		$redirect = $this->input->get('redirect', true, 'bool');

		if ($redirect) {
			$url = ESR::albums();
			return $this->ajax->redirect($url);
		}

		return $this->ajax->resolve();
	}

	/**
	 * Displays a confirmation dialog to delete an album.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);
		$output = $theme->output('site/albums/dialogs/delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post process after setting a cover photo for the album
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function setCover($photo = null)
	{
		$data = $photo->export();

		return $this->ajax->resolve($data);
	}

	/**
	 * Post processing after generating the playlist
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function playlist($photos = array())
	{
		return $this->ajax->resolve($photos);
	}

	/**
	 * Method to allow caller to load more photos in an album.
	 *
	 * @since	1.2.11
	 * @access	public
	 */
	public function loadMore($photos = array(), $nextStart = 0)
	{
		$layout = $this->input->get('layout', 'item', 'cmd');

		$options = array(
						'viewer' => $this->my->id,
						'layout' => $layout,
						'showResponse' => false,
						'showTags' => false,
						'openInPopup' => true
					);

		if ($layout == "dialog") {
			$options['showForm'] = false;
			$options['showInfo'] = false;
			$options['showStats'] = false;
			$options['showToolbar'] = false;
		}

		$output = array();

		if ($photos) {
			foreach ($photos as $photo) {
				$lib = ES::photo($photo->uid, $photo->type, $photo);
				$output[] = $lib->renderItem($options);
			}
		}
		return $this->ajax->resolve($output, $nextStart);
	}

	/**
	 * Show the rest of the albums on the user profile album sidebar area
	 *
	 * @since	2.1.10
	 * @access	public
	 */
	public function showMoreAlbums()
	{
		$totalAlbumCount = $this->input->get('totalalbums', 0, 'int');
		$startlimit = $this->input->get('startlimit', 0, 'int');
		$userAlbumOwnerId = $this->input->get('userAlbumOwnerId', 0, 'int');
		$albumType = $this->input->get('albumType', '', 'default');
		$albumId = $this->input->get('albumId', 0, 'int');
		$othersAlbum = $this->input->get('othersAlbum', false, 'bool');

		$limits = $this->config->get('photos.layout.albumlimit', 10);

		$options = array(
					'core' => false,
					'order' => 'assigned_date',
					'direction' => 'DESC',
					'privacy' => true,
					'startlimit' => $startlimit,
					'endlimit' => $limits
				);

		// Retrieve my cluster album and the total cluster albums count
		$userId = $this->input->get('userId', 0, 'int');
		$myTotalClusterAlbums = $this->input->get('myTotalClusterAlbums', 0, 'int');

		if (!$totalAlbumCount && $userId && $myTotalClusterAlbums) {
			$totalAlbumCount = $myTotalClusterAlbums;
			$options['userId'] = $userId;
		}

		// Retrieve album that is not belong to the current logged-in user
		if ($othersAlbum) {
			$options['othersAlbum'] = true;
		}

		$model = ES::model("Albums");
		$albums = $model->getAlbums($userAlbumOwnerId, $albumType, $options);

		$theme = ES::themes();
		$theme->set('albums', $albums);
		$theme->set('albumId', $albumId);

		$contents = $theme->output('site/albums/browser/sidebar');

		$nextlimit = $startlimit + $limits;

		if ($nextlimit >= $totalAlbumCount) {
			$nextlimit = '-1';
		}

		return $this->ajax->resolve($contents, $nextlimit);
	}
}
