<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerAlbums extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		// Map the alias methods here.
		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('apply', 'store');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('activate', 'toggleActivation');
		$this->registerTask('deactivate', 'toggleActivation');
	}

	/**
	 * Deletes an album from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$album = ES::table('Album');
			$album->load((int) $id);
			$state = $album->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_ALBUM_DELETED', 'album', ['album' => $album->getTitle()]);
			}

			// Deduct points from creator when his album is deleted.
			$album->assignPoints('photos.albums.delete', $album->uid);
		}

		$this->view->setMessage('COM_EASYSOCIAL_ALBUMS_ALBUM_DELETED_SUCCESSFULLY');

		return $this->view->call(__FUNCTION__);
	}
}
