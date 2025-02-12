<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/dependencies.php');

class SocialSearch extends EasySocial
{
	public static function factory()
	{
		return new self();
	}

	/**
	 * Determines if finder is enabled
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isFinderEnabled()
	{
		// Determines if finder is enabled
		$enabled = JComponentHelper::isEnabled('com_finder');

		if (!$enabled) {
			return $enabled;
		}

		return $enabled;
	}

	/**
	 * Allows caller to perform a search given the criterias
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function search($query = '', $limitstart = 0, $limit = 20, $filters = array(), $highlight = true, $showSuggest = false)
	{
		$data = new stdClass();
		$data->result = array();
		$data->next_limit = '';
		$data->total = 0;
		$data->suggestion = array();

		if (!$this->isFinderEnabled() || !$query) {
			return $data;
		}

		// we dont want space at left
		$query = ltrim($query);

		// for the right side space, we will replace with + so that
		// smart search know how to process the term.
		$query = rtrim($query, '+');

		// Do not search if minimum characters is enforced
		$length = ESJString::strlen($query);

		if ($this->config->get('search.minimum') && $length < $this->config->get('search.characters')) {
			return $data;
		}

		$searchSuggestion = $this->config->get('search.suggestion');

		$app = JFactory::getApplication();
		$jinput = $app->input;

		// We need to set the query string as Joomla's finder model seems to be looking for these query strings
		$jinput->request->set('t', $filters);

		// Load up finder's model
		$finderModel = new ESFinderModelSearch();
		$state = $finderModel->getState();

		// Get the query
		// this line need to be here. so that the indexer can get the correct value
		$query = $finderModel->getQuery();

		//reset the pagination state.
		$state->{'list.start'} = $limitstart;
		$state->{'list.limit'} = $limit;

		$results = $finderModel->getOutCome();

		$suggestionModel = new ESFinderModelSuggestions();
		$suggestedItems = $suggestionModel->getItems();

		// now instead of auto select the suggested term, we let the user to select.
		# 2815
		if ($searchSuggestion && $showSuggest && $suggestedItems) {
			$data->total = count($suggestedItems);
			$data->suggestion = $suggestedItems;

			return $data;
		}

		// when keyword suggestion disabled and when there is no terms match,
		// check if smart search suggested any terms or not. if yes, lets use it.
		if (!$searchSuggestion && !$results && $suggestedItems) {

			$suggestion = '';

			if ($suggestedItems) {
				// we need to get the shortest terms to search
				$curLength = ESJString::strlen($suggestedItems[0]);
				$suggestion = $suggestedItems[0];

				for($i = 1; $i < count($suggestedItems); $i++) {
					$iLen = ESJString::strlen($suggestedItems[$i]);

					if ($iLen < $curLength) {
						$curLength = $iLen;
						$suggestion = $suggestedItems[$i];
					}
				}

				if ($suggestion) {
					$app = JFactory::getApplication();
					$input = $app->input;
					$input->request->set('q', $suggestion);

					// Load up the new model
					$finderModel = new ESFinderModelSearch();
					$state = $finderModel->getState();

					// this line need to be here. so that the indexer can get the correct value
					$query = $finderModel->getQuery();
				}
			}

			if (!$suggestion && isset($query->included) && count($query->included) > 0) {

				foreach($query->included as $item) {
					if (isset($item->suggestion) && !empty($item->suggestion)) {
						$suggestion = $item->suggestion;
					}
				}

				if ($suggestion) {
					$jinput->request->set('q', $suggestion);

					// Load up the new model
					$finderModel = new ESFinderModelSearch();
					$state = $finderModel->getState();

					// this line need to be here. so that the indexer can get the correct value
					$query = $finderModel->getQuery();
				}
			}

			//reset the pagination state.
			$state->{'list.start'} = $limitstart;
			$state->{'list.limit'} = $limit;

			$results = $finderModel->getOutCome();
		}

		$data->total = $finderModel->getTotal();
		$pagination = $finderModel->getPagination();

		if ($results) {

			$data->result = $this->format($results, $query, $highlight);

			$query = $finderModel->getQuery();

			if (ES::isJoomla30()) {
				$pagination->{'pages.total'} = $pagination->pagesTotal;
				$pagination->{'pages.current'} = $pagination->pagesCurrent;
			}

			if ($pagination->{'pages.total'} == 1 || $pagination->{'pages.total'} == $pagination->{'pages.current'}) {
				$data->next_limit = '-1';
			} else {
				$data->next_limit = $pagination->limitstart + $pagination->limit;
			}
		}

		return $data;
	}

	/**
	 * Retrieves a list of known internal filters
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getInternalFilters()
	{
		return array(SOCIAL_INDEXER_TYPE_USERS, SOCIAL_INDEXER_TYPE_PHOTOS, SOCIAL_INDEXER_TYPE_ALBUMS, SOCIAL_INDEXER_TYPE_GROUPS, SOCIAL_INDEXER_TYPE_EVENTS, SOCIAL_INDEXER_TYPE_PAGES, SOCIAL_INDEXER_TYPE_VIDEOS, SOCIAL_INDEXER_TYPE_AUDIOS, SOCIAL_INDEXER_TYPE_MARKETPLACES);
	}

	public function getTaxonomyID( $type )
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$query = "select id from `#__finder_taxonomy`";
		$query .= " where `access` IN ( $groups )";
		$query .= " and `state` = 1";
		$query .= " and `title` = '$type'";

		$sql->raw( $query );

		$db->setQuery( $sql );

		return $db->loadResult();
	}

	/**
	 * Deprecated. Use @getFilters
	 *
	 * @deprecated	2.0
	 * @access	public
	 */
	public function getTaxonomyTypes($type = null)
	{
		return $this->getFilters($type);
	}

	/**
	 * Generates the list of available filters that the user can search
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFilters($type = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the list of groups the user belongs to
		$groups = implode(',', $this->my->getAuthorisedViewLevels());

		$query = "select distinct a.* from `#__finder_taxonomy` as a";
		$query .= " 	inner join `#__finder_taxonomy_map` as b on a.`id` = b.`node_id`";
		$query .= " 	inner join `#__finder_links` as c on c.`link_id` = b.`link_id`";
		$query .= " where a.`access` IN ( $groups )";
		$query .= " and a.`state` = 1";
		$query .= " and a.`parent_id` = ( select id from `#__finder_taxonomy` where `parent_id` = 1 and `title` = 'Type' )";

		if ($type) {
			$query .= ' AND a.`title`=' . $db->Quote($type);
		} else {
			$query .= ' AND a.`title` IN (select `title` FROM `#__finder_types`)';
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$filters = $db->loadObjectList();

		if (!$filters) {
			return $filters;
		}

		foreach ($filters as $filter) {

			$key = str_ireplace('EasySocial.', '', $filter->title);

			// These following items are unique
			if ($key == 'Users') {
				$key = 'People';
			}

			if ($key == 'EasyBlog') {
				$key = 'Blog';
			}

			if ($key == 'EasyDiscuss') {
				$key = 'Discuss';
			}

			// Double check if the key contain space then have to replace to underscore
			$key = str_ireplace(' ', '_', $key);

			$languageKey = 'COM_EASYSOCIAL_SEARCH_TYPE_' . strtoupper($key);

			$filter->displayTitle = JText::_($languageKey);
			$filter->alias = $filter->id . '-' . strtolower($key);

			if (stristr($filter->displayTitle, 'COM_EASYSOCIAL_SEARCH_TYPE_') !== false) {
				$filter->displayTitle = JText::_($filter->title);
			}
		}

		return $filters;
	}

	public function formatMini( $results, $q, $highlight = true )
	{
		$config = ES::config();
		$data = array();

		if( $results )
		{

			$searchRegex = '';
			$hlword = $q;
			if ($hlword) {
				$searchRegex = '#(';
				$searchRegex .= preg_quote($hlword, '#');
				$searchRegex .= '(?!(?>[^<]*(?:<(?!/?a\b)[^<]*)*)</a>))#iu';
			}


			foreach( $results as $row )
			{
				$obj 	= new SocialSearchItem();

				$obj->link	= FRoute::search(array('q' => urlencode($row)));

				//lets process the content and title highlight
				$title	= $row;

				if ($highlight) {
					if ($title) {
						$title		= preg_replace($searchRegex, '<span class="highlight">\0</span>', $title);
					}
				}
				$obj->title		= $title;

				$data[] = $obj;
			}
		}

		return $data;
	}

	/**
	 * Formats the search result
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function format($results, $query, $highlight = true)
	{
		$data = array();
		$userBlockedYou = array();

		//get all users who blocked the current logged in user.
		if (ES::isBlockEnabled($this->my)) {
			$model = ES::model('Blocks');
			$userBlockedYou = $model->getUsersBlocked($this->my->id, true);
		}

		if (!$results) {
			return $data;
		}


		$excludeCnt = 0;

		// Load up the model
		$indexerModel = ES::model('Indexer');

		// Retrieve a list of supported types
		$allowedTypes = $this->getInternalFilters();

		foreach ($results as $row) {

			if (!method_exists($row, 'getTaxonomy')) {
				continue;
			}

			$itemType = array_values($row->getTaxonomy('Type'));
			$itemType = $itemType[0];

			$group = '';
			$creatorColumn = 'created_by';
			$objAdapter = null;

			// Get the known groups
			$internal = array('easysocial.albums', 'easysocial.groups', 'easysocial.photos', 'easysocial.users', 'easysocial.events', 'easysocial.videos', 'easysocial.audios', 'easysocial.pages', 'easysocial.marketplaces');

			// Default
			$group = $itemType->title;

			if (in_array(strtolower($itemType->title), $internal)) {
				$group = str_ireplace('EasySocial.', '', $itemType->title);
			} else {

				if ($itemType->title == 'EasyDiscuss') {
					$group = JText::_('COM_EASYSOCIAL_SEARCH_TYPE_DISCUSS');
				}

				if ($itemType->title == 'EasyBlog') {
					$group = JText::_('COM_EASYSOCIAL_SEARCH_TYPE_BLOG');
				}
			}

			$group = strtolower($group);

			$obj = new SocialSearchItem();
			$obj->finder = $row;

			// remove the cli segment incase the indexing was perform using cli method.
			$tmp = ltrim(JPATH_ROOT,'/');
			$tmp = rtrim($tmp,'/');
			$tmp = $tmp . '/cli/';

			$row->route = str_replace($tmp, '', $row->route);

			// Check if this site support multilanguage
			$multilang = JLanguageMultilang::isEnabled();

			if (!is_null($objAdapter) ) {
				$obj->link = $objAdapter->getPermalink();
			} else {
				if (in_array(strtolower($itemType->title), $internal)) {

					$tmpUrl = str_ireplace('index.php?', '', $row->url);

					parse_str($tmpUrl, $vars);

					$id = (int) $vars['id'];

					switch (strtolower($itemType->title)) {
						case 'easysocial.videos':

							$table = ES::table('Video');
							$table->load($id);
							$video = ES::video($table);
							$obj->link = $video->getPermalink();
							break;

						case 'easysocial.albums':
							$album = ES::table('Album');
							$album->load($id);
							$obj->link = $album->getPermalink();
							break;

						case 'easysocial.photos':
							$photo = ES::table('Photo');
							$photo->load($id);
							$obj->link = $photo->getPermalink();
							break;

						case 'easysocial.users':
							$user = ES::user($id);
							$obj->link = $user->getPermalink();
							break;

						case 'easysocial.audios':

							$table = ES::table('Audio');
							$table->load($id);
							$audio = ES::audio($table);
							$obj->link = $audio->getPermalink();
							break;

						case 'easysocial.pages':

							$page = ES::page($id);
							$obj->link = $page->getPermalink();
							break;

						case 'easysocial.groups':

							$esgroup = ES::group($id);
							$obj->link = $esgroup->getPermalink();
							break;

						case 'easysocial.events':

							$event = ES::event($id);
							$obj->link = $event->getPermalink();
							break;

						case 'easysocial.marketplaces':

							$listing = ES::marketplace($id);
							$obj->link = $listing->getPermalink();
							break;

						default:
							$view = $vars['view'];
							unset($vars['view']);
							unset($vars['option']);

							$obj->link = call_user_func_array(array('ESR', $view), array($vars));
							break;

					}

				} else {
					$obj->link = JRoute::_($row->route);
				}
			}

			// ensure there is a leading slash provided there is no protocol in the url
			if (stristr($obj->link, 'http://') === false && stristr($obj->link, 'https://') === false) {
				$obj->link = '/'. ltrim($obj->link,'/');
			}

			$obj->image = '';
			$obj->utype = $itemType->title;
			$obj->id = $row->link_id;
			$obj->uid = $row->id;
			$obj->type_id = $row->type_id;

			$image = '';
			$checkBlockedUserId = '';

			// let check if this item contain image param or not
			if ($row->params && is_object($row->params)) {
				$image 	= $row->params->get('image','');
				$checkBlockedUserId = $row->getElement($creatorColumn);
			}

			if (ES::isBlockEnabled($this->my) && $userBlockedYou) {
				if ($checkBlockedUserId && in_array($checkBlockedUserId, $userBlockedYou)) {
					$excludeCnt++;
					// lets skip this item.
					continue;
				}
			}

			if ($group == 'users' && $obj->uid) {

				$user = ES::user($obj->uid);

				// since we knwo this is a user object, we need to get the user permalink properly.
				// Check if the easysocialurl system plugin is enabled or not.
				if (JPluginHelper::isEnabled('system', 'easysocialurl')) {
					$obj->link = $user->getPermalink();
				}

				// Depend on the setting if allow admin search ESAD users so the ESAD result will be appear.
				if (!($this->my->isSiteAdmin()) && !$user->hasCommunityAccess()) {
					$excludeCnt++;
					continue;
				}
			}

			// try to get any images from the body.
			// @rule: Match images from blog post
			if (!$image) {
				$pattern = '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';
				preg_match( $pattern , $row->body , $matches );

				$image = '';

				if ($matches) {
					$image = isset( $matches[1] ) ? $matches[1] : '';

					if (ESJString::stristr( $matches[1], 'https://' ) === false && ESJString::stristr($matches[1], 'http://') === false && !empty($image)) {
						$image	= rtrim(JURI::root(), '/') . '/' . ltrim( $image, '/');
					}
				}

				//let give a default image icons.
				if (!$image) {
					$image	= rtrim(JURI::root(), '/') . '/media/com_easysocial/images/defaults/search/large.png';
				}
			}

			$obj->image = $image;

			// Lets process the content and title highlight
			$title = $row->title;
			$content = $row->description ? $row->description : '';
			$content = JHtml::_('string.truncate', $row->description, 255);

			if ($highlight && $query->highlight) {

				if ($title) {
					$title = $this->highlight($query->highlight, $title);
				}

				if ($content) {
					$content = $this->highlight($query->highlight, $content);
				}
			}

			$obj->title = $title;
			$obj->content = $content;

			// This is where we group the items up
			if (!isset($data[$group])) {

				$item = new stdClass();
				$item->title = JText::_('COM_EASYSOCIAL_SEARCH_GROUP_' . $group);
				$item->namespace = in_array($group, $allowedTypes) ? $group : 'other';

				if (strpos($item->title, 'COM_EASYSOCIAL_SEARCH_GROUP_') !== false) {
					$item->title = ucfirst($group);
				}

				$item->result = array();
				$data[$group] = $item;
			}

			$obj->groupTitle = $data[$group]->title;
			$data[$group]->result[] = $obj;
		}

		if ($excludeCnt) {
			$data['excludeCnt'] = $excludeCnt;
		}


		return $data;
	}

	public function highlight($terms, $string)
	{
		static $patterns = array();

		$key = implode('.', $terms);

		if (!isset($patterns[$key])) {
			$pattern = '#(';
			$x = 0;

			foreach ($terms as $key => $word) {
				$pattern .= ($x == 0 ? '' : '|');
				$pattern .= preg_quote($word, '#');
				$x++;
			}

			$pattern .= '(?!(?>[^<]*(?:<(?!/?a\b)[^<]*)*)</a>))#iu';

			$patterns[$key] = $pattern;
		}

		$string = preg_replace($patterns[$key], '<span class="highlight">\0</span>', $string);

		return $string;
	}

	public function validateFilters($filterTypes, $filters = array())
	{
		// lets give default checked value for each filterTypes.
		for($i = 0; $i < count($filterTypes); $i++) {
			$item =& $filterTypes[$i];
			$item->checked = false;
		}

		if ($filters) {
			foreach($filters as $filter) {
				//need to check against each of the filter types
				foreach($filterTypes as $filterType) {
					if ($filterType->id == (int) $filter) {
						$filterType->checked = true;
						break;
					}
				}
			}
		}
	}

	/**
	 * Delete all url that related with the alias
	 *
	 * @since	2.2.4
	 * @access	public
	 */
	public function deleteFromSmartSearch($alias)
	{
		// Load the base adapter.
		$finderLibFile = JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

		if (!JFile::exists($finderLibFile)) {
			return;
		}

		require_once $finderLibFile;

		$model = ES::model('Search');
		$items = $model->getSmartSearchRecords($alias);

		if (!$items) {
			return true;
		}

		$indexer = FinderIndexer::getInstance();

		// Let the library remove the link
		foreach ($items as $item) {
			$indexer->remove($item);
		}

		return true;
	}
}
