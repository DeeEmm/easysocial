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

class EasySocialViewReviews extends EasySocialSiteView
{
	/**
	 * Allow caller to delete review
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmDelete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the item object
		$id = $this->input->get('id', 0, 'int');

		$review = ES::table('Reviews');
		$review->load($id);

		$theme = ES::themes();
		$theme->set('review', $review);

		$output = $theme->output('site/reviews/dialogs/delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows caller to approve review
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmApprove()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);

		$output = $theme->output('site/reviews/dialogs/approve');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows caller to reject review
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmReject()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);

		$output = $theme->output('site/reviews/dialogs/reject');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows caller to withdraw submitted review
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function confirmWithdraw()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);

		$output = $theme->output('site/reviews/dialogs/withdraw');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders review listings
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getReviews($cluster, $reviews, $pagination, $app, $isAdmin)
	{
		$output = '';

		if ($reviews) {
			foreach ($reviews as $review) {
				$theme = ES::themes();
				$theme->set('params', $app->getParams());
				$theme->set('pagination', $pagination);
				$theme->set('app', $app);
				$theme->set('cluster', $cluster);
				$theme->set('review', $review);
				$theme->set('isAdmin', $isAdmin);

				$output .= $theme->output('site/reviews/default/items');
			}
		}

		return $this->ajax->resolve($output, empty($reviews));
	}
}
