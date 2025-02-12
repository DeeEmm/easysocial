<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewGiphy extends EasySocialSiteView
{
	/**
	 * Retrieve the form of the GIPHY
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getForm()
	{
		ES::requireLogin();

		// Determine whether it is coming from the story form or not
		$from = $this->input->get('from', '', 'string');

		$story = false;

		if ($from == 'story') {
			$story = true;
		}

		// Search and get the data
		$giphy = ES::giphy();

		// By default, we always show GIFs first when it is being initialized
		$gifs = $giphy->getData(false, 'gifs');

		array_pop($gifs);

		$theme = ES::themes();
		$theme->set('gifs', $gifs);
		$theme->set('story', $story);

		$html = $theme->output('site/giphy/browser/default');

		return $this->ajax->resolve($html);
	}

	/**
	 * Post process of search for giphy via query
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function search($data, $offset)
	{
		ES::requireLogin();

		$limit = (int) $this->config->get('giphy.limit');

		// The new offset
		$offset = $offset + $limit;

		$hasLoadMore = (count($data) > $limit) ? true : false;

		// The maximum of the starting position of the results for GIPHY is 4999
		if ($hasLoadMore && $offset > 4999) {
			$hasLoadMore = false;
		}

		array_pop($data);

		$theme = ES::themes();
		$theme->set('giphies', $data);

		$html = $theme->output('site/giphy/browser/list');

		return $this->ajax->resolve($html, $data, $hasLoadMore, $offset);
	}
}