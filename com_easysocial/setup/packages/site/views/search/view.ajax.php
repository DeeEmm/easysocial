<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewSearch extends EasySocialSiteView
{
	/**
	 * Renders the additional result set
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadmore($type, $results, $nextlimit, $displayOptions)
	{
		$theme = ES::themes();
		$output = '';

		if (!$results) {
			return $this->ajax->resolve($output, $nextlimit);
		}

		foreach ($results as $result) {

			$output .= $theme->html('listing.' . $result->getType(), $result);
		}

		return $this->ajax->resolve($output, $nextlimit);
	}

	/**
	 * Processes after clicking on a filter for advanced search
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFilterResults($lib, $filter, $searchConfig)
	{
		ES::requireLogin();

		$routerSegment = array();
		$routerSegment['layout'] = 'advanced';

		if ($filter->id) {
			$routerSegment['fid'] = $filter->getAlias();
			$routerSegment['type'] = $filter->element;
		}

		// Get the criteria template
		$lib = ES::advancedsearch($filter->element);
		$criteriaTemplate = $lib->getCriteriaHTML(array('isTemplate' => true));

		$access = ES::access();
		$hasAccessCreateFilter = $access->allowed('search.create.filter');

		$theme = ES::themes();
		$theme->set('lib', $lib);
		$theme->set('type', $filter->element);
		$theme->set('activeFilter', $filter);
		$theme->set('routerSegment', $routerSegment);
		$theme->set('filter', $filter);
		$theme->set('criteriaHTML', $searchConfig['criteria']);
		$theme->set('criteriaTemplate', $criteriaTemplate);
		$theme->set('match', $searchConfig['match']);
		$theme->set('avatarOnly', $searchConfig['avatarOnly']);
		$theme->set('sort', $searchConfig['sort']);
		$theme->set('displayOptions', $searchConfig['displayOptions']);
		$theme->set('results', $searchConfig['results']);
		$theme->set('total', $searchConfig['total']);
		$theme->set('nextlimit', $searchConfig['nextlimit']);
		$theme->set('hasAccessCreateFilter', $hasAccessCreateFilter);

		$contents = $theme->output('site/search/advanced/contents');

		return $this->ajax->resolve($contents);

	}

	/**
	 * Renders the dialog to save the search criterias
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmSaveFilter()
	{
		// Require user to be logged in
		ES::requireLogin();

		$access = ES::access();

		if (!$access->allowed('search.create.filter')) {
			die();
		}

		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'cmd');
		$data = $this->input->get('data', '', 'default');
		$id = $this->input->get('id', 0, 'int');

		// load the filter
		$filter = ES::table('SearchFilter');
		$filter->load($id);

		$theme = ES::themes();
		$theme->set('type', $type);
		$theme->set('data', $data);
		$theme->set('filter', $filter);

		$contents = $theme->output('site/search/dialogs/filter.add');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders confirmation dialog to delete a filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDeleteFilter()
	{
		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);

		$contents = $theme->output('site/search/dialogs/filter.delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the result of pagination on the search page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getItems($query, $data, $filters, $mini, $loadmore = true)
	{
		$showadvancedlink = $this->input->get('showadvancedlink', true, 'bool');

		$theme = ES::themes();
		$theme->set('result', $data->result);
		$theme->set('total', $data->total);
		$theme->set('next_limit', $data->next_limit);
		$theme->set('query', $query);
		$theme->set('showadvancedlink', $showadvancedlink);
		$theme->set('filters', $filters);

		$next_type = '';
		$next_update = '';

		// On toolbar search, we want to generate the search link
		$searchLink = '';

		if ($mini) {
			$linkOptions = array('q' => urlencode($query));

			if (isset($filters) && $filters) {
				for($i = 0; $i < count($filters); $i++) {
					$linkOptions['filtertypes[' . $i . ']'] = $filters[$i];
				}
			}

			$searchLink = ESR::search($linkOptions);
		}

		$theme->set('searchLink', $searchLink);

		$output = '';

		if ($loadmore) {
			$namespace = 'site/search/default/list.result';
		}

		if ($mini) {
			$namespace = 'site/search/mini/default';
		}

		$output = $theme->output($namespace);

		return $this->ajax->resolve($output, $data->next_limit);
	}


	/**
	 * Renders the result of suggested items before proceed with the actual search.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function showSuggestions($query, $data)
	{
		$items = array();

		foreach ($data->suggestion as $item) {

			$text = preg_replace('/(' . $query .')/ims', '<strong>$1</strong>', $item);

			$obj = new stdClass();
			$obj->value = $item;
			$obj->text = $text;

			$items[] = $obj;
		}

		$theme = ES::themes();
		$namespace = 'site/search/mini/suggestion';

		$theme->set('result', $items);
		$theme->set('total', $data->total);
		$theme->set('query', $query);
		$output = $theme->output($namespace);

		return $this->ajax->resolve($output);
	}


	/**
	 * Sends the html codes for operator and conditions
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCriteria($hasKey, $keys, $operators, $condition)
	{
		return $this->ajax->resolve($hasKey, $keys, $operators, $condition);
	}

	/**
	 * Sends the html codes for operator and conditions
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOperators($operators, $condition)
	{
		return $this->ajax->resolve($operators, $condition);
	}

	/**
	 * Sends the html codes for conditions
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getConditions($html)
	{
		return $this->ajax->resolve($html);
	}
}
