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

class K2ViewProfile extends SocialAppsView
{
	public function exists()
	{
		$k2File = JPATH_ROOT . '/components/com_k2/helpers/route.php';

		if (!JFile::exists($k2File)) {
			return false;
		}

		// Ensure the component is enabled
		if (!JComponentHelper::isInstalled('com_k2')) {
			return false;
		}

		require_once($k2File);

		return true;
	}

	/**
	 * Displays the application output in the canvas.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display( $userId = null , $docType = null )
	{
		if (!$this->exists()) {
			echo JText::_('K2 is not installed');
			return;
		}

		// Get the app params
		$params = $this->app->getParams();

		$this->setTitle('APP_ARTICLES_APP_TITLE');

		// Get the blog model
		$total = (int) $params->get('total', $params->get('total', 5));

		// Retrieve a list of k2 items
		$model = $this->getModel('Items');
		$items = $model->getItems($userId, $total);
		$pagination = $model->getPagination();
		$totalPosts = $model->getTotalItems($userId);

		$maxContentLength = $params->get('content_length', 350);

		$user = ES::user($userId);

		$this->format($items ,$params);

		$this->set('maxContentLength', $maxContentLength);
		$this->set('total', $totalPosts);
		$this->set('pagination', $pagination);
		$this->set('user', $user);
		$this->set('articles', $items);

		echo parent::display('themes:/site/articles/default');
	}

	/**
	 * Method to display the sidebar through a module
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebar($moduleLib, $user)
	{
		$model = $this->getModel('Items');
		$total = $model->getTotalItems($user->id);

		$this->set('showCreateArticleButton', false);
		$this->set('total', $total);
		$this->set('user', $user);
		$this->set('moduleLib', $moduleLib);

		echo parent::display('themes:/site/articles/sidebar/default');
	}

	private function format(&$items, $params)
	{
		$this->exists();

		if (!$items) {
			return;
		}

		// Add K2's table path
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_k2/tables');

		foreach($items as $item) {
			$category = JTable::getInstance('K2Category', 'Table');
			$category->load($item->catid);

			$item->category = $category;
			$item->category->title = $item->category->name;
			$item->permalink = K2HelperRoute::getItemRoute( $item->id . ':' . $item->alias , $item->catid );
			$item->category->permalink = K2HelperRoute::getCategoryRoute( $category->id . ':' . $category->alias );
			$item->content = empty( $item->introtext ) ? $item->fulltext : $item->introtext;

			$titleLength = $params->get( 'title_length' );
			$contentLength = $params->get('content_length');

			if ($titleLength) {
				$item->title = ESJString::substr($item->title, 0, $titleLength);
			}

			if ($contentLength) {
				$item->content = ESJString::substr(strip_tags($item->content), 0, $contentLength);
			}
		}
	}
}
