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
	 * Post process after saving page review
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveReview($permalink)
	{
		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();

		// If there's an error, redirect them back to the form
		if ($this->hasErrors()) {

			$this->info->set($this->getMessage());

			$returnUrl = $this->getReturnUrl($permalink);

			return $this->redirect($returnUrl);
		}

		// Set message
		$this->info->set($this->getMessage());

		$this->redirect($permalink);
	}

	/**
	 * Post process after a reviews item is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete($permalink)
	{
		$this->info->set($this->getMessage());

		return $this->redirect($permalink);
	}

	/**
	 * Post process after a reviews item is approved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function approve($permalink)
	{
		$this->info->set($this->getMessage());

		return $this->redirect($permalink);
	}

	/**
	 * Post process after a reviews item is approved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function reject($permalink)
	{
		$this->info->set($this->getMessage());

		return $this->redirect($permalink);
	}

	/**
	 * Post process after a reviews item is withdrew
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function withdraw($permalink)
	{
		$this->info->set($this->getMessage());

		return $this->redirect($permalink);
	}
}
