<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialTagFilter extends EasySocial
{
	/**
	 * Function to get hashtags display link
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLinks($hashtags, $type, $clusterId = null, $clusterType = null)
	{
		$hashtags = explode(',', $hashtags);

		$linkOptions = array();

		if ($hashtags) {

			// Cluster link
			if ($clusterId && $clusterType) {
				$cluster = ES::cluster($clusterType, $clusterId);
				$uid = $cluster->getAlias();

				if ($uid && $clusterType) {
					$linkOptions['uid'] = $uid;
					$linkOptions['type'] = $clusterType;
				}
			}

			if (count($hashtags) == 1) {
				$linkOptions['hashtag'] = $hashtags[0];
				$tagLink = "<a href='" . ESR::$type($linkOptions) . "'>" . $hashtags[0] . "</a>";

				return JText::sprintf('COM_EASYSOCIAL_' . strtoupper($type) . '_TAGGED_WITH', $tagLink);
			} else {
				$text = JText::_('COM_EASYSOCIAL_' . strtoupper($type) . '_TAGGED_WITH_MULTIPLE');

				foreach ($hashtags as $hashtag) {
					// Re-assign hashtag properties.
					$linkOptions['hashtag'] = $hashtag;

					$link = ESR::$type($linkOptions);
					$text .= ' <a href="' . $link . '">#' . $hashtag . '</a>';
					$text .= ',';
				}

				$text = rtrim($text, ',');
			}
		}

		return $text;
	}

}
