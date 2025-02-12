<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

/**
 * EmoticonHook
 *
 * Converts smiley faces into emoticon images.
 *
 * @author      Miles Johnson - http://milesj.me
 * @copyright   Copyright 2006-2011, Miles Johnson, Inc.
 * @license     http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link        http://milesj.me/code/php/decoda
 */

class EmoticonHook extends DecodaHook {

	/**
	 * Configuration.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_config = array(
		'path' => '',
		'originalPath' => ''
	);

	/**
	 * Mapping of emoticons and smilies.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_emoticons = array();

	/**
	 * Map of smilies to emoticons.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_map = array();

	/**
	 * Load the emoticons from the JSON file.
	 *
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		parent::__construct($config);

		$path = DECODA_CONFIG . 'emoticons.json';

		// Check if there's an emoticon directory in template's folder
		$this->_config['path']	= '/templates/' . JFactory::getApplication()->getTemplate() . '/html/com_easysocial/emoticons/';
		$this->_config['originalPath'] = '/media/com_easysocial/images/icons/emoji/';

		$overrideExists = JFolder::exists(JPATH_ROOT . $this->_config['path']);

		// Set the path to our own directory
		// Force root URI in as '/' does not work properly on subfolder sites
		if (!$overrideExists) {
			$this->_config['path'] = $this->_config['originalPath'];
		} else {
			// check if the json file exits or not.
			$jsonFile = JPATH_ROOT . $this->_config['path'] . 'emoticons.json';

			if (JFile::exists($jsonFile)) {
				$path = $jsonFile;
			}

			// let apppend root here
			$this->_config['path'] = $this->_config['path'];
		}

		if (file_exists($path)) {
			$this->_emoticons = json_decode(file_get_contents($path), true);

			foreach ($this->_emoticons as $emoticon => $smilies) {
				foreach ($smilies as $smile) {
					$this->_map[$smile] = $emoticon;
				}
			}

			if (empty($this->_config['path'])) {
				$this->_config['path'] = str_replace(array(realpath($_SERVER['DOCUMENT_ROOT']), '\\', '/'), array('', '/', '/'), DECODA_EMOTICONS);
			}
		}

		// Always append the absolute url here
		$this->_config['path'] = $this->_config['path'];
	}

	/**
	 * Parse out the emoticons and replace with images.
	 *
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public function beforeParse($content)
	{
		if ($this->getParser()->getFilter('Image') && !empty($this->_emoticons)) {
			foreach ($this->_emoticons as $smilies) {
				foreach ($smilies as $smile) {
					// $pattern = '/(?P<left>^|\n|\s)(?:' . preg_quote($smile, '/') . ')(?P<right>\n|\s|$)/is';

					$pattern = '/(?P<left>^|\n|\s)(?:' . preg_quote($smile, '/') . ')(?P<right>\n|\s|$)/is';
					$content = preg_replace_callback($pattern, array($this, '_emoticonCallback'), $content);
					$content = preg_replace_callback($pattern, array($this, '_emoticonCallback'), $content);
					// $content = preg_replace_callback($pattern, array($this, '_emoticonCallback'), $content);
					// $pattern	= '/(^|\s)+' . preg_quote($smile, '/') . '(\s|$)+/is';
					// $content 	= preg_replace_callback($pattern, array($this, '_emoticonCallback'), $content);
				}
			}
		}

		// we will need to cross check with built in emoticn browser as well
		// get all the emoticons
		$model = ES::model('Emoticons');
		$emoticons = $model->getItems();

		foreach ($emoticons as $emo) {
			$smile = '(' . $emo->title . ')';
			$pattern = '/(?P<left>^|\n|\s)(?:' . preg_quote($smile, '/') . ')(?P<right>\n|\s|$)/is';
			$content = preg_replace_callback($pattern, array($this, '_builtInEmoticonCallback'), $content);
			$content = preg_replace_callback($pattern, array($this, '_builtInEmoticonCallback'), $content);
		}

		return $content;
	}

	/**
	 * Callback for smiley processing.
	 *
	 * @access protected
	 * @param array $matches
	 * @return string
	 */
	protected function _builtInEmoticonCallback($matches) {
		$smiley = trim($matches[0]);

		// once matches, try to load the emoticon table
		$title = str_replace(array('(', ')'), '', $smiley);

		// Load the emoticon using title
		$table = ES::table('emoticon');
		$table->load(array('title' => $title));

		if (!$table->id) {
			return $matches[0];
		}

		$image = $table->getIcon();

		if (count($matches) === 1) {
			return $image;
		}

		$l = isset($matches[1]) ? $matches[1] : '';
		$r = isset($matches[2]) ? $matches[2] : '';

		return $l . $image . $r;
	}

	/**
	 * Callback for smiley processing.
	 *
	 * @access protected
	 * @param array $matches
	 * @return string
	 */
	protected function _emoticonCallback($matches) {
		$smiley = trim($matches[0]);

		if (count( $matches ) === 1 && isset($this->_map[ $smiley ])) {
			$image = $this->getParser()->getFilter('Image')->parse(array(
				'tag' => 'img',
				'attributes' => array()
			), $this->_config['path'] . $this->_map[$smiley] . '.png');

			return $image;
		}

		if (count($matches) === 1 || !isset($this->_map[$smiley])) {
			return $matches[0];
		}

		$l = isset($matches[1]) ? $matches[1] : '';
		$r = isset($matches[2]) ? $matches[2] : '';
		$smileyPath = $this->_config['path'] . $this->_map[$smiley] . '.png';

		// Check if the emoji is exists if override folder exists.
		if ($this->_config['path'] != $this->_config['originalPath']) {
			$absoulteSmiley = JPATH_ROOT . $smileyPath;

			// Override file is not exists
			if (!JFile::exists($absoulteSmiley)) {
				$smileyPath = $this->_config['originalPath'] . $this->_map[$smiley] . '.png';
			}
		}

		// Add support for custom format
		if ($this->getParser()->restFormat) {
			$identifier = uniqid();
			$streamTags = $this->getParser()->streamTags;

			if (!is_array($streamTags)) {
				$streamTags = ES::makeArray($streamTags);
			}

			$object = new stdClass();
			$object->identifier = $identifier;
			$object->type = 'emoticon';
			$object->source = $smileyPath;

			$streamTags[] = $object;

			$this->getParser()->streamTags = $streamTags;

			// return $l . '[object key=' . $identifier . ']' . $r;
			return $l . '[[object]]' . $identifier . '[[object]]' . $r;
		}

		// Append the http protocol for the email content which required absolute path
		if (strpos($smileyPath, 'http://') !== 0 || strpos($smileyPath, 'https://') !== 0) {
			$smileyPath = rtrim(JURI::root() , '/') . $smileyPath;
		}

		$image = $this->getParser()->getFilter('Image')->parse(array(
			'tag' => 'img',
			'attributes' => array('class' => 'emoji', 'width' => '20', 'height' => '20')
		), $smileyPath);

		return $l . $image . $r;
	}
}
