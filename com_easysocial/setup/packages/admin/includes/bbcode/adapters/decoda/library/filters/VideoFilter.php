<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

/**
 * VideoFilter
 *
 * Provides the tag for videos. Only a few video services are supported.
 *
 * @author      Miles Johnson - http://milesj.me
 * @copyright   Copyright 2006-2011, Miles Johnson, Inc.
 * @license     http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link        http://milesj.me/code/php/decoda
 */

class VideoFilter extends DecodaFilter {

	/**
	 * Regex pattern.
	 */
	const VIDEO_PATTERN = '/^[-_a-z0-9]+$/is';

	/**
	 * Supported tags.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_tags = array(
		'video' => array(
			'template' => 'video',
			'type' => self::TYPE_BLOCK,
			'allowed' => self::TYPE_NONE,
			'pattern' => self::VIDEO_PATTERN,
			'attributes' => array(
				'default' => '/[a-z0-9]+/i',
				'size' => '/small|medium|large/i'
			)
		)
	);

	/**
	 * Known video url and format
	 *
	 * @since	2.2
	 * @access	private
	 */
	private $patterns = array(
		'youtube.com' => 'youtube',
		'youtu.be' => 'youtube',
		'vimeo.com' => 'vimeo',
		'metacafe.com' => 'metacafe',
		'google.com' => 'google',
		'mtv.com' => 'mtv',
		'liveleak.com' => 'liveleak',
		'revver.com' => 'revver',
		'dailymotion.com' => 'dailymotion',
		'nicovideo.jp' => 'nicovideo',
		'smule.com' => 'smule'
	);

	/**
	 * Video formats.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_formats = array(
		'youtube' => array(
			'small' => array(560, 315),
			'medium' => array(640, 360),
			'large' => array(853, 480),
			'player' => 'iframe',
			'path' => 'http://www.youtube.com/embed/{id}'
		),
		'vimeo' => array(
			'small' => array(400, 225),
			'medium' => array(550, 309),
			'large' => array(700, 394),
			'player' => 'iframe',
			'path' => 'http://player.vimeo.com/video/{id}'
		),
		'liveleak' => array(
			'small' => array(450, 370),
			'medium' => array(600, 493),
			'large' => array(750, 617),
			'player' => 'embed',
			'path' => 'http://liveleak.com/e/{id}'
		),
		'veoh' => array(
			'small' => array(410, 341),
			'medium' => array(610, 507),
			'large' => array(810, 674),
			'player' => 'embed',
			'path' => 'http://veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.5.3.1004&permalinkId={id}&player=videodetailsembedded&videoAutoPlay=0&id=anonymous'
		),
		'dailymotion' => array(
			'small' => array(320, 240),
			'medium' => array(480, 360),
			'large' => array(560, 420),
			'player' => 'embed',
			'path' => 'http://dailymotion.com/swf/video/{id}&additionalInfos=0&autoPlay=0'
		),
		'myspace' => array(
			'small' => array(325, 260),
			'medium' => array(425, 340),
			'large' => array(525, 420),
			'player' => 'embed',
			'path' => 'http://mediaservices.myspace.com/services/media/embed.aspx/m={id},t=1,mt=video'
		),
		'wegame' => array(
			'small' => array(325, 260),
			'medium' => array(480, 387),
			'large' => array(525, 420),
			'player' => 'embed',
			'path' => 'http://wegame.com/static/flash/player.swf?xmlrequest=http://www.wegame.com/player/video/{id}&embedPlayer=true'
		),
		'collegehumor' => array(
			'small' => array(300, 169),
			'medium' => array(450, 254),
			'large' => array(600, 338),
			'player' => 'embed',
			'path' => 'http://collegehumor.com/moogaloop/moogaloop.swf?clip_id={id}&use_node_id=true&fullscreen=1'
		)
	);

	/**
	 * Retrieves video provider
	 *
	 * @since	2.2
	 * @access	private
	 */
	private function getProvider($url)
	{
		$provider = strtolower($this->patterns[$url]);
		$file = __DIR__ . '/videoAdapters/' . $provider . '.php';

		require_once($file);

		$class = 'ESVideo' . ucfirst($this->patterns[$url]);

		if (class_exists($class)) {
			$obj = new $class();

			return $obj;
		}

		return false;
	}

	/**
	 * Custom build the HTML for videos.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function parse(array $tag, $content) 
	{
		// make sure the content has no htm tags.
		$rawUrl = strip_tags(html_entity_decode($content));

		if (stristr($rawUrl, 'http://') === false && stristr($rawUrl, 'https://') === false) {
			$rawUrl = 'http://' . $rawUrl;
		}

		$url = parse_url($rawUrl);
		$url = explode('.' , $url['host']);

		// Not a valid domain name.
		if (count($url) == 1) {
			return JText::_('Invalid video code');
		}

		// Last two parts will always be the domain name.
		$url = $url[count($url) - 2] . '.' . $url[count($url) - 1];

		if (!empty($url) && array_key_exists($url, $this->patterns)) {
			$provider = $this->getProvider($url);

			if (!$provider) {
				return JText::_('Invalid video code');
			}
		} else {
			return JTexT::_('Invalid video code');
		}

		$videoId = $provider->getCode($rawUrl);
		$providerName = $this->patterns[$url];

		$size = 'medium';

		if (empty($this->_formats[$providerName])) {
			return sprintf('(Invalid %s video code)', $providerName);
		}

		$video = $this->_formats[$providerName];
		$size = isset($video[$size]) ? $video[$size] : $video['medium'];

		$tag['attributes']['default'] = $providerName;
		$tag['attributes']['width'] = $size[0];
		$tag['attributes']['height'] = $size[1];
		$tag['attributes']['player'] = $video['player'];
		$tag['attributes']['url'] = str_replace(array('{id}', '{width}', '{height}'), array($videoId, $size[0], $size[1]), $video['path']);

		return parent::parse($tag, $content);
	}
}
