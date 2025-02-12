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

ES::import('site:/views/views');

class EasySocialViewStream extends EasySocialSiteView
{
	/**
	 * Post processing after deleting a custom filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function deleteFilter()
	{
		$this->info->set($this->getMessage());

		$redirect = ESR::dashboard(array(), false);

		return $this->redirect($url);
	}

	/**
	 * Renders the single stream item layout
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function item()
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Get the stream id from the request
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID'));
		}

		// Load the stream table data first
		$streamTable = ES::table('Stream');
		$loadState = $streamTable->load($id);

		// If we are unable to find the record, just throw an error
		if (!$loadState) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID'));
		}

		// check if user as the acccess to view cluster invite only stream item
		if ($streamTable->cluster_id && $streamTable->cluster_type) {
			$cluster = $streamTable->getCluster();

			if ($cluster->isInviteOnly() && !$cluster->canViewItem()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID'));
			}
		}

		// check if actor is already blocked by admin.
		$streamActor = ES::user($streamTable->actor_id);

		if (!ES::isSiteAdmin() && $streamActor->isBlock()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID'));
		}

		// Retrieve stream
		$lib = ES::stream();
		$stream = $lib->getItem($id, $streamTable->cluster_id, $streamTable->cluster_type, false, array('perspective' => 'dashboard'));

		// This could be due to permission issue if the stream item belongs to a closed or invite only group / event
		if ($stream === false) {

			if ($streamTable->cluster_id && $streamTable->cluster_type) {
				$template = 'site/stream/restricted';
				$this->set('cluster', $streamTable->getCluster());
				$this->set('streamTable', $streamTable);
				parent::display($template);
				return;
			} else {
				// stream from user group.
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_STREAM_CONTENT_NOT_AVAILABLE'));
			}
		}

		// If the user is not allowed to view this stream, display the appropriate message
		if ($stream === true || (is_array($stream) && count($stream) <= 0)) {
			$type = $streamTable->cluster_type ? $streamTable->cluster_type : SOCIAL_TYPE_USER;
			$cluster = false;

			if ($streamTable->cluster_type) {
				$cluster = $streamTable->getCluster();
			} else {
				$cluster = ES::user($streamTable->actor_id);
			}

			$template = 'site/stream/restricted';
			$this->set('cluster', $cluster);
			$this->set('streamTable', $streamTable);
			parent::display($template);

			return;
		}

		$metaObj = new stdClass();

		// Get the first stream item
		$stream = $stream[0];


		// Strip off any html tags from the title
		$title = html_entity_decode($stream->title);
		$title = strip_tags($title);
		$title = trim($title);

		// Set the page attributes
		$this->page->title($title);

		$image = $lib->getContentImage($stream);

		if ($streamTable->cluster_type) {
			$cluster = $streamTable->getCluster();

			// Increment the hit counter
			$cluster->hit();
		}

		// if this is group/page/event's create/update stream, we use their cover as the og:image
		if ($streamTable->cluster_type == 'group' || $streamTable->cluster_type == 'page' || $streamTable->cluster_type == 'event') {
			if ($streamTable->verb == 'create' || $streamTable->verb == 'update') {
				$image->url = $cluster->getCover();
			}
		}

		if (!$image->url) {
			// Try to get user avatar image as an alternative.
			$image->url = ES::user($stream->actor->id)->getAvatar(SOCIAL_AVATAR_LARGE);
		}

		if ($image->url) {
			$metaObj->image = $image;
		}

		// Get the permalink of this stream
		$permalink = ESR::stream(array('id' => $stream->uid, 'layout' => 'item', 'external' => 1));

		// Append additional meta details
		$metaObj->url = $permalink;
		$metaObj->type = 'article';
		$metaObj->title = $title;

		// if that is group stream, only set the group title without user name
		if ($streamTable->cluster_type == 'group' || $streamTable->cluster_type == 'page') {
			$metaObj->title = $cluster->getTitle();
		}

		// if that is link type stream, show the link title
		if ($streamTable->context_type == 'links') {

			// Get the assets associated with this stream
			$assets = $stream->getAssets();
			$assets = $assets[0];

			// Load the link object
			$link = ES::table('Link');
			$link->loadByLink($assets->get('link'));

			// Get the oembed object
			$oembed = $link->getOembed();

			// link title
			$metaObj->title = $assets->get('title');

			if (!$oembed && $this->config->get('links.cache.data')) {

				// Get the link data
				$linkObj = json_decode($link->data);

				if (isset($linkObj->title) && $linkObj->title) {
					$metaObj->title = $linkObj->title;
				}
			}
		}

		// render the meta tags here.
		$this->meta->setMetaObj($metaObj);

		// Get stream actions
		$actions = '';

		// Determines if we should display actions
		if ($stream->display == SOCIAL_STREAM_DISPLAY_FULL) {
			$actions = $lib->getActions($stream);
		}

		// Determines if we should display the translations.
		$language = $this->my->getLanguage();
		$siteLanguage = JFactory::getLanguage();
		$showTranslations = false;

		if (($language != $siteLanguage->getTag()) || $this->config->get('stream.translations.explicit')) {
			$showTranslations = true;
		}

		// Set timeline as default active filter
		$active = 'timeline';

		// Get the stream author object (group/event/page/user)
		$object = $stream->getActor();

		// Display cluster header if this stream is belong to cluster
		if ($streamTable->cluster_id) {
			$object = ES::cluster($streamTable->cluster_type, $streamTable->cluster_id);

			$clusterApps = array('feeds', 'news', 'discussions', 'polls');

			// Set apps as active state
			if (in_array($streamTable->context_type, $clusterApps)) {
				$active = 'apps';
			}
		}

		$streamDate = $stream->lapsed;

		$view = $this->input->get('view', '', 'cmd');

		$this->set('active', $active);
		$this->set('streamDate', $streamDate);
		$this->set('object', $object);
		$this->set('showTranslations', $showTranslations);
		$this->set('actions', $actions);
		$this->set('stream', $stream);
		$this->set('view', $view);

		return parent::display('site/stream/item/default');
	}

	/**
	 * Post processing after saving a new filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function saveFilter($filter, $error = null)
	{
		$this->info->set($this->getMessage());

		// Construct the redirect url now
		if ($filter->utype == SOCIAL_TYPE_USER) {

			// Return to dashboard directly
			if ($error) {
				return $this->redirect(ESR::dashboard());
			}

			return $this->redirect(ESR::dashboard(array('type' => 'filter', 'filterid' => $filter->getAlias()), false));
		}

		$cluster = ES::cluster($filter->utype, $filter->uid);
		$redirect = $cluster->getPermalink(false);

		return $this->redirect($redirect);
	}


	public function form()
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();

		$my 	= ES::user();
		$id 	= $this->input->get('id', 0, 'int');

		$filter = ES::table( 'StreamFilter' );
		$filter->load( $id );

		$model = ES::model( 'Stream' );
		$items = $model->getFilters( $my->id );

		$this->set( 'filter', $filter );
		$this->set( 'items', $items );


		// Set page title
		if( $filter->id )
		{
			ES::document()->title( JText::sprintf( 'COM_EASYSOCIAL_STREAM_FILTER_EDIT_FILTER', $filter->title ) );
		}
		else
		{
			ES::document()->title( JText::_( 'COM_EASYSOCIAL_STREAM_FILTER_CREATE_NEW_FILTER' ) );
		}

		// Set the page breadcrumb
		ES::document()->breadcrumb( JText::_( 'COM_EASYSOCIAL_PAGE_TITLE_DASHBOARD' ) , FRoute::dashboard() );
		ES::document()->breadcrumb( JText::_( 'Filter' ) );


		echo parent::display( 'site/stream/filter.form' );
	}

	/**
	 * Post processing after load more in stream is clicked for guests
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function loadmoreGuest($stream)
	{
		$content = $stream->html(true);
		$startlimit = $stream->getNextStartLimit();

		if (empty($startlimit)) {
			$startlimit = '';
		}

		$data = new stdClass();
		$data->contents = $content;
		$data->nextlimit = $startlimit;

		echo json_encode($data);exit;
	}

	/**
	 * Post processing after load more.
	 * We are using "html" type output because we need to render modules
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function loadmore(SocialStream $stream)
	{
		// Get the content from the stream
		$content = $stream->html(true);

		$startlimit = $stream->getNextStartLimit();
		$startdatelimit = $stream->getNextStartDateLimit();

		if (empty($startlimit)) {
			$startlimit = '';
		}

		$data = new stdClass();
		$data->contents = $content;
		$data->nextlimit = $startlimit;
		$data->nextdatelimit = $startdatelimit;

		echo json_encode($data);exit;
	}
}
