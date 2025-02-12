<?php
/**
* @package		EasyBlog
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasyBlog is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialCache
{
	public $storage = null;

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public static function getInstance()
	{
		static $obj = null;

		if (is_null($obj)) {
			$obj = new self();
		}

		return $obj;
	}

	/**
	 * @since	1.4
	 * @access	public
	 * @param   null
	 * @return  SocialCache
	 */
	public static function factory()
	{
		return new self();
	}

	public function cacheClusters($clusterIds = array())
	{
		if ($clusterIds) {
			$modelCluster = ES::model('Clusters');
			$results = $modelCluster->preloadClusters($clusterIds);

			if ($results) {

				$clusterCatIds = array();

				foreach($results as $item) {
					$clusterCatIds[] = $item->category_id;

					$clusterTbl = ES::table('Cluster');
					$clusterTbl->bind($item);

					$key = 'cluster.' . $item->id;
					$this->set($key, $clusterTbl);
				}

				if ($clusterCatIds) {
					$clusterCatIds = array_unique($clusterCatIds);

					$modelCategory = ES::model('ClusterCategory');
					$results = $modelCategory->preloadCategory($clusterCatIds);

					if ($results) {

						foreach($results as $item) {
							$clusterCat = ES::table('ClusterCategory');
							$clusterCat->bind($item);

							$key = 'cluster.category.' . $item->id;
							$this->set($key, $clusterCat);
						}

					}
				}


			}

		}
	}

	public function cachePhotos($photos = array())
	{
		if ($photos) {
			// photos
			$photo = ES::table( 'Photo' );
			$photo->setCacheable( true );
			$albumIds = $photo->loadByBatch( $photos );

			// photos meta
			$photoModel = ES::model( 'Photos' );
			$photoModel->setCacheable( true );
			$photoModel->setMetasBatch( $photos );
		}

		if ($albumIds) {
			$albumIds = array_unique( $albumIds );
			$album = ES::table( 'Album' );
			$album->loadByBatch( $albumIds );
		}
	}

	public function cacheUsersPrivacy($users = array())
	{
		if ($users) {

			$userIds = array();

			foreach($users as $userId) {

				if (! $userId) {
					continue;
				}

				$userPrivacykey = 'user.privacy.' . $userId;

				if (! $this->exists($userPrivacykey)) {
					$userIds[] = $userId;
				}
			}

			if ($userIds) {
				$privacyModel = ES::model('Privacy');
				$items = $privacyModel->preloadUserPrivacy($userIds);

				foreach($items as $uid => $items) {
					$key = 'user.privacy.' . $uid;
					$this->set($key, $items);
				}
			}
		}
	}

	public function cacheProfileStepsFields($steps, $visible)
	{
		$stepIds = array();

		foreach ($steps as $step) {
			$sid = $step->id;

			$fieldkey = 'fields.steps.' . $sid;

			if (! $this->exists($fieldkey)) {
				$stepIds[] = $sid;
			}
		}

		if ($stepIds) {
			$model = ES::model('Fields');
			$items = $model->preloadStepsFields($stepIds, $visible);

			foreach($items as $uid => $item) {
				$key = 'fields.steps.' . $uid;
				$this->set($key, $item);
			}
		}
	}


	public function cacheFieldsPrivacy($userId, $fields)
	{
		$fids = array();

		foreach($fields as $field) {
			$fids[] = $field->id;
		}

		if ($fids) {
			$privacyModel = ES::model('Privacy');
			$items = $privacyModel->preloadFieldsPrivacy($userId, $fids);

			foreach($items as $uid => $rows) {
				if ($rows) {
					foreach ($rows as $type => $item) {
						$key = 'field.privacy.' . $userId . '.' . $uid . '.' . $type;
						$this->set($key, $item);
					}
				}
			}
		}
	}


	public function cacheUsersMeta($users = array())
	{
		if ($users) {

			// profile default avatars
			$profileAvatars = array();
			$userIds = array();

			foreach($users as $user) {

				$defaultProfileAvatarkey = SOCIAL_TYPE_PROFILES .'.avatar.' . $user->profile_id;
				$userMetakey = 'user.meta.' . $user->id;

				if (! $this->exists($defaultProfileAvatarkey) && $user->profile_id) {
					$profileAvatars[] = $user->profile_id;
				}

				if (! $this->exists($userMetakey)) {
					$userIds[] = $user->id;
				}

			}

			if ($profileAvatars) {

				// preset the values first
				foreach ($profileAvatars as $profileId) {
					$key = SOCIAL_TYPE_PROFILES .'.avatar.' . $profileId;
					$this->set($key, array());
				}

				$avatarModel = ES::model('Avatars');
				$avatars = $avatarModel->preloadDefaultAvatar($profileAvatars);

				if ($avatars) {
					foreach($avatars as $uid => $items) {
						$key = SOCIAL_TYPE_PROFILES .'.avatar.' . $uid;
						$this->set($key, $items);
					}
				}
			}

			if ($userIds) {

				$usersArr = array();

				foreach($userIds as $uid) {
					$usersArr[$uid] = '0';
				}

				$userModel = ES::model('Users');

				// user groups
				$userModel->setUserGroupsBatch($userIds);

				// es user meta
				$users = $userModel->preloadUsers($userIds);
				if ($users) {
					foreach($users as $item) {
						$usersArr[$item->user_id] = $item;
					}
				}

				foreach($usersArr as $uid => $item) {
					$userMetakey = 'user.meta.' . $uid;
					$this->set($userMetakey, $item);
				}

				// perload online
				$onlineUsers = $userModel->preloadIsOnline($userIds);
				foreach($userIds as $uid) {
					$isOnline = isset($onlineUsers[$uid]) ? $onlineUsers[$uid] : 0;

					$userOnlineKey = 'user.online.' . $uid;
					$this->set($userOnlineKey, $isOnline);
				}

			}

		}

	}




	/**
	 * Adds a cache for a specific item type using key
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string, object
	 * @return  boolean
	 */
	public function set($key, $items)
	{
		// Check if this item already exists.
		$this->storage[$key] = $items;
	}

	/**
	 * Get cache for the object type
	 *
	 * @since	5.0
	 * @access	public
	 * @param	string
	 * @return  object
	 */
	public function get($key)
	{
		if (isset($this->storage[$key])) {
			return $this->storage[$key];
		}

		return null;
	}

	/**
	 * check if the cache for the object type exists or not
	 *
	 * @since	5.0
	 * @access	public
	 * @param	string, string
	 * @return  boolean
	 */
	public function exists($key)
	{
		if (isset($this->storage[$key])) {
			return true;
		}

		return false;
	}

}
