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
	 * Stores a submitted report
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		// Get data from $_POST
		$post = $this->input->post->getArray();

		if (!$this->my->id && !$this->config->get('reports.guests', false)) {
			return;
		}
		// Determine if this user has the permissions to submit reports.
		$access = ES::access();

		if (!$access->allowed('reports.submit')) {
			return $this->view->exception('COM_EASYSOCIAL_REPORTS_NOT_ALLOWED_TO_SUBMIT_REPORTS', ES_ERROR);
		}

		// Get the reports model
		$model = ES::model('Reports');

		// Determine if this user has exceeded the number of reports that they can submit
		$total = $model->getCount(array('created_by' => $this->my->id));

		if ($access->exceeded('reports.limit' , $total)) {
			$this->view->setMessage('COM_EASYSOCIAL_REPORTS_LIMIT_EXCEEDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Create the report
		$report = ES::table('Report');
		$report->bind($post);

		// Try to get the user's ip address.
		$report->ip = $this->input->server->get('REMOTE_ADDR', '');

		// Set the creator id.
		$report->created_by = $this->my->id;

		// Set the item actor id
		$report->actor_id = isset($post['actorid']) && $post['actorid'] ? $post['actorid'] : 0;

		// Set the default state of the report to new
		$report->state = 0;

		// Try to store the report.
		$state = $report->store();

		// If there's an error, throw it
		if (!$state) {
			$this->view->setMessage($report->getError() , ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Add badge for the author when a report is created.
		ES::badges()->log('com_easysocial', 'reports.create', $this->my->id, 'COM_EASYSOCIAL_REPORTS_BADGE_CREATED_REPORT');

		// Add points for the author when a report is created.
		ES::points()->assign('reports.create', 'com_easysocial', $this->my->id);

		// Determine if we should send an email
		$report->notify();

		$this->view->setMessage('COM_EASYSOCIAL_REPORTS_STORED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}
}
