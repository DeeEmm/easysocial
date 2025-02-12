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
	private function checkFeature()
	{
		// Do not allow user to access photos if it's not enabled
		if (!$this->config->get('photos.enabled')) {
			$this->setMessage('COM_EASYSOCIAL_ALBUMS_PHOTOS_DISABLED', SOCIAL_MSG_ERROR);

			$this->info->set($this->getMessage());
			return $this->redirectToView('dashboard');
		}
	}

	public function display($content = '')
	{
		// Check if photos is enabled
		$this->checkFeature();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// See if are viewing another user's album (userid param determines that).
		$this->input->get('userid', null, 'int');

		// If we viewing another user's albums, load that user.
		// If not, load current logged in user.
		$user = ES::user($uid);

		$url = ESR::albums(array('userid' => $user->getAlias()), false);

		return $this->redirect($url);
	}

	/**
	 * Responsible to display the restricted page.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function restricted(SocialPhoto $lib)
	{
		$this->set('lib', $lib);

		echo parent::display('site/photos/restricted');
	}

	/**
	 * Responsible to display a nice message when a photo is already deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleted($lib)
	{
		$this->setMessage('COM_EASYSOCIAL_PHOTOS_DELETED', SOCIAL_MSG_ERROR);
		$this->info->set($this->getMessage());

		$redirect = ESR::dashboard(array(), false);
		return $this->redirect($redirect);
	}

	/**
	 * Renders the single photo item page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function item()
	{
		// Check if photos is enabled
		$this->checkFeature();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		$helper = $this->getHelper('Item');

		$uid = $helper->getUid();
		$type = $helper->getType();
		$id = $helper->getId();

		$lib = $helper->getLib();

		// Ensure that the photo truly exists
		if (!$lib->adapter) {
			ES::raiseError(404, JText::_('Invalid access'));
		}

		// Increment the hit counter
		if (in_array($type, array(SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_GROUP)) && $uid) {
			$clusters = ES::cluster($type, $uid);

			// User is trying to spoof a request by entering invalid cluster objct
			if (!$clusters->id) {
				return $this->redirectToView('dashboard');
			}

			$clusters->hit();
		}


		if ($lib->isblocked()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PHOTOS_DELETED'));
		}

		// If the photo no longer exists on the site, we should redirect to the dashboard rather than showing deleted page
		if (!$id || !$lib->data->id) {
			return $this->deleted($lib);
		}

		// Set the opengraph data for this photo
		ES::meta()->setMeta('title', ES::string()->escape($lib->data->title));
		ES::meta()->setMeta('description', ES::string()->escape($lib->data->caption));
		ES::meta()->setMeta('image', $lib->data->getSource('large'));
		ES::meta()->setMeta('url', $lib->data->getPermalink(true, true));

		// Get the album's library
		$album = $lib->album();

		// Set the page title.
		$title = $lib->getPageTitle($this->getLayout());
		ES::document()->title($title);

		// Set the breadcrumbs
		$lib->setBreadcrumbs($this->getLayout());

		// Determines if the photo is viewable or not.
		if (!$lib->viewable()) {
			return $this->restricted($lib);
		}

		// Assign a badge for the user
		$lib->data->assignBadge('photos.browse', $this->my->id);

		// Render options
		$options = array('viewer' => $this->my->id, 'size' => SOCIAL_PHOTOS_LARGE, 'showNavigation' => true);

		// We want to display the comments.
		$options['showResponse'] = true;
		$options['resizeUsingCss'] = false;
		$options['resizeMode'] = 'contain';

		// Render the photo output
		$output = $lib->renderItem($options);

		return $this->output($lib, $output);
	}

	/**
	 * Responsible to output the contents wrapped within the photo view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function output(SocialPhoto $lib, $content = '')
	{
		// Get the current photo table
		$photo = $lib->data;

		// By default we only display the single photo unless they have permissions to view the current album
		$photos = [$photo];

		// Make this configurable in the future
		$limit = $this->config->get('photos.layout.sidebarlimit');
		$total = 0;

		// Determines if the user can really view the photo's from the current album.
		if ($lib->albumViewable()) {
			$photos = $lib->getAlbumPhotos(['limit' => $limit]);
			$total = $lib->getTotalAlbumPhotos();
		}

		$current = 0;

		if ($photos && is_array($photos)) {
			$current = count($photos);
		}

		$this->set('total', $total);
		$this->set('limit', $limit);
		$this->set('id', $photo->id);
		$this->set('album', $lib->albumLib->data);
		$this->set('photos', $photos);
		$this->set('lib', $lib);
		$this->set('content', $content);
		$this->set('current', $current);
		$this->set('uuid', uniqid());

		echo parent::display('site/photos/default');
	}

	/**
	 * Displays the photo form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form()
	{
		ES::requireLogin();
		ES::checkCompleteProfile();

		// Check if photos is enabled
		$this->checkFeature();

		$id = $this->input->get('id', null, 'int');

		// Load the photo table
		$table = ES::table('Photo');
		$table->load($id);

		// Load up the library photo library
		$lib = ES::photo($table->uid, $table->type, $table);

		// If id is not given or photo does not exist
		if (!$id || !$table->id) {
			return $this->deleted($lib);
		}

		// Check if the person is allowed to edit the photo
		if (!$lib->editable()) {
			return $this->restricted($lib);
		}

		// Set the page title.
		$title = $lib->getPageTitle($this->getLayout());
		ES::document()->title($title);

		// Set the breadcrumbs
		$lib->setBreadcrumbs($this->getLayout());

		$options = [
			'size' => 'large',
			'showForm' => true,
			'layout' => 'form'
		];
		$output = $lib->renderItem($options);

		return $this->output($lib, $output);
	}

	/**
	 * Allows use to download a photo from the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function download()
	{
		// Check if photos is enabled
		$this->checkFeature();

		// Load up info object
		$info = ES::info();

		// Get the id of the photo
		$id = $this->input->get('id', null, 'int');

		$photo = ES::table('Photo');
		$photo->load($id);

		// Id provided must be valid
		if (!$id || !$photo->id) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED'), SOCIAL_MSG_ERROR);
			$info->set($this->getMessage());

			return $this->redirect(ESR::albums(array(), false));
		}

		// Load up photo library
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		if (!$lib->downloadable()) {
			return $this->restricted($lib);
		}

		$photo->download();
	}
}
