<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerPoints extends EasySocialController
{
	/**
	 * Since achievers are paginated, this allows retrieving more achievers
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function loadAchievers()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$start = $this->input->get('start', 0, 'int');

		$limit = ES::getLimit('points');

		$options = [
					'start' => $start,
					'limit' => $limit
					];

		$model = ES::model('Points');
		$achievers = $model->getAchievers($id, $options);
		$nextlimit = $model->getNextLimit();

		$this->view->call(__FUNCTION__, $achievers, $nextlimit);
	}

	/**
	 * Since achievers are paginated, this allows retrieving more achievers
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getAchieversCount()
	{
		ES::checkToken();

		$ids = $this->input->get('ids', array(), 'array');

		$data = array();

		if ($ids) {
			foreach ($ids as $id) {

				$id = (int) $id;

				if ($id) {

					$table = ES::table('Points');
					$table->load($id);
					$count = $table->getTotalAchievers();

					$obj = new stdClass();
					$obj->id = $id;
					$obj->count = $count;

					$data[] = $obj;
				}
			}
		}

		$this->view->call(__FUNCTION__, $data);
	}
	
}