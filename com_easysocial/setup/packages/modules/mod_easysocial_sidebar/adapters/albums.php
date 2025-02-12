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

require_once(__DIR__ . '/abstract.php');

class SocialSidebarAlbums extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$helper = ES::viewHelper('Albums', 'List');

		if ($helper->isViewItem()) {
			return $this->renderItem();
		}

		return $this->renderListing();
	}

	/**
	 * Render sidebar for albums page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderItem()
	{
		$helper = ES::viewHelper('Albums', 'Item');

		$id = $helper->getId();
		$layout = $helper->getLayout();
		$lib = $helper->getAlbumsLibrary();
		$coreAlbums = $helper->getCoreAlbums();

		$albums = $helper->getAlbums();
		$totalAlbums = $helper->getTotalAlbums();

		// retrieve the current logged-in user album from the cluster page
		$myAlbums = $helper->getMyAlbums();
		$myTotalClusterAlbums = $helper->getTotalMyClusterAlbums();

		// Need to get the same unique id generated from the albums view
		$uuid = $helper->getUuid($id);

		$path = $this->getTemplatePath('album_item');
		require($path);
	}

	/**
	 * Render sidebar for album listings
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderListing()
	{
		$helper = ES::viewHelper('Albums', 'List');

		$lib = $helper->getAlbumsLibrary();
		$filter = $helper->getFilter();
		$total = $helper->getTotalAlbums();
		$myTotal = $helper->getTotalMyAlbums();
		$myFavouriteTotal = $helper->getTotalMyFavouriteAlbums();

		$path = $this->getTemplatePath('albums');

		require($path);
	}
}