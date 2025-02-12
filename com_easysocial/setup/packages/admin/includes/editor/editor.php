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

class SocialEditor extends EasySocial
{
	/**
	 * This is to fix the "none" editor bug on Joomla 3.7.0
	 *
	 *
	 * @since	2.0.17
	 * @access	public
	 */
	public function getEditor($type)
	{
		static $instances = [];

		if (!isset($instances[$type])) {
			$instances[$type] = ESCompat::getEditor($type);
		}

		return $instances[$type];
	}

	/**
	 * Since Joomla 3.7.0 has different implementation of $editor->getContent, we need to abstract it here.
	 *
	 * Joomla 3.7.0 TinyMCE replaces xx-yy-zz with xx_yy_zz
	 * Joomla 3.6.0 TinyMCE doesn't replace anything
	 *
	 * @since	2.0.17
	 * @access	public
	 */
	public function getContent($editor, $inputName)
	{
		$isJoomla37 = version_compare(JVERSION, '3.7.0') !== -1;
		$type = $editor->get('_name');

		if ($type == 'tinymce' && $isJoomla37) {
			$inputName = str_ireplace('-', '_', $inputName);
		}

		return $editor->getContent($inputName);
	}

	/**
	 * Joomla 4 compatibility
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getSaveMethod($editor, $inputName)
	{
		static $instances = [];

		if (!isset($instances[$editor])) {
			$instances[$editor] = '';
			$editorInstance = $this->getEditor($editor);

			if (method_exists($editorInstance, 'save')) {
				$instances[$editor] = $editorInstance->save($inputName);
			}
		}

		return $instances[$editor];
	}

	/**
	 * Format the editor ID 
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function formatId($inputName, $editorName)
	{
		if ($editorName == 'tinymce') {
			$inputName = preg_replace('/(\s|[^A-Za-z0-9_])+/', '_', $inputName);
		}

		return $inputName;
	}
}
