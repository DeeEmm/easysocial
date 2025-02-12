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

class SocialBBCodeMetaCafe
{
	private function getCode($url)
	{
		preg_match('/\/watch\/(.*)(?=(\/).)/i', $url, $matches);

		if (!empty($matches)) {
			return $matches[1];
		}

		return false;
	}

	/**
     * Retrieves the embed widget for metacafe.com videos
     *
     * @since   2.1
     * @access  public
     */
	public function getEmbedHTML($url)
	{
		$code = $this->getCode($url);

		if ($code) {
			$src = str_ireplace('/watch/', '/embed/', $url);
			return '<div class="es-video es-video--16by9"><iframe title="" width="720" height="405" src="' . $src . '" frameborder="0" allowfullscreen></iframe></div>';
		}

		return false;
	}
}
