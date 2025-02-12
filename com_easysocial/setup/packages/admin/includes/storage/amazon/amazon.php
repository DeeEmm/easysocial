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

// Include amazon's autoloader
require_once(__DIR__ . '/autoloader.php');

use Aws\S3\S3Client;

class SocialStorageAmazon implements SocialStorageInterface
{
	public $config = null;
	public $bucket = null;
	public $region = null;
	public $endpoint = 's3.amazonaws.com';
	public $secure = null;

	private $client = null;

	public function __construct()
	{
		$this->config = ES::config();

		// Get the access and secret keys
		$access = trim($this->config->get('storage.amazon.access'));
		$secret = trim($this->config->get('storage.amazon.secret'));

		// Determines if we should be using ssl
		$this->secure = $this->config->get('storage.amazon.ssl');

		// Determines the bucket that we should use
		$this->bucket = rtrim($this->config->get('storage.amazon.bucket'), '/');

		// Get the region
		$this->region = $this->config->get('storage.amazon.region');

		// We need to construct the endpoint uri for non "US" regions
		if ($this->region != "us") {
			$this->endpoint = 's3-' . $this->region . '.amazonaws.com';
		}

		// Amazon renamed their standard us region to us-east-1
		if ($this->region == 'us') {
			$this->region = 'us-east-1';
		}

		$options = new stdClass();
		$options->credentials = array('key' => $access, 'secret' => $secret);
		$options->signature = 'v4';
		$options->region = $this->region;
		$options->version = 'latest';

		$options = (array) $options;

		$this->client = S3Client::factory($options);
	}

	/**
	 * Initializes a bucket
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function init()
	{
		// We assume that either the system or the user has already created the bucket.
		if (!empty($this->bucket)) {
			return $this->bucket;
		}

		// Initialize to check if the container exists
		$jConfig = ES::config('joomla');
		$bucket = str_ireplace('http://' , '' , JURI::root());
		$bucket = JFilterOutput::stringURLSafe($bucket);

		// Create a new container
		$exists = $this->containerExists($bucket);

		if (!$exists) {
			$result = $this->createContainer($bucket);
		}

		return $bucket;
	}

	/**
	 * Checks if the provided bucket exists on S3
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function containerExists($bucket)
	{
		// Create a new container
		$exists = $this->client->doesBucketExist($bucket);

		if (!$exists) {
			return false;
		}

		return true;
	}

	/**
	 * Creates a new container on Amazon S3
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function createContainer($container)
	{
		$options = new stdClass();
		$options->Bucket = $container;

		// Since this amazon API is version 2, we should not be passing in the location constraint if this region is us standard.
		if ($this->region != 'us-east-1') {
			$options->LocationConstraint = $this->region;
		}

		$options = (array) $options;

		$result = $this->client->createBucket($options);

		if (isset($result['RequestId'])) {
			return true;
		}

		return false;
	}

	/**
	 * Returns a list of buckets
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function getContainers()
	{
		$result = $this->client->listBuckets();

		if (!isset($result['Buckets'])) {
			return array();
		}

		$buckets = $result['Buckets'];

		return $buckets;
	}

	/**
	 * Returns the absolute path to the object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermalink($relativePath)
	{
		// Ensure that the preceeding / is removed
		$relativePath = ltrim($relativePath, '/');

		$paths = explode('/', $this->bucket);

		$subfolder = false;
		$base = $paths[0];

		if (count($paths) > 1) {
			unset($paths[0]);
			$subfolder = implode('/', $paths);
		}

		$url = $this->secure ? 'https://' : 'http://';

		if ($url == 'https://' || stristr($base, '_') !== false) {
			$url .= $this->endpoint . '/' . $base . '/';
		} else {
			$url .= $this->endpoint . '/' . $base . '/';
		}

		if ($subfolder) {
			$url .= rtrim($subfolder, '/') . '/';
		}

		$url .= $relativePath;

		return $url;
	}

	/**
	 * Pushes a file to the remote repository
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function push($fileName, $source, $dest, $mimeType = 'application/octet-stream')
	{
		$options = new stdClass();
		$options->Bucket = $this->bucket;
		$options->Key = $dest;
		$options->SourceFile = $source;
		$options->ACL = "public-read";
		$options->ContentType = $mimeType;

		// Determine if this file is downloadable
		if ($mimeType == 'application/octet-stream') {
			$options->ContentDisposition = "attachment; filename=" . $fileName;
		}

		$options->CacheControl = 'max-age=172800';

		$options = (array) $options;

		try {
			$result = $this->client->putObject($options);
		} catch (Exception $e) {

			$exception = ES::response($e->getMessage(), ES_ERROR);

			return $exception;
		}

		// Here we assume that if there is an "ObjectURL" returned, it is success.
		if (isset($result["ObjectURL"])) {
			return true;
		}

		return false;
	}

	/**
	 * Pulls a file from the remote repositor
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function pull($relativePath)
	{
		$this->client->downloadBucket(JPATH_ROOT, $this->bucket, $relativePath);
	}

	/**
	 * Deletes a file from the remote repository
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete($paths, $folder = false)
	{
		if (is_array($paths)) {

			// Ensure that all indexes are integers
			$paths = array_values($paths);

			foreach ($paths as $relativePath) {

				// Ensure that leading / is removed
				$relativePath = ltrim($relativePath, '/');

				// Finally delete the last item
				$this->client->deleteObject(array('Bucket' => $this->bucket, 'Key' => $relativePath));
			}

			return true;
		}

		// Ensure that leading / is removed
		$paths = ltrim($paths, '/');

		if ($folder) {

			$options = array('Bucket' => $this->bucket, 'Prefix' => $paths);
			$result = $this->client->listObjects($options);

			// Nothing here
			if (!isset($result['Contents']) || !$result['Contents']) {
				return false;
			}

			$objects = $result['Contents'];

			foreach ($objects as $object) {
				$options = array('Bucket' => $this->bucket, 'Key' => $object["Key"]);

				$this->client->deleteObject($options);
			}

			return true;
		}

		// If this is not a folder, we just delete it.
		$this->client->deleteObject(array('Bucket' => $this->bucket, 'Key' => $paths));

		return true;
	}

	/**
	 * Method to download the file from amazon
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function download($filePath, $fileName)
	{
		$command = $this->client->getCommand('GetObject', array(
			'Bucket' => $this->bucket,
			'Key' => $filePath,
			'ResponseContentDisposition' => 'attachment; filename="' . $fileName . '"'
		));

		$signedUrl = $command->createPresignedUrl('+15 minutes');

		echo $signedUrl;
		header('Location: '. $signedUrl);
		exit;
	}
}
