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

class EasySocialViewArticles extends EasySocialAdminView
{
	/**
	 * Displays a Joomla article browser
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/articles/dialog.browse');

		return $this->ajax->resolve($content);
	}

	/**
	 * Display the result of articles suggestion
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function suggest($result)
	{
		// If there's nothing, just return the empty object.
		if (!$result) {
			return $this->ajax->resolve(array());
		}

		$items = array();

		// Determines if we should use a specific input name
		$inputName = $this->input->get('inputName', '', 'default');

		foreach ($result as $article) {
			$theme = ES::themes();
			$theme->set('icon', 'fa-file-text');
			$theme->set('list', $article);
			$theme->set('inputName', $inputName);

			$items[] = $theme->output('site/suggest/list.item');
		}

		return $this->ajax->resolve($items);
	}
}
