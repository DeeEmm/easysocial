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

jimport('joomla.html.html');
jimport('joomla.form.formfield');

require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php');

class JFormFieldEasySocial extends JFormField
{
	public function __construct()
	{
		ES::language()->loadAdmin();
		ES::language()->loadSite();

		$this->page = ES::document();
		$this->app = JFactory::getApplication();

		$this->doc = JFactory::getDocument();
		$this->doc->addStylesheet(rtrim(JURI::root() , '/') . '/administrator/components/com_easysocial/themes/default/styles/style.css');
	}

	/**
	 * Abstract method that should be implemented on child classes
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	protected function getInput()
	{
	}
}
