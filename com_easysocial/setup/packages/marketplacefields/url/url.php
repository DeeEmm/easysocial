<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('fields:/user/url/url');

class SocialFieldsMarketplaceUrl extends SocialFieldsUserUrl
{

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  4.0.12
	 * @access public
	 */
	public function onGetValue($user)
	{
		// since in marketplace we display the url using field->value, we need to make the value as anchor link.
		$value = $this->getValue();

		if (stristr($value, 'http://') === false && stristr($value, 'https://') === false) {

			// Determine what is the current site domain protocol
			$uri = JURI::getInstance();
			$scheme = $uri->toString(array('scheme'));

			$value = $scheme . $value;
		}

		$theme = ES::themes();

		$theme->set('params', $this->params);
		$theme->set('value', $this->escape($value));

		$content = $theme->output('fields/marketplace/url/display_content');
		$this->field->value = $content;
	}
}
