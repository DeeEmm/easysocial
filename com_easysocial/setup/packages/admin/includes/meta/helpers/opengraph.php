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

class OpengraphRenderer
{
	public static function add($ogTag, $content)
	{
		// To prevent duplication
		static $tags = array();

		$doc = JFactory::getDocument();

		// Only add these tags on html type view
		// Only add if previously not added to prevent duplication of the same tags
		if (!isset($tags[$ogTag]) && $doc->getType() == 'html') {
			$tags[$ogTag] = $content;
			$doc->addCustomTag('<meta property="' . $ogTag . '" content="' . $content . '" />');
		}
	}

	public static function type($type)
	{
		self::add('og:type', $type);
	}

	public static function title($title)
	{
		self::add('og:title', ES::string()->escape($title));
	}

	public static function description($content)
	{
		self::add('og:description', ES::string()->escape($content));
	}

	public static function video($videos)
	{
		if (!$videos || empty($videos)) {
			return;
		}

		foreach ($videos as $video) {

			// if the video come from soundcloud we need to skip this video tag because Facebook not support this
			// https://soundcloud.com/bigsean-1/bounce-back

			if (strpos($video->url, 'https://soundcloud.com/') !== false) {
				return;
			}

			self::add('og:video:url', $video->url);

			if (strpos($video->url, 'https://www.youtube.com/watch?v=') !== false) {
				$embedUrl = str_replace('https://www.youtube.com/watch?v=', 'https://www.youtube.com/embed/', $video->url);
				self::add('og:video:secure_url', $embedUrl);
			}

			self::add('og:video:type', $video->type);
			self::add('og:video:width', $video->width);
			self::add('og:video:height', $video->height);
		}
	}

	public static function image($images)
	{
		if (!$images || empty($images)) {
			return;
		}

		foreach ($images as $image) {
			self::add('og:image', $image->url);

			if ($image->width) {
				self::add('og:image:width', $image->width);
			}

			if ($image->height) {
				self::add('og:image:height', $image->height);
			}
		}
	}

	public static function url($url)
	{
		self::add('og:url', $url);
	}

	public static function start_time($startDate)
	{
		self::add('og:start_time', $startDate);
	}

	public static function end_time($endDate)
	{
		self::add('og:end_time', $endDate);
	}
}
