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

ES::import('site:/views/views');

class EasySocialViewDownload extends EasySocialSiteView
{
	/**
	 * Renders the download account data page
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::requireLogin();

		if (!$this->config->get('users.download.enabled')) {
			ES::raiseError(404, JText::_('COM_ES_GDPR_DOWNLOAD_DISABLED'));
		}

		if (!$this->my->id) {
			ES::raiseError(404, JText::_('COM_ES_GDPR_DOWNLOAD_INVALID_ID'));
		}

		$download = ES::table('Download');
		$exists = $download->load(array('userid' => $this->my->id));

		if (!$exists || !$download->isReady()) {
			ES::raiseError(404, JText::_('COM_ES_GDPR_DOWNLOAD_INVALID_ID'));
		}

		return $download->showArchiveDownload();
	}
}
