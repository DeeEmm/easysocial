<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewCrawler extends EasySocialSiteView
{
	/**
	 * Does a remote call to the server to fetch contents of a given url.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function proxy()
	{
		$type = $this->input->get('type', '', 'word');
		$url = $this->input->get('url', '', 'default');

		// Currently we only support serving of images
		if ($type != 'image') {
			$this->blank();
		}

		$connector = ES::get('Connector');
		$connector->addUrl($url);
		$connector->connect();

		$contents = $connector->getResult($url, true);

		list($headers, $output) = explode("\r\n\r\n", $contents);


		// Get the content type
		$pattern = '/Content-Type: (.*)/i';
		preg_match($pattern, $headers, $matches);

		if (!$matches || empty($matches) || !isset($matches[1])) {
			$this->blank();
		}

		$contentType = strtolower(trim($matches[1]));
		$allowedTypes = array('image/png', 'image/jpeg', 'image/gif', 'image/bmp');

		// Only allow image types
		if (!in_array($contentType, $allowedTypes)) {
			$this->blank();
		}

		header('Content-Type: ' . $contentType);
		header('Content-Length: ' . strlen($output));
		echo $output;
		exit;
	}

	/**
	 * Allows caller to render a blank image
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function blank()
	{
		$file = JPATH_ROOT . '/media/com_easysocial/images/crawler/blank.png';
		$size = filesize($file);
		$info = getimagesize($file);

		header('Content-Type: image/png');
		header('Content-Length: ' . $size);
		echo file_get_contents($file);
		exit;
	}
}
