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

class ArticleViewProfile extends SocialAppsView
{
	/**
	 * Displays the list of articles the user created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($userId = null, $docType = null)
	{
		// Get the user params
		$params = $this->getUserParams($userId);
		$appParams	= $this->app->getParams();

		$this->setTitle('APP_ARTICLES_APP_TITLE');

		// Get the blog model
		$total = (int) $params->get('total', $appParams->get('total', 5));

		// Get list of blog posts created by the user on the site.
		$model = $this->getModel('Article');
		$articles = $model->getItems($userId, $total);
		$pagination = $model->getPagination();

		$user = ES::user($userId);
		$total = $model->getTotalArticles($userId);

		$maxContentLength = $appParams->get('content_length', 350);

		$this->format($articles, $appParams);

		$this->set('maxContentLength', $maxContentLength);
		$this->set('pagination', $pagination);
		$this->set('total', $total);
		$this->set('user', $user);
		$this->set('articles', $articles);

		echo parent::display('themes:/site/articles/default');
	}

	/**
	 * A sidebar function through a module
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebar($moduleLib, $user)
	{
		$model = $this->getModel('Article');
		$total = $model->getTotalArticles($user->id);

		// Determine whether need to show create article button on the page.
		$showCreateArticleButton = $this->canCreateArticle($user);

		$this->set('moduleLib', $moduleLib);
		$this->set('total', $total);
		$this->set('user', $user);
		$this->set('showCreateArticleButton', $showCreateArticleButton);

		echo parent::display('themes:/site/articles/sidebar/default');
	}

	/**
	 * Determine the create article button should show or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function canCreateArticle($user)
	{
		// determine the current viewer is the profile user or not
		$isViewer = $user->isViewer();

		// current viewer
		$my = ES::user();

		// if the viewer has permission to create article and got permission to create in one of the category.
		$authorised = $my->authorise('core.create', 'com_content') || count($my->getAuthorisedCategories('com_content', 'core.create'));

		if ($isViewer && $authorised) {
			return true;
		}

		return false;
	}

	/**
	 * Format the article content
	 *
	 * @since	2.0
	 * @access	private
	 */
	private function format(&$articles, $params)
	{
		if (!$articles) {
			return;
		}

		foreach ($articles as $article) {
			$category = JTable::getInstance('Category');
			$category->load($article->catid);

			$article->category = $category;
			$article->permalink = JRoute::_(ESContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid));

			$article->category->permalink = JRoute::_(ESContentHelperRoute::getCategoryRoute($category->id . ':' . $category->alias));

			$article->content = empty($article->introtext) ? $article->fulltext : $article->introtext;

			$titleLength = $params->get('title_length');


			if ($titleLength) {
				$article->title = ESJString::substr($article->title, 0, $titleLength);
			}

			$article->content = $this->normalizeContent($article->content);

			// Try to get image of the article
			$image = false;

			if ($article->images) {
				$data = json_decode($article->images);
				if (isset($data->image_intro) && $data->image_intro) {
					$article->image = rtrim(JURI::root(), '/') .  '/' . $data->image_intro;
				}
			}

			if (!isset($article->image)) {
				$image = $this->processContentImage($article->content);

				if ($image) {
					$article->image = $image;

					// Remove all image from the content to avoid duplicate image
					$article->content = $this->removeContentImage($article->content);
				}
			}
		}
	}

	/**
	 * Method to normalize the content of the article
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function normalizeContent($content)
	{
		// Expression to search for anything inside {}
		$regex = '/{(.*)}/i';

		// Find all instances of plugin and put in $matches for {}
		preg_match_all($regex, $content, $matchesreg, PREG_SET_ORDER);

		// {} matched
		if ($matchesreg) {
			foreach ($matchesreg as $match) {
				$content = str_replace($match[0], '', $content);
			}
		}

		return $content;
	}

	/**
	 * Get image from the content
	 *
	 * @since	2.0
	 * @access	private
	 */
	private function processContentImage($content)
	{
		// @rule: Match images from content
		$pattern = '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';
		preg_match($pattern, $content, $matches);

		$image = '';

		if ($matches) {
			$image = isset($matches[1]) ? $matches[1] : '';

			if (ESJString::stristr($matches[1], 'https://') === false && ESJString::stristr($matches[1], 'http://') === false && !empty($image)) {
				$image	= rtrim(JURI::root(), '/') . '/' . ltrim($image, '/');
			}
		}

		return $image;
	}

	/**
	 * Remove all image tag from the content
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function removeContentImage($content)
	{
		preg_match("/<img[^>]+\>/i", $content, $matches);

		if ($matches) {
			$content = str_replace($matches[0], '', $content);
		}

		return $content;
	}
}
