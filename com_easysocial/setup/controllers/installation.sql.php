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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationSql extends EasySocialSetupController
{
	/**
	 * Executes the necessary SQL queries during installation
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		// Get the temporary path from the server.
		$tmpPath = $this->input->get('path', '', 'default');

		// There should be a queries.zip archive in the archive.
		$tmpQueriesPath = $tmpPath . '/queries.zip';

		// Extract the queries
		$path = $tmpPath . '/queries';

		// Check if this folder exists.
		if (JFolder::exists($path)) {
			JFolder::delete($path);
		}

		$state = $this->extractArchive($tmpQueriesPath, $path);

		if (!$state) {
			$result = new stdClass();
			$result->state = false;
			$result->message = JText::_('COM_EASYSOCIAL_INSTALLATION_ERROR_UNABLE_EXTRACT_QUERIES');

			return $this->output($result);
		}

		// Get the list of files in the folder.
		$queryFiles = JFolder::files($path, '.', false, true, array('.svn', 'CVS', '.DS_Store', '__MACOSX', '.php'));

		// When there are no queries file, we should just display a proper warning instead of exit
		if (!$queryFiles) {
			$result = new stdClass();
			$result->state = true;
			$result->message = JText::_('COM_EASYSOCIAL_INSTALLATION_ERROR_EMPTY_QUERIES_FOLDER');

			return $this->output($result);
		}

		$db = JFactory::getDBO();
		$isMySQL = $this->isMySQL();
		$total = 0;

		foreach ($queryFiles as $file) {
			$contents = file_get_contents($file);
			$queries = $this->splitSql($contents);

			foreach ($queries as $query) {
				$query = trim($query);

				if ($isMySQL && !$this->hasUTF8mb4Support()) {
					$query = $this->convertUtf8mb4QueryToUtf8($query);
				}

				if ($isMySQL && !$this->isMySQL56()) {
					$query = $this->convertDefaultDateValue($query);
				}

				if (!empty($query)) {
					$db->setQuery($query);
					$db->execute();
				}
			}

			$total += 1;
		}

		// due to system plugins, we need to run the new columns on user table here.
		$this->installUserColumns();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_SQL_EXECUTED_SUCCESS', $total);

		return $this->output($result);
	}

	/**
	 * install required columns in user table if not exists
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function installUserColumns()
	{
		$db = JFactory::getDBO();

		$verifiedColumnSql = "ALTER TABLE `#__social_users` ADD `verified` TINYINT(3) NOT NULL DEFAULT '0'";
		$paramColumnSql = "ALTER TABLE `#__social_users` ADD `social_params` LONGTEXT NOT NULL";
		$affiliationColumnSql = "ALTER TABLE `#__social_users` ADD `affiliation_id` VARCHAR(32) NOT NULL AFTER `verified`";
		$robotsColumnSql = "ALTER TABLE `#__social_users` ADD COLUMN `robots` VARCHAR(16) DEFAULT 'inherit'";

		$columns = array(
			'verified' => $verifiedColumnSql,
			'social_params' => $paramColumnSql,
			'affiliation_id' => $affiliationColumnSql,
			'robots' => $robotsColumnSql
		);

		$query = "SHOW FIELDS FROM `#__social_users`";
		$db->setQuery($query);

		$rows = $db->loadObjectList();
		$fields	= array();

		foreach ($rows as $row) {
			$fields[] = $row->Field;
		}

		// do checking here:
		foreach ($columns as $column => $query) {
			$columnExist = in_array($column, $fields);

			// if not exists, lets add this column.
			if (!$columnExist) {
				$db->setQuery($query);
				$this->query($db);
			}
		}

		return true;
	}
}
