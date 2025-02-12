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

ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/joomla_username/helper');

class SocialFieldsUserTerms extends SocialFieldItem
{
	/**
	 * Retrieves the terms and conditions text
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getTerms()
	{
		// Render the ajax lib.
		$ajax = ES::ajax();

		// Load the field
		$id = $this->input->get('id', 0, 'int');
		$this->field = ES::table('Field');
		$this->field->load($id);

		// Get the field params
		$params = $this->getParams();

		// Should we retrieve from the article?
		$useArticle = $params->get('article', false);
		$articleId = $params->get('article_id');
		$article = false;

		if ($useArticle && $articleId) {

			$article = JTable::getInstance('Content');
			$article->load($articleId);

			// Determine if the site enable multilingual language
			if (JLanguageAssociations::isEnabled()) {

				// Retrieve all the association article data e.g. English, French and etc
				$termsAssociated = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $articleId);

				// Determine the current site language
				$currentLang = JFactory::getLanguage()->getTag();

				// Only come inside this checking if the current site language not match with the selected article language
				// And see whether this tearmAssociated got detect got other association article or not
				if (isset($termsAssociated) && $currentLang !== $article->language && array_key_exists($currentLang, $termsAssociated)) {

					foreach ($termsAssociated as $term) {

						// Retrieve the associated article id
						if ($term->language == $currentLang) {
							$articleId = explode(':', $term->id);
							$articleId = $articleId[0];
							break;
						}
					}
				}

				// Reload the new associated article id
				$article->load($articleId);
			}
		}

		$theme = ES::themes();
		$theme->set('params', $params);
		$theme->set('useArticle', $useArticle);
		$theme->set('article', $article);

		$output = $theme->output('fields/user/terms/dialog.terms');

		return $ajax->resolve($output);
	}

}
