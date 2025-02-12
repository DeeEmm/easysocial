<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class SocialMultiSites extends EasySocial
{
	/**
	 * Determines if the multi sites functionality exists
	 *
	 * @since	5.0
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function exists()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_multisites/helpers/utils.php';

		if (!JFile::exists($file)) {
			return false;
		}

		include_once($file);

		if (!class_exists('MultisitesHelperUtils') || !method_exists('MultisitesHelperUtils', 'getComboSiteIDs')) {
			return false;
		}

		return true;
	}

	/**
	 * Generates the form for multi sites
	 *
	 * @since	5.0
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function getForm($elementName, $value = '', $elementText = '')
	{
		if (!$this->exists()) {
			return false;
		}

		if (!$elementText) {
			$elementText = JText::_('COM_EASYSOCIAL_SELECT_SITE');
		}

		$form = MultisitesHelperUtils::getComboSiteIDs($value, $elementName, $elementText);

		if (!$form) {
			return false;
		}

		return $form;
	}
}