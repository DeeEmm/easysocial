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

class EasySocialViewUploader extends EasySocialSiteView
{
	/**
	 * Previews an item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preview()
	{
		$id = $this->input->get('id', 0, 'int');

		$file = ES::table('Upload');
		$exists = $file->load($id);

		if (!$exists || !$file->id) {
			throw ES::exception('Unknown file', 500);
		}

		header('Content-Description: File Transfer');
		header('Content-Type: ' . $file->mime);
		header('Content-Disposition: inline');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . $file->size);
		ob_clean();
		flush();

		echo $file->getContents();

		exit;
	}
}
