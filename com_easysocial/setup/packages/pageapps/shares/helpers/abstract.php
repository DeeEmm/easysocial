<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/themes/themes');

class SocialPageSharesHelper extends EasySocial
{
	protected $item = null;
	protected $share = null;

	public function __construct(SocialStreamItem &$item, $share)
	{
		parent::__construct();

		$this->item = $item;
		$this->share = $share;
	}

	public function formatContent($content)
	{
		if (empty($this->item->tags)) {
			return $content;
		}

		$string = ES::string();
		$content = $string->processTags($this->item->tags, $content);

		return $content;
	}

	/**
	 * Renders the standard restricted view
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function restricted()
	{
		$theme = ES::themes();
		$output = $theme->output('themes:/site/streams/restricted');

		return $output;
	}
}
