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

class EasySocialViewReports extends EasySocialAdminView
{
	/**
	 * Requests confirmation before purging reports
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmPurge()
	{
		$theme = ES::themes();
		$html = $theme->output('admin/reports/dialogs/purge');

		return $this->ajax->resolve($html);
	}

	/**
	 * Display confirmation before deleting a report
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$output	= $theme->output('admin/reports/dialogs/delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Generates a list of reports for an object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getReporters($reporters = array())
	{
		// Get the report id.
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->exception();
		}

		$report = ES::table('Report');
		$report->load($id);

		$model = ES::model('Reports');
		$reporters = $model->getReporters($report->extension, $report->uid, $report->type);

		$theme = ES::themes();
		$theme->set('reporters' , $reporters);

		$contents = $theme->output('admin/reports/dialogs/reporters');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after a report has been deleted.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeItem()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve($this->getMessage());
	}
}
