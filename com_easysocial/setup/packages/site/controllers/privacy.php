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

class EasySocialControllerPrivacy extends EasySocialController
{
	/**
	 * Allows current logged in user to update their privacy item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		// get data from form post.
		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('utype', '', 'default');
		$value = $this->input->get('value', '', 'default');
		$pid = $this->input->get('pid', '', 'default');
		$customIds = $this->input->get('custom', '', 'default');
		$fields = $this->input->get('field', '', 'default');
		$streamid = $this->input->get('streamid', '', 'default');
		$userid = $this->input->get('userid', '', 'default');
		$pitemid = $this->input->get('pitemid', '', 'default');

		// If id is invalid, throw an error.
		if (!$uid) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		// we need to check if userid is belong to this privacy item or not.
		$ownerUserId = $this->my->id;

		if ($userid && $userid != $this->my->id && $this->my->isSiteAdmin()) {
			if ($streamid) {
				$streamTbl = ES::table('Stream');
				$streamTbl->load($streamid);

				$ownerUserId = $streamTbl->actor_id;
			} else if ($pitemid) {
				$pItemTbl = ES::table('PrivacyItems');
				$pItemTbl->load($pitemid);

				$ownerUserId = $pItemTbl->user_id;
			} else {
				$ownerUserId = $userid;
			}
		}

		$model = ES::model('Privacy');
		$state = $model->update($ownerUserId, $pid, $uid, $utype, $value, $customIds, $fields);

		// If there's an error, log this down.
		if (!$state) {
			$this->view->setMessage($model->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$privacy = ES::privacy();
		$access = $privacy->toValue($value);

		// lets try to get the stream id if not present. #3587
		$allowedMedia = array('photos', 'videos', 'audios');

		if (!$streamid && in_array($utype, $allowedMedia)) {
			$result = $model->getStreamItemCount($utype, $uid);

			if ($result && isset($result->total) && $result->total == 1) {
				$streamid = $result->uid;
			}
		}

		// lets check if there is stream id presented or not. if yes, means we need to update
		// privacy access in stream too.
		if ($streamid) {

			$stream = ES::stream();
			$stream->updateAccess($streamid, $access, $customIds, $fields);

			// Further check if this is a story albums stream, we need to update individual photos that related to the stream as well
			$streamTbl = ES::table('Stream');
			$streamTbl->load($streamid);

			if ($streamTbl->context_type == 'photos') {
				$streamModel = ES::model('Stream');
				$contextIds = $streamModel->getContextItems($streamid);

				if (!empty($contextIds)) {
					$photosId = array();
					foreach ($contextIds as $context) {
						if ($context->context_type == 'photos') {
							$privacy->add('photos.view', $context->context_id, $context->context_type, $value, $ownerUserId, $customIds, $fields);
							$photosId[] = $context->context_id;
						}
					}

					if($photosId) {
						$model->updateMediaAccess('photos', $photosId, $access, $customIds, $fields);
					}
				}
			}
		}

		// we need to further update privacy access on these medias table. #3289
		$supportedMedia = array('photos', 'albums', 'audios', 'videos');
		if (in_array($utype, $supportedMedia)) {
			$model->updateMediaAccess($utype, $uid, $access, $customIds, $fields);
		}

		$tooltips = JText::_('COM_EASYSOCIAL_PRIVACY_TOOLTIPS_SHARED_WITH_' . strtoupper($value));

		return $this->view->call(__FUNCTION__, $tooltips);
	}
}
