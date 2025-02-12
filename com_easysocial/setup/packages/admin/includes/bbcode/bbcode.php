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

class SocialBBCode extends EasySocial
{
	private $adapter    = null;

	// Used to convert bbcode video
	private $patterns = [
		'youtube.com' => 'youtube',
		'youtu.be' => 'youtube',
		'vimeo.com' => 'vimeo',
		'metacafe.com' => 'metacafe',
		'google.com' => 'google',
		'mtv.com' => 'mtv',
		'revver.com' => 'revver',
		'dailymotion.com' => 'dailymotion',
		'nicovideo.jp' => 'nicovideo',
		'smule.com' => 'smule'
	];

	private $code= '/\[video\](.*?)\[\/video\]/ms';

	public function __construct()
	{
		parent::__construct();

		require_once(__DIR__ . '/adapters/decoda/decoda.php');

		$this->adapter = new BBCodeDecodaAdapter();
	}

	/**
	 * Retrieves the video provider
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getProvider($url, $content)
	{
		$provider = strtolower($this->patterns[$url]);
		$file = __DIR__ . '/adapters/' . $provider . '.php';

		require_once($file);

		$class = 'SocialBBCode' . ucfirst($this->patterns[$url]);

		if (class_exists($class)) {
			$obj = new $class($url, false, $content);

			return $obj;
		}

		return false;
	}

	/**
	 * Replace contents
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function replaceVideo($content)
	{
		preg_match_all($this->code, $content, $matches);

		$videos	= $matches[0];

		if (!$videos) {
			return $content;
		}

		foreach ($videos as $video) {

			preg_match($this->code, $video , $match);

			$matchUrl = $match[1];

			// make sure the content has no htm tags.
			$rawUrl = strip_tags(html_entity_decode($matchUrl));

			if (stristr($rawUrl, 'http://') === false && stristr($rawUrl, 'https://') === false) {
				$rawUrl = 'http://' . $rawUrl;
			}

			$url = parse_url( $rawUrl );
			$url = explode( '.' , $url['host']);

			// Not a valid domain name.
			if (count($url) == 1) {
				return;
			}

			// Last two parts will always be the domain name.
			$url	= $url[ count( $url ) - 2 ] . '.' . $url[ count( $url ) - 1 ];

			if (!empty($url) && array_key_exists($url, $this->patterns)) {
				$provider = $this->getProvider($url, false, $content);

				$html = $provider->getEmbedHTML($rawUrl);

				$content = str_ireplace($video, $html, $content);
			}
		}

		return $content;
	}

	/**
	 * This class uses the factory pattern.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory()
	{
		$decoda = new self();

		return $decoda;
	}

	public static function getSmileys()
	{

	}

	/**
	 * Processes a string with decoda library.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function parse($string, $options = array(), &$streamTags = false, $debug = false)
	{
		if (!isset($options['escape'])) {
			$options['escape'] = false;
		}

		return $this->adapter->parse($string, $options, $streamTags, $debug);
	}

	public function parseRaw($string, $filters = array())
	{
		return $this->adapter->parseRaw($string, $filters);
	}

	/**
	 * Displays the markitup html
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function editor($nameAttribute , $value = '' , $config = array() , $attributes = array() )
	{
		$theme = ES::themes();
		$uniqueId = uniqid(rand());
		$attr = '';

		if (!empty($attributes)) {

			foreach ($attributes as $key => $val) {
				$attr .= ' ' . $key . '="' . $val . '"';
			}
		}

		// Determines if we should display the file browser
		$files = isset($config['files']) && $config['files'] ? true : false;

		// Determine the correct uid and type
		$uid  = isset($config['uid']) ? $config['uid'] : ES::user()->id;
		$type = isset($config['type']) ? $config['type'] : SOCIAL_TYPE_USER;

		if (isset($config['controllerName'])) {
			$theme->set('controllerName', $config['controllerName']);
		}

		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('files', $files);
		$theme->set('value', $value);
		$theme->set('attr', $attr);
		$theme->set('nameAttribute', $nameAttribute);
		$theme->set('uniqueId', $uniqueId);

		$output = $theme->output('site/bbcode/editor');

		return $output;
	}
}
