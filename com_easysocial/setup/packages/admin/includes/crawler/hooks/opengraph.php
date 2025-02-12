<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerOpengraph extends SocialCrawlerAbstract
{
	public $patterns = array(
							// Example: <meta property="og:image" content="https://stackideas.com/images/easyblog_images/1257/b2ap3_thumbnail_easyblog-37-supports-joomla-3.jpg"/>
							'image'	=> 'og:image',

							// Example: <meta property="og:title" content="EasyBlog 3.7 is now Joomla 3.0 ready" />
							'title'	=> 'og:title',

							// Example: <meta property="og:description" content="EasyBlog 3.7 now works in Joomla 3.0 and comes with some new features." />
							'desc'	=> 'og:description',

							// Example: <meta property="og:type" content="article" />
							'type'	=> 'og:type',

							// Example: <meta property="og:type" content="article" />
							'url'	=>	'og:url',

							// Example: <meta property="og:video" content="http://www.youtube.com/v/T39GhB5uBGQ?version=3&amp;autohide=1">
							'video'	=> 'og:video',

							// Example: <meta property="og:video:type" content="application/x-shockwave-flash">
							'video_type' => 'og:video:type',

							// Example: <meta property="og:video:width" content="640">
							'video_width' => 'og:video:width',

							// Example: <meta property="og:video:height" content="640">
							'video_height' => 'og:video:height',

							'video_duration' => 'og:video:duration'
						);


	public function process(&$result)
	{
		$opengraph = new stdClass();
		foreach ($this->patterns as $key => $pattern) {

			// Try to find the pattern now
			$items = $this->parser->find('meta[property=' . $pattern . ']');

			if (!$items) {
				$items = $this->parser->find('meta[name=' . $pattern . ']');
			}

			foreach ($items as $meta) {

				if ($key == 'title') {
					// some title meta the html entities are wrong. e.g. &amp;quot;Trailer&amp;quot;
					// most likely due to two time of htmlentities
					// #3270
					$meta->content = str_replace('&amp;', '&', $meta->content);
				}

				$opengraph->$key = $meta->content;
				break;
			}
		}

		$result->opengraph = $opengraph;
	}
}
