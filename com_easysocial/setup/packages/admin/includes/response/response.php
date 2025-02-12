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

/**
 * This is an extremely sinple library was created to convert the use of the exception library incorrectly.
 *
 * Its main purpose is to allow views to send a standard type with message property object
 * which can be routed through AJAX / JSON requests.
 *
 * @since	3.3
 */
class SocialResponse
{
	public $type;
	public $message;

	public function __construct($message, $type = ES_ERROR)
	{
		if (is_array($type)) {
			$type = $type[0];
		}

		$this->type = $type;

		// Retrieve the upload file error message
		if ($type == SOCIAL_EXCEPTION_UPLOAD) {
			$error = $this->getUploadErrorMessage($message);
			$message = $error['message'];

			if (!$message) {
				$message = JText::sprintf('COM_EASYSOCIAL_EXCEPTION_ADAPTER_UNKNOWN_ERROR', $type);
			}
		} else {
			// Translate message so a user can pass in the language string directly.
			$message = JText::_($message);
		}

		$this->message = $message;
	}

	/**
	 * Retrieve the error message of the uploaded file
	 *
	 * @since	3.3
	 * @access	public
	 */
	private function getUploadErrorMessage($file)
	{
		$code = $file['error'];

		switch ($code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = 'COM_EASYSOCIAL_EXCEPTION_UPLOAD_INI_SIZE';
				break;

			case UPLOAD_ERR_FORM_SIZE:
				$message = 'COM_EASYSOCIAL_EXCEPTION_UPLOAD_FORM_SIZE';
				break;

			case UPLOAD_ERR_PARTIAL:
				$message = 'COM_EASYSOCIAL_EXCEPTION_UPLOAD_PARTIAL';
				break;

			case UPLOAD_ERR_NO_FILE:
				$message = 'COM_EASYSOCIAL_EXCEPTION_UPLOAD_NO_FILE';
				break;

			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'COM_EASYSOCIAL_EXCEPTION_UPLOAD_NO_TMP_FILE';
				break;

			case UPLOAD_ERR_CANT_WRITE:
				$message = 'COM_EASYSOCIAL_EXCEPTION_UPLOAD_CANT_WRITE';
				break;

			case UPLOAD_ERR_EXTENSION:
				$message = 'COM_EASYSOCIAL_EXCEPTION_UPLOAD_EXTENSION';
				break;

			default:
				return null;
				break;
		}

		return [
			'message' => JText::_($message),
			'type' => ES_ERROR
		];
	}
}
