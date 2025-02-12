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

class EasySocialControllerSefUrls extends EasySocialController
{
	// Used for registering action logs
	protected $logContext = 'sefurls';

	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'save');
		$this->registerTask('apply', 'save');
		$this->registerTask('remove', 'remove');
		$this->registerTask('purgeAll', 'purge');
	}

	/**
	 * Purge all sef urls
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function purge()
	{
		ES::checkToken();

		$config = ES::config();
		$model = ES::model('Urls');

		$this->logAction('COM_ES_ACTION_LOG_PURGED_SEFURLS');

		// before purge, we need to get all the customized urls
		$customUrls = $model->getCustomUrls();
		$withCustom = $customUrls ? false : true;

		$state = $model->purge($withCustom);

		if ($state && $config->get('seo.cachefile.enabled')) {
			$cache = ES::fileCache();
			$cache->purge($customUrls);
		}

		$this->view->setMessage('COM_ES_SEFURLS_PURGED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Save the workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save()
	{
		ES::requireLogin();

		// Get the current task
		$task = $this->getTask();

		$id = $this->input->get('id', 0, 'int');
		$sefurl = $this->input->get('sefurl', '', 'default');

		if (!$id || !$sefurl) {
			return $this->view->exception('Invalid data provided!');
		}

		$url = ES::table('Urls');
		$url->load($id);

		$oriSef = $url->sefurl;

		$url->sefurl = $sefurl;
		$url->custom = 1; // always mark this url as customized

		$state = $url->store();

		if (!$state) {
			$this->view->setMessage($workflow->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $url, $task);
		}

		// update cache entry
		$url->updateCacheEntry($oriSef);

		// Set message.
		$message = 'COM_ES_SEFURLS_UPDATED_SUCCESSFULLY';

		$this->logAction('COM_ES_ACTION_LOG_SEFURL_UPDATED', [
			'original' => $oriSef,
			'new' => $sefurl
		]);

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $url, $task);
	}

	/**
	 * Delete the worfklow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();
		$config = ES::config();

		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			return $this->view->exception('Invalid ID provided');
		}

		foreach ($ids as $id) {

			$url = ES::table('Urls');
			$url->load((int) $id);

			$this->logAction('COM_ES_ACTION_LOG_SEFURL_DELETED', [
				'permalink' => $url->sefurl
			]);
		}


		$model = ES::model('Urls');

		if ($config->get('seo.cachefile.enabled')) {
			// get the data 1st.
			$urls = $model->getUrls($ids);

			// delete from cache.
			$cache = ES::fileCache();
			$cache->removeCacheItems($urls);
		}

		// delete from db.
		$model->delete($ids);

		$this->view->setMessage('COM_ES_SEFURLS_DELETE_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}
}
