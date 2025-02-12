<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelArticles extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('articles', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('filter', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');

		$this->setState('filter', $filter);

		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Retrieves the list of articles for the back end
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getItems($options = array())
	{
		$sql = $this->db->sql();

		$filter = $this->getState('filter');

		$simplifiedResult = isset($options['simplifiedResult']) ? $options['simplifiedResult'] : false;

		$sql->select('#__content');

		if ($simplifiedResult) {
			$sql->column('id');
			$sql->column('title');
		}

		if (isset($options['search']) && $options['search']) {
			$search = $options['search'];

			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		if (!$result) {
			return $result;
		}

		return $result;
	}
}