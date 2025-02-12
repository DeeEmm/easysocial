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

ES::import('site:/views/views');

class EasySocialViewComments extends EasySocialSiteView
{
	/**
	 * Post process after comment is stored
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save($comment = null)
	{
		if ($comment->getError()) {
			return $this->ajax->reject($comment->getError());
		}

		$output = $comment->renderHTML();

		return $this->ajax->resolve($output, $comment->parent);
	}

	public function update($comment = null, $giphyInValid = false)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$output = $comment->renderHTML();

		return $this->ajax->resolve($output, $giphyInValid);
	}

	/**
	 * Renders a list of comments
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function load($comments = null, $parentCount = 0)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$output = array();

		if (!$parentCount) {
			$parentCount = count($comments);
		}

		foreach ($comments as $comment) {

			if (!$comment instanceof SocialTableComments) {
				continue;
			}

			$output[] = $comment->renderHTML();
		}

		return $this->ajax->resolve($output, $parentCount);
	}

	/**
	 * Confirmation to delete a comment attachment item
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmDeleteCommentAttachment()
	{
		$theme = ES::themes();
		$contents = $theme->output('site/comments/dialogs/delete.attachment');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after a comment attachment is deleted
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function deleteAttachment()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after deleting a comment
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function delete($isParent)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve($isParent);
	}

	/**
	 * Notifies the caller if there are any updates on the comments
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUpdates($data = null)
	{
		return $this->ajax->resolve($data);
	}

	/**
	 * Renders a dialog for delete confirmation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();

		$contents = $theme->output('site/comments/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the edit comment form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function edit($comment)
	{
		$overlay = $comment->getOverlay();

		// Get attachments associated with this comment
		$model = ES::model('Files');
		$attachments = $model->getFiles($comment->id, SOCIAL_TYPE_COMMENTS);

		$attachmentEnabled = $this->config->get('comments.attachments.enabled', false);
		$attachmentEnabled = $attachmentEnabled ? true : false; // to avoid ajax return 0 or 1 as string.

		$theme = ES::themes();
		$theme->set('comment', $comment->comment);
		$theme->set('overlay', $overlay);
		$theme->set('hasGiphy', $comment->hasGiphy());
		$theme->set('attachments', $attachments);
		$theme->set('isEdit', true);

		$contents = $theme->output('site/comments/form');

		return $this->ajax->resolve($contents, $attachments, $attachmentEnabled);
	}
}
