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

class EasySocialViewSearchAdvancedHelper extends EasySocial
{
	/**
	 * Retrieves the active criterias
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCriterias()
	{
		static $criterias = null;

		if (is_null($criterias)) {
			$criterias = $this->input->get('criterias', '', 'default');
		}

		return $criterias;
	}

	/**
	 * Retrieves the active filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$activeFilter = false;

			$criterias = $this->getActiveCriterias();
			$filterId = $this->input->get('fid', 0, 'int');

			if ($filterId) {
				$activeFilter = ES::table('SearchFilter');
				$activeFilter->load($filterId);
			}
		}

		return $activeFilter;
	}

	/**
	 * Determines the current active type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveType()
	{
		static $type = null;

		if (is_null($type)) {
			// Advanced search type
			$type = $this->input->get('type', SOCIAL_FIELDS_GROUP_USER, 'default');

			$activeFilter = $this->getActiveFilter();

			if ($activeFilter) {
				$type = $activeFilter->element;
			}
		}

		return $type;
	}

	/**
	 * Retrieves the advanced search adapters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAdapters()
	{
		static $adapters = null;

		if (is_null($adapters)) {
			$lib = $this->getLibrary();
			$adapters = $lib->getAdapters();
		}

		return $adapters;
	}

	/**
	 * Generates the create new search filter link
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCreateLink()
	{
		static $link = null;

		if (is_null($link)) {
			$lib = $this->getLibrary();

			$link = $lib->getLink();
		}

		return $link;
	}

	/**
	 * Generates a list of filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilters()
	{
		static $filters = null;

		if (is_null($filters)) {
			$filters = array_merge($this->getUserFilters(), $this->getSiteWideFilters());
		}

		return $filters;
	}

	/**
	 * Retrieves the advanced search library
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLibrary()
	{
		static $lib = null;

		if (is_null($lib)) {
			$lib = ES::advancedsearch($this->getActiveType());
		}

		return $lib;
	}

	/**
	 * Retrieves the list of user filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getSiteWideFilters()
	{
		static $filters = null;

		if (is_null($filters)) {
			$model = ES::model('Search');
			$filters = $model->getSiteWideFilters($this->getActiveType());
		}

		return $filters;
	}

	/**
	 * Retrieves the list of user filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUserFilters()
	{
		static $filters = null;

		if (is_null($filters)) {
			$model = ES::model('Search');
			$filters = $model->getFilters($this->getActiveType(), $this->my->id, false);
		}

		return $filters;
	}

	/**
	 * Determines if the current active type is supported
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function isSupportedType()
	{
		$active = $this->getActiveType();
		$supported = [
			SOCIAL_TYPE_PAGE,
			SOCIAL_TYPE_USER,
			SOCIAL_TYPE_EVENT,
			SOCIAL_TYPE_GROUP,
			SOCIAL_TYPE_MARKETPLACE
		];

		if (!in_array($active, $supported)) {
			return false;
		}

		return true;
	}

	/**
	 * Normalise user search data filters.
	 *
	 * @since	3.2.5
	 * @access	public
	 */
	public function normalizeUserData($searchConfig)
	{
		$profile = $this->input->get('profile', 0, 'int');
		$clusterCategory = $this->input->get('clusterCategory', 0, 'int');

		$newSearchConfig = $searchConfig;

		// these two are used in custom field search module.
		if ($profile || $clusterCategory) {

			$criterias = $searchConfig['criterias'];
			$datakeys = $searchConfig['datakeys'];
			$operators = $searchConfig['operators'];
			$conditions = $searchConfig['conditions'];

			$total = count($criterias);

			$newItems = array('criterias' => array(),
							'datakeys' => array(),
							'operators' => array(),
							'conditions' => array()
						);

			if ($total > 0) {
				for($i = 0; $i < $total; $i++) {
					$criteria = explode('|', $criterias[$i]);
					$operator = $operators[$i];
					$datakey = $datakeys[$i];
					$cond = $conditions[$i];

					$fcode = $criteria[0];
					$ftype = $criteria[1];

					// $allowBlankOperators = array('blank', 'notblank', 'notequal', 'notcontain');
					$allowBlankOperators = array('blank', 'notblank');

					// if (($ftype == 'boolean' && $operator == 'equal' && $cond === '') || 
					// 	($ftype != 'boolean' && !in_array($operator, $allowBlankOperators) && $cond === '')) {
					// 	continue;
					// }

					if ($cond === '') {
						continue;
					}

					if ($ftype == 'checkbox' && $cond && strpos($cond, '|') !== false) {
						// we need to split the values and add a criteria for each of the condition.
						$arrConds = explode('|', $cond);

						for($c = 0; $c < count($arrConds); $c++) {
							$newItems['criterias'][] = $criterias[$i];
							$newItems['datakeys'][] = $datakeys[$i];
							$newItems['operators'][] = $operators[$i];
							$newItems['conditions'][] = $arrConds[$c];
						}
					} else {
						$newItems['criterias'][] = $criterias[$i];
						$newItems['datakeys'][] = $datakeys[$i];
						$newItems['operators'][] = $operators[$i];
						$newItems['conditions'][] = $conditions[$i];
					}
				}

				$newSearchConfig['criterias'] = $newItems['criterias'];
				$newSearchConfig['datakeys'] = $newItems['datakeys'];
				$newSearchConfig['operators'] = $newItems['operators'];
				$newSearchConfig['conditions'] = $newItems['conditions'];
			}
		}

		return $newSearchConfig;
	}
}
