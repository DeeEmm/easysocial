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

class EasySocialViewFeeds extends EasySocialSiteView
{
	/**
	 * Displays the creation form for rss feeds
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function create()
	{
		ES::requireLogin();		
		ES::checkToken();

		$theme = ES::themes();
		$output = $theme->output('site/feeds/dialogs/create');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after deleting a feed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Confirms if the user wants to delete the feed item
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function confirmDelete()
	{
		ES::requireLogin();		
		ES::checkToken();

		$uid = $this->input->get('uid', 0, 'int');
		$cluster = ES::cluster($uid);

		// Get the feed item
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Rss');
		$table->load($id);
	
		// Ensure that this is the owner or the group admin
		if (!$cluster->isAdmin() && $table->user_id != $this->my->id) {
			return $this->exception('APP_FEEDS_NOT_ALLOWED_TO_DELETE');
		}

		$theme = ES::themes();
		$theme->set('feed', $table);

		$output = $theme->output('site/feeds/dialogs/delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post process after saving a feed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save(SocialTableRss $table)
	{
		// Load the cluster
		$cluster = ES::cluster($table->uid);

		// Initialize the parser.
		$parser = @JFactory::getFeedParser($table->url);

		$table->parser = false;
		
		if ($parser) {
			$table->parser = $parser;
			$table->total = @$parser->get_item_quantity();
			$table->items = @$parser->get_items(0, 5);
		}
		
		$theme = ES::themes();
		$theme->set('totalDisplayed', 5);
		$theme->set('rss', $table);
		$theme->set('cluster', $cluster);

		$output = $theme->output('site/feeds/default/item');

		return $this->ajax->resolve($output);
	}
}
