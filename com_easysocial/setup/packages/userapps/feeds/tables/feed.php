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

ES::import('admin:/tables/table');

class FeedsTableFeed extends SocialTable
{
	public $id = null;
	public $user_id	= null;
	public $title = null;
	public $description = null;
	public $url = null;
	public $state = null;
	public $created = null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_feeds' , 'id' , $db );
	}

	/**
	 * Overrides the delete function
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete( $pk = null )
	{
		$state = parent::delete();

		// Delete any items that are related to this stream
		$stream = ES::stream();
		$stream->delete($this->id,'feeds');

		return $state;
	}

	/**
	 * Creates a new stream for the feed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createStream($verb)
	{
		// Add activity logging when a friend connection has been made.
		// Activity logging.
		$stream				= ES::stream();
		$streamTemplate		= $stream->getTemplate();

		// Set the actor.
		$streamTemplate->setActor( $this->user_id , SOCIAL_TYPE_USER );

		// Set the context.
		$streamTemplate->setContext( $this->id , 'feeds' );

		// Set the verb.
		$streamTemplate->setVerb( $verb );

		// Set the public stream
		$streamTemplate->setAccess( 'core.view' );

		// Set the params to offload the loading
		$streamTemplate->setParams( $this );

		// Create the stream data.
		$stream->add( $streamTemplate );
	}

	/**
	 * Retrieves the feeds app record
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getApp()
	{
		static $app;

		if (empty($app)) {
			$app = ES::table('app');
			$app->load(array('type' => SOCIAL_TYPE_APPS, 'group' => SOCIAL_APPS_GROUP_USER, 'element' => 'feeds'));
		}

		return $app;
	}

	/**
	 * Initializes the parser
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getParser()
	{
		static $parsers	= array();

		if (!isset($parsers[$this->id])) {

			$rssDoc = new JFeedFactory();
			$feed = $rssDoc->getFeed($this->url);

			$parsers[$this->id] = $feed;
		}

		return $parsers[$this->id];
	}

	/**
	 * Shorthand to get the permalink of this note.
	 *
	 * @since  2.1.0
	 * @access public
	 */
	public function getPermalink($external = false, $xhtml = true, $sef = true, $adminSef = false)
	{
		$app = $this->getApp();
		$options = array('cid' => $this->id, 'uid' => ES::user($this->user_id)->getAlias(), 'type' => SOCIAL_TYPE_USER, 'external' => $external, 'sef' => $sef, 'adminSef' => $adminSef);

		return $app->getCanvasUrl($options, $xhtml);
	}
}
