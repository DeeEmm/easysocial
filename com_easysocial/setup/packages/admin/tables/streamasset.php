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

ES::import('admin:/tables/table');

class SocialTableStreamAsset extends SocialTable
{
	public $id = null;
	public $stream_id = null;
	public $type = null;
	public $data = null;

	public function __construct($db)
	{
		parent::__construct('#__social_stream_assets', 'id', $db);
	}

	public function getParams()
	{
		$registry = ES::registry();
		$registry->load($this->data);

		return $registry;
	}

	/**
	 * Update assets data from the link stream.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateAssetsData($streamId, $data)
	{
		$model = ES::model('Stream');
		$state = $model->updateAssetsData($streamId, $data);

		return $state;
	}
}
