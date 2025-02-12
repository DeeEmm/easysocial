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

function addPrefix(&$title, $prefix)
{
	$title[] = $prefix;
}

// Determine how is the user's current id being set.
function addView(&$title, $view, $menuView = '')
{
	$check = checkAddView();
	$add = false;

	if ($check) {
		// must add view segment.
		$add = true;
	} else {
		if ($menuView) {
			if ($view != $menuView) {
				$add = true;
			}
		} else {
			$add = true;
		}
	}

	if ($add) {
		$title[] = ESJString::ucwords(JText::_('COM_EASYSOCIAL_ROUTER_' . strtoupper($view)));
	}

	shRemoveFromGETVarsList('view');
}

function addLayout(&$title , $view , $layout, $Itemid = null)
{
	$activeMenu = JFactory::getApplication()->getMenu()->getActive();
	if ($Itemid) {
		$activeMenu = JFactory::getApplication()->getMenu('site')->getItem($Itemid);
	}

	$add = ($activeMenu && isset($activeMenu->query['view']) && isset($activeMenu->query['layout']) && $activeMenu->query['view'] == $view && $activeMenu->query['layout'] == $layout) ? false : true;

	if ($add) {
		$title[] = ESJString::ucwords(JText::_('COM_EASYSOCIAL_ROUTER_' . strtoupper($view) . '_LAYOUT_' . strtoupper($layout)));
	}

	shRemoveFromGETVarsList('layout');
}

function stripExtensions($title)
{
	// Remove known extensions from title
	$extensions = array('jpg' , 'png' , 'gif');

	$title 	= ESJString::str_ireplace($extensions , '' , $title);

	return $title;
}

function getAppAlias($id)
{
	static $_cache = array();

	// somehow somewhere is passing invalid characters
	$test = (int) $id;
	if (!$test) {
		return $id;
	}

	if (!isset($_cache[$id])) {

		$app = ES::table('App');
		$app->load((int) $id);

		$alias = JFilterOutput::stringURLSafe($app->alias);
		$alias = ESR::normalizePermalink($alias);

		$_cache[$id] = $alias;
	}

	return $_cache[$id];
}

function getListAlias($id)
{
	static $_cache = array();

	if (!isset($_cache[$id])) {

		$list = ES::table('List');
		$list->load($id);

		$alias = JFilterOutput::stringURLSafe($list->title);
		$alias = ESR::normalizePermalink($alias);

		$_cache[$id] = $alias;
	}

	return $_cache[$id];
}

function getBadgeAlias($id)
{
	static $_cache = array();

	if (!isset($_cache[$id])) {

		$badge = ES::table('Badge');
		$badge->load($id);

		$alias = JFilterOutput::stringURLSafe($badge->alias);
		$alias = ESR::normalizePermalink($alias);

		$_cache[$id] = $alias;
	}

	return $_cache[$id];
}

function getAudioGenreAlias($id)
{
	static $genres = array();

	if (!isset($genres[$id])) {

		$id = (int) $id;

		$genre = ES::table('AudioGenre');
		$genre->load($id);

		$alias = ESR::normalizePermalink($genre->alias);
		$genres[$id] = ESJString::ucwords($alias);
	}

	return $genres[$id];
}

function getVideoCategoryAlias($id)
{
	static $cats = array();

	if (!isset($cats[$id])) {

		$id = (int) $id;

		$category = ES::table('VideoCategory');
		$category->load($id);

		$alias = ESR::normalizePermalink($category->alias);
		$cats[$id] = ESJString::ucwords($alias);
	}

	return $cats[$id];
}

function getPageCategoryAlias($id)
{
	static $categories 	= array();

	// Ensure that the id is purely an integer
	if (!isset($categories[$id])) {

		$category 	= ES::table('PageCategory');
		$category->load($id);

		$alias = $category->getAlias();
		$alias = str_ireplace(':', '-', $alias);

		$categories[$id] = ESR::normalizePermalink($alias);
	}

	return $categories[$id];
}


function getGroupCategoryAlias($id)
{
	static $categories 	= array();

	// Ensure that the id is purely an integer
	if (!isset($categories[$id])) {

		$category = ES::table('GroupCategory');
		$category->load($id);

		$alias = $category->getAlias();
		$alias = str_ireplace(':', '-', $alias);

		$categories[$id] = ESR::normalizePermalink($alias);
	}

	return $categories[$id];
}

function getEventCategoryAlias($id)
{
	static $categories = array();

	// Ensure that the id is purely an integer
	if (!isset($categories[$id])) {

		$category = ES::table('EventCategory');
		$category->load($id);

		$alias = $category->getAlias();
		$alias = str_ireplace(':', '-', $alias);


		$categories[$id] = ESR::normalizePermalink($alias);
	}

	return $categories[$id];
}

function getAudioAlias($id)
{
	$id = (int) $id;

	static $audios = array();

	if (!isset($audios[$id])) {
		$audio = ES::table('Audio');
		$audio->load($id);

		$alias = $audio->getAlias();
		$alias = str_ireplace(':', '-', $alias);
		$alias = ESR::normalizePermalink($alias);

		$audios[$id] = ESJString::ucwords($alias);
	}

	return $audios[$id];
}

function getVideoAlias($id)
{
	$id = (int) $id;

	static $videos = array();

	if (!isset($videos[$id])) {
		$video = ES::table('Video');
		$video->load($id);

		$alias = $video->getAlias();
		$alias = str_ireplace(':', '-', $alias);
		$alias = ESR::normalizePermalink($alias);

		$videos[$id] = ESJString::ucwords($alias);
	}

	return $videos[$id];
}

function getGroupAlias($id)
{
	static $groups 	= array();

	// Ensure that the id is purely an integer
	if (!isset($groups[$id])) {
		$group 	= ES::group($id);
		// We need to replace : with - since SH404 correctly processes it.
		$alias 	= $group->getAlias();
		$alias 	= str_ireplace(':', '-', $alias);

		$groups[$id] = ESR::normalizePermalink($alias);
	}

	return $groups[$id];
}

function getPageAlias($id)
{
	static $pages 	= array();

	// Ensure that the id is purely an integer
	if (!isset($pages[$id])) {
		$page 	= ES::page($id);
		// We need to replace : with - since SH404 correctly processes it.
		$alias 	= $page->getAlias();
		$alias 	= str_ireplace(':', '-', $alias);

		$pages[$id]	= ESR::normalizePermalink($alias);
	}

	return $pages[$id];
}

function getProfileAlias($id)
{
	static $profiles 	= array();

	// Ensure that the id is purely an integer
	if (!isset($profiles[$id])) {
		$profile  = ES::table('Profile');
		$profile->load($id);
		// We need to replace : with - since SH404 correctly processes it.
		$alias 	= $profile->getAlias();
		$alias 	= str_ireplace(':', '-', $alias);

		$profiles[$id]	= ESR::normalizePermalink($alias);
	}

	return $profiles[$id];
}

function getEventAlias($id)
{
	static $events 	= array();

	// Ensure that the id is purely an integer
	if (!isset($events[$id])) {
		$event = ES::event($id);
		// We need to replace : with - since SH404 correctly processes it.
		$alias = $event->getAlias();
		$alias = str_ireplace(':', '-', $alias);

		$events[$id] = ESR::normalizePermalink($alias);
	}

	return $events[$id];
}

function getUserAlias($id)
{
	static $users 	= array();

	$id = (int) $id;

	if (!isset($users[$id])) {
		$user = ES::user($id);
		$alias = $user->getAlias();

		$users[$id] = ESR::normalizePermalink($alias);
	}

	return $users[$id];
}

function uniqueUrl($title , $fragment)
{
	$i 	= 1;

	$url = implode('/' , $title) . '/' . $fragment;

	while (urlExists($url)) {
		$fragment = $fragment . '-' . $i;

		$url = $url . $fragment;
		$i++;
	}

	return $fragment;
}

function urlExists($title)
{
	$url = $title;

	if (is_array($title)) {
		$url = implode('/' , $title);
	}

	$db = ES::db();
	$sql = $db->sql();
	$sql->select('#__sh404sef_urls');
	$sql->where('oldurl' , $url , '=' , 'OR');
	$sql->where('oldurl' , $url . '.html' , '=' , 'OR');

	$db->setQuery($sql);

	$exists	= $db->loadResult() > 0 ? true : false;

	return $exists;
}

function checkAddView()
{
	// check if it is a must to add view segment or not.
	static $addView = null;

	if (is_null($addView)) {

		// sh404sef version
		$sefConfig = Sh404sefFactory::getConfig();
		$shVersions = explode('.', $sefConfig->version);
		$shVersion = $shVersions[0] . '.' . $shVersions[1];

		// php version
		$phpVersion = phpversion();

		if (version_compare($shVersion, '4.6', '<=') && version_compare($phpVersion, '7.0.0') >= 0) {
			$addView = true;
		} else {
			$addView = false;
		}
	}

	return $addView;
}


function isEasysocialURLPluginEnabled()
{
	$isEasySocialUrlPluginInstalled = JPluginHelper::getPlugin('system', 'easysocialurl');
	$isEasySocialUrlPluginEnabled = JPluginHelper::isEnabled('system', 'easysocialurl');

	if ($isEasySocialUrlPluginInstalled && $isEasySocialUrlPluginEnabled) {
		return true;
	}

	return false;
}

