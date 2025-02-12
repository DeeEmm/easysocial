<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialUploader
{
	public $name = 'file';
	public $maxsize = null;
	public $multiple = false;
	protected $type = null;

	public function __construct($options = [], $type = null)
	{
		$this->type = $type;
		$this->input = JFactory::getApplication()->input;

		if (isset($options['name'])) {
			$this->name = $options['name'];
		}

		if (isset($options['maxsize'])) {
			$this->maxsize = $options['maxsize'];
		}

		if (isset($options['multiple'])) {
			$this->multiple = $options['multiple'];
		}
	}

	/**
	 * Retrieves the mime of the uploaded file
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getMime()
	{
		$file = $this->input->files->get($this->name);

		if (!$file || !is_array($file) || !isset($file['type'])) {
			return false;
		}

		return $file['type'];
	}

	public static function factory($options = [])
	{
		$obj = new self($options);
		return $obj;
	}

	public function formatAllowedExtension($commaSeparatedString)
	{
		$items = explode(',', $commaSeparatedString);

		// Remove empty values
		$items = array_filter($items);

		return $items;
	}

	/**
	 * Generates a unique token for the current session.
	 * Without this token, the caller isn't allowed to upload the file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function generateToken()
	{
		// Generate a unique id.
		$id = uniqid();

		// Add md5 hash
		$id = md5($id);

		$table = ES::table('UploaderToken');
		$table->token = $id;
		$table->created = ES::date()->toMySQL();

		$table->store();

		return $id;
	}

	/**
	 * Performs validity checks of files
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getFile($name = null, $filter = '')
	{
		// Check if post_max_size is exceeded.
		if (empty($_FILES) && empty($_POST)) {
			return ES::response('COM_EASYSOCIAL_EXCEPTION_UPLOAD_POST_SIZE');
		}

		// Get the file
		if (empty($name)) {
			$name = $this->name;
		}

		$file = $this->input->files->get($name, array(), 'raw');

		if ($this->multiple) {
			$tmp = $_FILES[$name];
			$file = array();

			foreach ($tmp as $k => $v) {
				$file[$k] = $v['file'];
			}
		}

		// Check for invalid file object
		if (empty($file)) {
			return ES::response('COM_EASYSOCIAL_EXCEPTION_UPLOAD_NO_OBJECT');
		}

		// Verify file extension
		$verify = $this->verifyFileExtension($file);

		if ($verify !== true) {
			return $verify;
		}

		// If there's an error in this file
		if ($file['error']) {
			return ES::response($file, SOCIAL_EXCEPTION_UPLOAD);
		}

		// Check if file exceeds max upload filesize
		$maxsize = ES::math()->convertBytes($this->maxsize);

		if ($maxsize > 0 && $file['size'] > $maxsize) {
			return ES::response(
				JText::sprintf(
					'COM_EASYSOCIAL_EXCEPTION_UPLOAD_MAX_SIZE',
					ES::math()->convertUnits($maxsize, 'B', 'MB', false, true)
				)
			);
		}

		// Ensure that the file is valid
		if ($filter && $filter == 'image') {
			$result = $this->filter($filter, $file);

			if ($result !== true) {
				return $result;
			}
		}

		// Return file
		return $file;
	}

	/**
	 * Filters a file
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function filter($filter, $file)
	{
		if (!$filter) {
			return true;
		}

		if ($filter == 'image') {
			$result = \JFilterInput::isSafeFile($file);

			if (!$result) {
				return ES::response('COM_EASYSOCIAL_EXCEPTION_INVALID_IMAGE');
			}

			$containsXSS = $this->containsXSS($file['tmp_name']);

			if ($containsXSS) {
				return ES::response('COM_EASYSOCIAL_EXCEPTION_INVALID_IMAGE');
			}

			if (!$this->isImage($file['type']) || !$this->isImageExtension($file['name'])) {
				return ES::response('COM_EASYSOCIAL_EXCEPTION_INVALID_IMAGE');
			}

			if (!ES::isImagickEnabled()) {
				$info = getimagesize($file['tmp_name']);

				if (!$info) {
					return ES::response('COM_EASYSOCIAL_EXCEPTION_INVALID_IMAGE');
				}
			}
		}

		return true;
	}

	/**
	 * Determines if the file is truly an image
	 *
	 * @since	3.2.8
	 * @access	public
	 */
	public function isImage($mime = null)
	{
		if (is_null($mime)) {
			$mime = $this->getMime();
		}

		return ES::isImage($mime);
	}

	/**
	 * Determines if an extension is an image type
	 *
	 * @since	3.2.8
	 * @access	public
	 */
	public function isImageExtension($fileName)
	{
		static $imageTypes = array('gif', 'jpg', 'jpeg', 'png', 'webp');

		if (ES::isImagickEnabled()) {
			$imageTypes[] = 'heic';
		}

		$extension = JFile::getExt($fileName);
		$extension = strtolower($extension);

		return in_array($extension, $imageTypes);
	}

	/**
	 * Checks if the file contains any funky html tags
	 *
	 * @since	5.0
	 * @access	public
	 */
	public static function containsXSS($path)
	{
		// Sanitize the content of the files
		$contents = file_get_contents($path, false, NULL, 0, 256);

		$tags = array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');

		// If we can't read the file, just skip this altogether
		if (!$contents) {
			return false;
		}

		foreach ($tags as $tag) {
			// If this tag is matched anywhere in the contents, we can safely assume that this file is dangerous
			if (stristr($contents, '<' . $tag . ' ') || stristr($contents, '<' . $tag . '>') || stristr($contents, '<?php') || stristr($contents, '?\>')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize file name
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function normalizeFilename($file, $postfix = false)
	{
		$filename = $file['name'];

		// we need to check if file name don't have any extension,
		// we manually add it.
		$ext = explode('.', $filename);

		// Append postfix if exists
		if ($postfix) {
			$filename = $ext[0] . '-' . $postfix;

			if (isset($ext[1])) {
				$filename .= '.' . $ext[1];
			}
		}

		if (!isset($ext[1])) {
			$fileContent = file_get_contents($file['tmp_name']);

			// To fix upload audio file from android (no extension)
			if (substr($fileContent, 0, 3) == 'ID3') {
				$filename .= '.mp3';
			}
		}

		$filename = str_replace(',', '', $filename);

		return $filename;
	}

	/**
	 * Normalizing file type
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function normalizeFiletype($file)
	{
		$fileType = $file['type'];

		// if the file type is application/octet-stream
		// means system cannot detect the file type or the extension is missing
		if ($fileType == 'application/octet-stream') {

			$fileContent = file_get_contents($file['tmp_name']);

			// To fix upload audio file from android (upload with no extension)
			if (substr($fileContent, 0, 3) == 'ID3') {
				$fileType = 'audio/mpeg';
			}
		}

		return $fileType;
	}

	/**
	 * Performs validity checks of files
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public function validate($file, $filter = '')
	{
		// Check for invalid file object
		if (empty($file)) {
			return ES::response('COM_EASYSOCIAL_EXCEPTION_UPLOAD_NO_OBJECT');
		}

		// If there's an error in this file
		if ($file['error']) {
			return ES::response($file, SOCIAL_EXCEPTION_UPLOAD);
		}

		// Check if file exceeds max upload filesize
		$maxsize = ES::math()->convertBytes($this->maxsize);

		if ($maxsize > 0 && $file['size'] > $maxsize) {
			return ES::response(
				JText::sprintf(
					'COM_EASYSOCIAL_EXCEPTION_UPLOAD_MAX_SIZE',
					ES::math()->convertUnits($maxsize, 'B', 'MB', false, true)
				)
			);
		}

		// Ensure that the file is valid
		if ($filter && $filter == 'image') {
			$result = $this->filter($filter, $file);

			if ($result !== true) {
				return $result;
			}
		}

		// Return file
		return $file;
	}

	/**
	 * Verifies the file extension
	 *
	 * @since	3.2.8
	 * @access	public
	 */
	public function verifyFileExtension($file)
	{
		// If we don't know the type, skip this
		if (!$this->type) {
			return true;
		}

		$pattern = null;

		if ($this->type == 'conversations') {
			$config = ES::config();
			$allowed = $config->get('conversations.attachments.types');
			$allowed = $this->formatAllowedExtension($allowed);
			$pattern = implode('|', $allowed);
		}

		if (!is_null($pattern)) {
			$fileName = $file['name'];
			$match = preg_match("/$pattern/i", $fileName);

			if (!$match) {
				return ES::response('Invalid file provided');
			}
		}

		return true;
	}
}
