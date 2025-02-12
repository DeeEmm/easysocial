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

// Render autoload library
ES::autoload();

// Import the Intervention Image Manager Class
use Intervention\Image\ImageManager;

class SocialImage
{
	public $image = null;
	private $original = null;
	private $meta = null;
	private $adapter = null;

	static $sizes = [
		'thumbnail' => [
			'width'  => SOCIAL_PHOTOS_GIF_THUMB_WIDTH,
			'height' => SOCIAL_PHOTOS_GIF_THUMB_HEIGHT
		],

		'large' => [
			'width'  => SOCIAL_PHOTOS_GIF_LARGE_WIDTH,
			'height' => SOCIAL_PHOTOS_GIF_LARGE_HEIGHT
		]
	];

	public static $xssTags = array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');

	public function __construct()
	{
		$driver = 'gd';

		// Detect if imagick exists on the server
		if (ES::isImagickEnabled()) {
			$driver = 'imagick';
		}

		$this->adapter = new ImageManager([
			'driver' => $driver
		]);
	}

	/**
	 * Creates a snapshot of the image resource so that we can revert it at any given time
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function backup()
	{
		$this->image->backup();
	}

	/**
	 * Determines if the contents contains any of the known possible xss tags
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function contentsContainXss($contents)
	{
		foreach (self::$xssTags as $tag) {
			// If this tag is matched anywhere in the contents, we can safely assume that this file is dangerous
			if (stristr($contents, '<' . $tag . ' ') || stristr($contents, '<' . $tag . '>') || stristr($contents, '<?php') || stristr($contents, '?\>')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the file contains any funky html tags
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function containsXSS($path)
	{
		// Sanitize the content of the files
		$contents = file_get_contents($path, false, null, 0, 256);

		// If we can't read the file, just skip this altogether
		if (!$contents) {
			return false;
		}

		return $this->contentsContainXss($contents);
	}

	/**
	 * This class uses the factory pattern.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public static function factory()
	{
		$image 	= new self();

		return $image;
	}

	/**
	 * Determines if the image is a valid image type.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isValid()
	{
		if (!$this->image) {
			return false;
		}

		return true;
	}

	/**
	 * Loads an image resource given the path to the image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function load($path, $name = '')
	{
		// Set the meta info about this image.
		$meta = new stdClass();

		// Set the path for this image.
		$meta->path = $path;

		// Set the meta info
		$meta->info = getimagesize($path);

		// Set the name for this image.
		if (!empty($name)) {
			$meta->name = $name;
		} else {
			$meta->name = basename($path);
		}

		// If name is not provided, we'll generate a unique one for it base on the path.
		if (empty($meta->name)) {
			$meta->name = $this->genUniqueName($path);
		}

		$this->meta = $meta;

		// Set the image resource.
		try {
			$this->image = $this->adapter->make($path);
		} catch (Exception $e) {
		}

		// Check for any malicious contents
		$dangerousFile = $this->containsXSS($path);

		if ($dangerousFile) {
			$this->image = false;

			return false;
		}

		// Fix the orientation of the image first.
		$this->fixOrientation();

		// Set the original image resource.
		$this->original	= $this->image;

		return $this;
	}

	/**
	 * Updates the image resource on the current object
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function replaceImage($image)
	{
		$this->image = $image;
	}

	/**
	 * Clones the current image object
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function cloneImage()
	{
		$newImageResource = $this->adapter->make($this->meta->path);

		$image = clone($this);
		$image->replaceImage($newImageResource);

		return $image;

	}
	public function newInstance()
	{
		$image = ES::image();

		$image->load($this->meta->path, $this->meta->name);

		return $image;
	}

	/**
	 * Retrieves the mime of the current image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getMime()
	{
		if (!$this->image) {
			return false;
		}

		if (!isset($this->meta->info['mime'])) {
			return false;
		}

		return $this->meta->info['mime'];
	}

	/**
	 * Resizes an image to a specific width.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function width($width)
	{
		$this->image->resize($width, null, function($constraint) {
			$constraint->aspectRatio();
		});

		return $this;
	}

	/**
	 * Resizes an image to a specific height.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function height($height)
	{
		$this->image->resize(null, $height);

		return $this;
	}

	/**
	 * Gets the width of the image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getWidth()
	{
		$width = $this->meta->info[0];

		return $width;
	}

	/**
	 * Gets the width of the image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getHeight()
	{
		$height = $this->meta->info[1];

		return $height;
	}

	/**
	 * General resize for image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function resize($width = null, $height = null, $respectAspectRatio = true, $preventUpsize = true)
	{
		try {
			$this->image->resize($width, $height, function($constraint) use ($respectAspectRatio, $preventUpsize) {
				if ($respectAspectRatio) {
					$constraint->aspectRatio();
				}

				if ($preventUpsize) {
					$constraint->upsize();
				}
			});
		} catch (Exception $e) {

		}


		return $this;
	}

	/**
	 * Rotates the image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function rotate($angle = 0)
	{
		try {
			$this->image->rotate($angle);
		} catch (Exception $e) {
		}

		return $this;
	}

	/**
	 * Crops an image given the coordinates, width and height.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function crop($x, $y, $width, $height)
	{
		$this->image->crop($width, $height, $x, $y);

		return $this;
	}

	/**
	 * Save's the image resource in a target location.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save($target, $quality = null)
	{
		$state = false;
		$config = ES::config();

		if (is_null($quality)) {
			$quality = (int) $config->get('photos.uploader.quality', 90);
		}

		try {

			// Ensure the parent folder always exists or image library will throw an exception
			$folder = dirname($target);
			$exists = JFolder::exists($folder);

			if (!$exists) {
				JFolder::create($folder);
			}

			$result = $this->image->save($target, $quality);

			$state = true;
		} catch (Exception $e) {
		}

		return $state;
	}

	/**
	 * Save's the animated gif image resources in a target location.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveGif($storagePath, $filename, $overrideSize = [])
	{
		// Get the api key
		$config = ES::config();
		$key = $config->get('general.key');

		$post = array();

		$resources = $storagePath . '/' . $filename;

		// Get absolute path of the image
		$imageFile = JPATH_ROOT . $resources;

		$sizes = json_encode(self::$sizes);

		if ($overrideSize) {
			$sizes = json_encode($overrideSize);
		}

		// Add image file
		// php 5.5 and above will use CURLFile(imagepath)' instead of '@/imagepath' to add the image file
		$cfile = class_exists('CURLFile', false) ? new CURLFile($imageFile) : "@" . $imageFile;

		$post['imageFile'] = $cfile;
		$post['imageName'] = $this->getName(false);
		$post['sizes'] = $sizes;

		// Essential post data
		$post['key'] = $key;
		$post['storagePath'] = $storagePath;
		$post['domain'] = rtrim(JURI::root(), '/');

		$url = SOCIAL_SERVICE_IMAGE_RESIZER;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		curl_close($ch);

		// Check if this is a valid zip file.
		jimport('joomla.filesystem.archive');

		$zipAdapter = ESArchive::getAdapter('zip');

		$isZip = $zipAdapter->checkZipData($result);

		if ($isZip) {
			return $result;
		}

		// Create log file here since we know there are some error during the process above.
		$name = md5($filename);
		$logPath = JPATH_ROOT . $storagePath . '/' . $name . '.log';

		JFile::write($logPath, $result);

		return false;
	}

	/**
	 * Test whether the image is animated
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isAnimated()
	{
		$file = file_get_contents($this->meta->path);

		// Test if the image contain animation.
		$animated = preg_match('#(\x00\x21\xF9\x04.{4}\x00\x2C.*){2,}#s', $file);

		return $animated;
	}

	/**
	 * Just copy the file to the target
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function copy($target)
	{
		$state = JFile::copy($this->meta->path, $target);

		return $state;
	}

	/**
	 * Returns the path of the image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getPath()
	{
		return $this->meta->path;
	}

	/**
	 * Returns the name of the image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getName($hash = false)
	{
		if ($hash) {
			return $this->genUniqueName();
		}

		return $this->meta->name;
	}

	public function getOriginalExtension()
	{
		$extension = pathinfo($this->meta->name, PATHINFO_EXTENSION);
		$extension = '.' . $extension;

		return $extension;
	}

	/**
	 * Returns the extension type for this image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getExtension()
	{
		$mime = false;

		if (isset($this->meta->info['mime'])) {
			$mime = $this->meta->info['mime'];
		}

		// Default to png
		$extension = '.png';

		if ($mime == 'image/jpeg') {
			$extension = '.jpg';
		}

		if ($mime == 'image/webp') {
			$extension = '.webp';
		}

		return $extension;
	}

	/**
	 * Generates a random image name based on the node id.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function genUniqueName($salt = '')
	{
		if (!$salt) {
			$salt = uniqid();
		}

		$hash = md5($this->meta->name . $salt);

		return $hash;
	}

	/**
	 * Generates a file name for an image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function generateFilename($size, $fileName = '', $overrideExtension = false)
	{
		if (empty($fileName)) {
			$fileName = $this->getName(false);
		}

		// Remove any previously _stock from the image name
		$fileName = str_ireplace('_stock', '', $fileName);

		$extension = $this->getExtension();

		if ($overrideExtension) {
			$extension = $overrideExtension;
		}

		$fileName = str_ireplace($extension, '', $fileName);

		// Ensure that the file name is lowercased
		$fileName = strtolower($fileName);

		// Ensure that the file name is valid
		$fileName = JFilterOutput::stringURLSafe($fileName);

		// Append the size and extension back to the file name.
		$fileName = $fileName . '_' . $size . $extension;

		return $fileName;
	}

	/**
	 * Determines if the current image has exif data
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasExifSupport()
	{
		$mime = $this->getMime();

		if ($mime == 'image/jpg' || $mime == 'image/jpeg') {
			return true;
		}

		return false;
	}

	/**
	 * Fixes image orientation
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function fixOrientation()
	{
		$exif = ES::get('Exif');

		if (!$exif->isAvailable(false)) {
			return false;
		}

		// Get the mime type for this image
		$mime = $this->getMime();

		// Only image with jpeg are supported.
		if ($mime != 'image/jpeg') {
			return false;
		}

		// Load exif data.
		$exif->load($this->meta->path);

		$orientation = $exif->getOrientation();

		switch ($orientation) {
			case 1:
				// Do nothing here as the image is already correct.
				// $this->adapter->rotate($this->image, 0);
			break;

			// Flip image horizontally since it's at top right
			case 2:
				$this->image->flip('h');
				// $this->adapter->flop($this->image);
			break;

			// Rotate image 180 degrees left since it's at bottom right
			case 3:

				$this->image->rotate(180);
				// $this->adapter->rotate($this->image, 180);
			break;

			// Flip image vertically because it's at bottom left
			case 4:

				$this->image->flip('v');
				// $this->adapter->flip($this->image);
			break;

			// Flip vertically, then totate image 90 degrees right.
			case 5:
				$this->image->flip('v');
				// $this->adapter->flip($this->image);

				$this->image->rotate(90);
				// $this->adapter->rotate($this->image, -90);
			break;

			// Rotate image 90 degrees right
			case 6:
				$this->image->rotate(-90);
				// $this->adapter->rotate($this->image, 90);
			break;

			// Flip image horizontally
			case 7:
				$this->image->flip('h');
				$this->image->rotate(-90);

				// $this->adapter->flop($this->image);

				// Rotate 90 degrees right.
				// $this->adapter->rotate($this->image, 90);
			break;

			// Rotate image 90 degrees left
			case 8:
				$this->image->rotate(90);

				// $this->adapter->rotate($this->image, -90);
			break;
		}
	}

	/**
	 * Resets to the last backed up image
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function reset()
	{
		$this->image->backup();
	}
}
