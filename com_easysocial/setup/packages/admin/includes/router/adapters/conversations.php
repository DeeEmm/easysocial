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

/**
 * Component's router for conversations view.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterConversations extends SocialRouterAdapter
{
	/**
	 * Construct's the conversations url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If there is no active menu for friends, we need to add the view.
		if ($menu && $menu->query['view'] != 'conversations') {
			$segments[]	= $this->translate($query['view']);
		}

		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}
		unset($query['view']);

		$type = isset($query['type']) ? $query['type'] : null;
		if (!is_null($type)) {
			$segments[] = $this->translate('conversations_type_' . $type);
			unset($query['type']);
		}

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[]	= $this->translate('conversations_layout_' . $layout);
			unset($query['layout']);
		}

		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id)) {
			$segments[]	= $id;
			unset($query['id']);
		}

		$fileId = isset($query['fileid']) ? $query['fileid'] : null;

		if (!is_null($fileId)) {
			$segments[]	= $fileId;
			unset($query['fileid']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	An array of url segments
	 * @return	array 	The query string data
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		// URL: http://site.com/menu/conversations
		if ($total == 1) {
			$vars['view'] = 'conversations';
			return $vars;
		}

		// URL: http://site.com/menu/conversations/archives
		if ($total == 2 && (str_ireplace(':' , '-' , $segments[1]) == $this->translate('conversations_type_archives') || $segments[1] == $this->translate('conversations_type_archives'))) {
			$vars['view'] = 'conversations';
			$vars['type'] = 'archives';
			return $vars;
		}

		// URL: http://site.com/menu/conversations/compose
		if ($total == 2 && $segments[1] == $this->translate('conversations_layout_compose')) {
			$vars['view'] = 'conversations';
			$vars['layout'] = 'compose';
			return $vars;
		}

		// URL:  http://site.com/menu/conversations/1
		if ($total == 2 && $segments[1]) {
			$vars['view'] = 'conversations';
			$vars['id'] = $segments[1];
			return $vars;
		}

		// URL: http://site.com/menu/conversations/download/ID
		if ($total == 3 && $segments[1] == $this->translate('conversations_layout_download')) {
			$vars['view'] = 'conversations';
			$vars['layout'] = 'download';
			$vars['fileid'] = $segments[2];
			return $vars;
		}

		// URL: http://site.com/menu/conversations/download/ID
		if ($total == 3 && $segments[1] == $this->translate('conversations_layout_preview')) {
			$vars['view'] = 'conversations';
			$vars['layout'] = 'preview';
			$vars['fileid'] = $segments[2];
			return $vars;
		}

		return $vars;
	}
}
