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

class EasySocialControllerReports extends EasySocialController
{
	/**
	 * Deletes specific reports
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeItem()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$report = ES::table('Report');
		$report->load($id);

		$state = $report->delete();

		if (!$state) {
			$this->view->setMessage($report->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_REPORT_DELETED', 'report', ['name' => $report->title]);

		// Deduct points from the author when their report is deleted.
		$points = ES::points();
		$points->assign('reports.delete', 'com_easysocial', $report->created_by);

		$this->view->setMessage('COM_EASYSOCIAL_REPORTS_REPORT_ITEM_HAS_BEEN_DELETED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes reports
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$model = ES::model('Reports');

		foreach ($ids as $id) {
			$table = ES::table('Report');
			$table->load((int) $id);


			$reports = $model->getReporters($table->extension, $table->uid, $table->type);

			foreach ($reports as $report) {
				$report->delete();

				// Deduct points from the author when their report is deleted.
				$points = ES::points();
				$points->assign('reports.delete', 'com_easysocial', $report->created_by);
			}

			$this->actionlog->log('COM_ES_ACTION_LOG_REPORT_DELETED_ALL', 'report', ['name' => $table->title]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_REPORTS_REPORT_ITEM_HAS_BEEN_DELETED');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Purge all reports on site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purge()
	{
		ES::checkToken();

		$model = ES::model('Reports');
		$state = $model->purge();

		if (!$state) {
			return $this->view->exception($model->getError());
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_REPORT_PURGED', 'report');

		$this->view->setMessage('COM_EASYSOCIAL_REPORTS_PURGED_SUCCESSFULLY');
		return $this->view->call( __FUNCTION__ );
	}
}
