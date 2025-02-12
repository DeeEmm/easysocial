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

jimport('joomla.application.component.model');

// Include the main model parent.
ES::import('admin:/includes/model');

class EasySocialModelEmails extends EasySocialModel
{
	public $total = null;
	public $pagination = null;
	private $data = null;
	private $displayOptions = null;

	public function __construct( $config = array() )
	{
		$this->displayOptions = array();
		parent::__construct('Emails', $config);
	}

	/**
	 * Retrieves a list of email template files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFiles()
	{
		$folder = $this->getFolder();

		// Retrieve the list of files
		$rows = JFolder::files($folder, '.', true, true);
		$files = array();

		// Get the current site template
		$currentTemplate = $this->getCurrentTemplate();

		foreach ($rows as $row) {

			$row = ES::normalizeSeparator($row);
			$fileName = basename($row);

			if ($fileName == 'index.html' || stristr($fileName, '.orig') !== false) {
				continue;
			}

			// Get the file object
			$file = $this->getTemplate($row);
			$files[] = $file;
		}

		return $files;
	}

	/**
	 * Generates the path to an email template
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFolder()
	{
		$folder = SOCIAL_ROOT . '/themes/wireframe/emails';
		$folder = ES::normalizeSeparator($folder);

		return $folder;
	}

	/**
	 * Generates the path to the overriden folder
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOverrideFolder($file)
	{
		$path = JPATH_ROOT . '/templates/' . $this->getCurrentTemplate() . '/html/com_easysocial/emails/' . ltrim($file, '/');

		return $path;
	}

	/**
	 * Retrieves a list of email templates
	 *
	 * @since	4.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTemplate($absolutePath, $contents = false)
	{
		$file = new stdClass();
		$file->name = basename($absolutePath);

		$file->desc = str_ireplace('.php', '', $file->name);

		$groupName = '';
		$uriSegments = explode('/', $absolutePath);

		// Retrieve the URI elements for last second segment
		$lastSecondSegments = array_slice($uriSegments, -2, 1);

		// separate the group in the email description constants to prevent duplicate
		if ($lastSecondSegments) {
			$groupName = $lastSecondSegments[0] . '_';
		}

		$file->desc = $groupName . str_ireplace(['.', '-'], '_', $file->desc);
		$file->desc = strtoupper($file->desc);
		$file->desc = JText::_('COM_EASYSOCIAL_EMAILS_TEMPLATE_' . $file->desc);
		$file->path = $absolutePath;
		$file->relative = str_ireplace($this->getFolder(), '', $file->path);

		// Get the current site template
		$currentTemplate = $this->getCurrentTemplate();

		// Determine if the email template file has already been overriden.
		$overridePath = $this->getOverrideFolder($file->relative);

		$file->override = JFile::exists($overridePath);
		$file->overridePath = $overridePath;
		$file->contents = '';

		if ($contents) {
			if ($file->override) {
				$file->contents = file_get_contents($file->overridePath);
			} else {
				$file->contents = file_get_contents($file->path);
			}
		}
		return $file;
	}

	/**
	 * Retrieves the current site template
	 *
	 * @since	4.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getCurrentTemplate()
	{
		$db = ES::db();

		$query = 'SELECT ' . $db->nameQuote('template') . ' FROM ' . $db->nameQuote('#__template_styles');
		$query .= ' WHERE ' . $db->nameQuote('home') . '=' . $db->Quote(1);
		$query .= ' AND ' . $db->qn('client_id') . '=' . $db->Quote(0);

		$db->setQuery($query);

		$template = $db->loadResult();
		return $template;
	}

	/**
	 * Saves contents
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function write($path, $contents)
	{
		$state = JFile::write($path, $contents);

		return $state;
	}
}
