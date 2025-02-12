<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewDiscussions extends EasySocialSiteView
{
	/**
	 * Post process after saving a reply
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function reply(SocialTableDiscussion $reply, $cluster)
	{
		// Load the contents
		$theme = ES::themes();

		// Since this reply is new, we don't have an answer for this item.
		$answer = false;

		$reply->author = $reply->getAuthor();

		// Get the parent discussion
		$discussion = $reply->getParent();

		// Get the cluster's access
		$access = $cluster->getAccess();
		$files = $access->get('files.enabled', true);

		$theme->set('files', $files);
		$theme->set('question', $discussion);
		$theme->set('cluster', $cluster);
		$theme->set('answer', $answer);
		$theme->set('reply', $reply);

		$contents = $theme->output('site/discussions/item/default.item');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders discussions
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getDiscussions($cluster, $discussions, $pagination, $app, $filter)
	{
		$theme = ES::themes();
		$theme->set('filter', $filter);
		$theme->set('discussions', $discussions);
		$theme->set('pagination', $pagination);
		$theme->set('cluster', $cluster);
		$theme->set('params', $app->getParams());
		$theme->set('app', $app);

		$output = $theme->output('site/discussions/default/wrapper');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the confirmation dialog to delete a discussion
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmDelete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$discussion = ES::table('Discussion');
		$discussion->load($id);

		$theme = ES::themes();
		$theme->set('discussion', $discussion);

		$output = $theme->output('site/discussions/dialogs/delete');

		return $this->ajax->resolve($output);
	}


	/**
	 * Displays the delete confirmation dialog
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function confirmDeleteReply()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$reply = ES::table('Discussion');
		$reply->load($id);

		$theme = ES::themes();
		$output = $theme->output('site/discussions/dialogs/delete.reply');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the video embed dialog form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function showVideoDialog()
	{
		$element = $this->input->get('editorName', '', 'word');
		$caretPosition = $this->input->get('caretPosition', '', 'int');

		$theme = ES::themes();
		$theme->set('element', $element);
		$theme->set('caretPosition', $caretPosition);

		$output = $theme->output('site/bbcode/dialog.video');

		return $this->ajax->resolve($output);
	}


	/**
	 * Post proccessing after deleting a reply
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteReply($discussion)
	{
		return $this->ajax->resolve($discussion->total_replies);
	}

	/**
	 * Post processing after a discussion is locked
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function lock()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after updating a reply
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function update($discussion, $reply, $cluster)
	{
		return $this->ajax->resolve($reply->getContent());
	}

	/**
	 * Post processing after a discussion is unlocked
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function unlock()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after a reply is marked as answer
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function accept()
	{
		return $this->ajax->resolve();
	}
}
