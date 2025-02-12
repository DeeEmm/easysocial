<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialCrawlerAbstract extends EasySocial
{
	public $url = null;
	public $parser = null;
	public $contents = '';
	public $oembed = false;

	public function __construct($url, $parser, $contents)
	{
		parent::__construct();

		$this->url = $url;
		$this->parser = $parser;
		$this->contents = $contents;
	}

	/**
	 * Determines if the current link supports oembed tags
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOembedUrl()
	{
		$link = $this->parser->find('link[type=application/json+oembed]');

		if ($link) {
			return $link;
		}

		return false;
	}

	public function getOembed()
	{
		$oembedUrl = $this->getOembedUrl();

		if (!$oembedUrl) {
			return false;
		}

		$object = false;

		foreach ($oembedUrl as $node) {

			if (!isset($node->attr['href'])) {
				continue;
			}

			// Get the oembed url
			$url = $node->attr['href'];

			// Urls should not contain html entities
			$url = html_entity_decode($url);

			// Now we need to crawl the url again
			$connector = ES::connector($url);
			$contents = $connector
							->execute()
							->getResult();

			$object = json_decode($contents);

			if (is_array($object) && isset($object[0])) {
				$object = $object[0];
			}

			if (isset($object->thumbnail_url)) {
				$object->thumbnail = $object->thumbnail_url;
			}

			// For wordpress
			$wordpress = $this->isWordpress($url);

			if (is_object($object)) {
				$object->isWordpress = false;
			} else {
				$object = new stdClass();
				$object->isWordpress = false;
			}

			if ($wordpress) {
				$object->isWordpress = true;
			}
		}

		return $object;
	}

	private function isWordpress($url)
	{
		if (stristr($url, 'https://public-api.wordpress.com/oembed') !== false) {
			return true;
		}

		// For some cases, the url for wordpress is different
		// http://site.com/wp-json/oembed/1.0/embed?url=http%3A%2F%2Fsite.com.br%2Farticle1%2F
		$url = rtrim($url,'/');
		$tmp = explode('/', $url);

		if (strtolower($tmp[count($tmp) - 3] == 'oembed') && strtolower($tmp[count($tmp) - 4] == 'wp-json')) {
			return true;
		}

		return false;
	}

	/**
	 * Fix the http url in https site
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function fixOembedUrl($oembed)
	{
		$uri = JURI::getInstance();

		if ($uri->getScheme() == 'https') {
			$oembed->html = ESJString::str_ireplace('http://', 'https://', $oembed->html);
			$oembed->thumbnail = str_ireplace('http://', 'https://', $oembed->thumbnail);

			if (isset($oembed->thumbnail_url)) {
				$oembed->thumbnail_url = str_ireplace('http://', 'https://', $oembed->thumbnail_url);
			}
		}

		return $oembed;
	}

	/**
	 * Generates the opengraph data given the link to the video
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getOpengraphData($contents)
	{
		$og = new stdClass();

		$parser = SocialSimpleHTML::str_get_html($contents);

		$og->type = 'video';

		$meta = @$parser->find('meta[property=og:video]');

		if ($meta && isset($meta[0])) {
			$og->video = $meta[0]->content;
		}

		$meta = $parser->find('meta[property=og:image]');
		$og->image = $meta[0]->content;

		$meta = @$parser->find('meta[property=og:title]');
		$og->title = $meta[0]->content;

		$meta = @$parser->find('meta[property=og:video:width]');

		if ($meta && isset($meta[0])) {
			$og->video_width = $meta[0]->content;
		}

		$meta = @$parser->find('meta[property=og:video:height]');

		if ($meta && isset($meta[0])) {
			$og->video_height = $meta[0]->content;
		}

		$meta = @$parser->find('meta[property=og:video:duration]');

		if ($meta && isset($meta[0])) {
			$og->video_duration = $meta[0]->content;
		}

		return $og;
	}
}
