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

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

/**
 * Migrators model.
 *
 * @since	1.4
 * @author	Sam <sam@stackideas.com>
 */
class EasySocialModelMigrators extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('migrators');
	}


	public function purgeHistory($component)
	{
		$map['jomsocial']['component'] = 'com_community';
		$map['jomsocial']['elements'] = array('messages', 'conversation', 'points', 'cover', 'avatar', 'photocomments', 'photos', 'albumscover', 'albums', 'connection', 'profileusers', 'profiles', 'fields');

		$map['jomsocialevent']['component'] = 'com_community';
		$map['jomsocialevent']['elements'] = array('eventwalls', 'eventcover', 'eventavatar', 'events', 'eventcategory');
		$map['jomsocialevent']['likes'] = array('eventmembers');

		$map['jomsocialgroup']['component'] = 'com_community';
		$map['jomsocialgroup']['elements'] = array('groupcover', 'groupwalls', 'groupbulletins', 'groupdiscussions', 'groupdiscussionsfile', 'groupcollection', 'groupdiscussionsparent', 'groupcategory', 'groups', 'groupavatar', 'groupphotos', 'groupalbums');
		$map['jomsocialgroup']['likes'] = array('groupmembers');

		$map['cb']['component'] = 'com_comprofiler';
		$map['cb']['elements'] = array('steps', 'fields', 'profiles', 'avatar', 'connection', 'users');

		$map['kunena']['component'] = 'com_kunena';
		$map['kunena']['elements'] = array('topic', 'reply', 'avatar');

		$map['joomla']['component'] = 'joomla';
		$map['joomla']['elements'] = array('userreg');

		$map['easyblog']['component'] = 'com_easyblog';
		$map['easyblog']['elements'] = array('blogcomment', 'blog');

		$state = true;
		if (isset($map[$component])) {
			$db = ES::db();
			$sql = $db->sql();

			$target = $map[$component]['component'];
			$elements = $map[$component]['elements'];

			$elementString = '';
			if ($elements) {
				foreach($elements as $element) {
					$elementString .= $elementString ? ',' . $db->Quote($element) : $db->Quote($element);
				}
			}

			$query = "delete from `#__social_migrators` where `component` = " . $db->Quote($target);
			$query .= " and `element` IN (" . $elementString . ")";

			$sql->raw($query);
			$db->setQuery($sql);
			$state = $db->query();

			// lets check if we need to execute LIKE statement or not.
			if (isset($map[$component]['likes']) && $map[$component]['likes']) {

				$likes = $map[$component]['likes'];

				foreach($likes as $like) {

					$query = "delete from `#__social_migrators` where `component` = " . $db->Quote($target);
					$query .= " and `element` LIKE " . $db->Quote($like . '%');

					$sql->clear();
					$sql->raw($query);
					$db->setQuery($sql);
					$state = $db->query();
				}
			}


		}

		return $state;
	}


	/**
	 * function to retrieve counts for image files stored in amazon or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getJSAmazonPhotosCount($component, $type)
	{
		$db = ES::db();

		$count = 0;

		$query = '';

		switch ($type) {

			case 'user':

				$query = 'select sum(cnt) as totalcnt from (';

				$query .= 'select count(1) as `cnt`';
				$query .= ' from `#__community_photos` as a';
				$query .= ' inner join `#__community_photos_albums` as pa on a.`albumid` = pa.`id` and pa.`type` = ' . $db->Quote('user');
				$query .= ' where not exists ( ';
				$query .= '		select b.`id` from `#__social_migrators` as b';
				$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'photos' ) . ' and b.`component` = ' . $db->Quote($component);
				$query .= ' )';
				$query .= ' and a.`storage` = ' . $db->Quote('s3');

				$query .= ' UNION ';

				$query .= 'select count(1) as `cnt`';
				$query .= ' from `#__community_users` as a';
				$query .= ' where not exists ( ';
				$query .= '		select b.`id` from `#__social_migrators` as b';
				$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote( 'avatar' ) . ' and b.`component` = ' . $db->Quote( $component );
				$query .= ' )';
				$query .= ' and a.`storage` = ' . $db->Quote('s3');

				$query .= ' UNION ';

				$query .= 'select count(1) as `cnt`';
				$query .= ' from `#__community_users` as a';
				$query .= ' where not exists ( ';
				$query .= '		select b.`id` from `#__social_migrators` as b';
				$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote( 'cover' ) . ' and b.`component` = ' . $db->Quote( $component );
				$query .= ' )';
				$query .= ' and a.`storage` = ' . $db->Quote('s3');

				$query .= ') as x';

				break;

			case 'group':

				$query = 'select sum(cnt) as totalcnt from (';

				$query .= 'select count(1) as `cnt`';
				$query .= ' from `#__community_photos` as a';
				$query .= ' inner join `#__community_photos_albums` as pa on a.`albumid` = pa.`id` and pa.`type` like ' . $db->Quote('group%');
				$query .= ' where not exists ( ';
				$query .= '		select b.`id` from `#__social_migrators` as b';
				$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'groupphotos' ) . ' and b.`component` = ' . $db->Quote($component);
				$query .= ' )';
				$query .= ' and a.`storage` = ' . $db->Quote('s3');

				$query .= ' UNION ';

				$query .= 'select count(1) as `cnt`';
				$query .= ' from `#__community_groups` as a';
				$query .= ' where not exists ( ';
				$query .= '		select b.`id` from `#__social_migrators` as b';
				$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'groupavatar' ) . ' and b.`component` = ' . $db->Quote( $component );
				$query .= ' )';
				$query .= ' and a.`storage` = ' . $db->Quote('s3');

				$query .= ' UNION ';

				$query .= 'select count(1) as `cnt`';
				$query .= ' from `#__community_groups` as a';
				$query .= ' where not exists ( ';
				$query .= '		select b.`id` from `#__social_migrators` as b';
				$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'groupcover' ) . ' and b.`component` = ' . $db->Quote( $component );
				$query .= ' )';
				$query .= ' and a.`storage` = ' . $db->Quote('s3');

				$query .= ') as x';


				break;

			case 'event':

				$query = 'select count(1) as `cnt`';
				$query .= ' from `#__community_photos` as a';
				$query .= ' inner join `#__community_photos_albums` as pa on a.`albumid` = pa.`id` and pa.`type` like ' . $db->Quote('event%');
				$query .= ' and a.`storage` = ' . $db->Quote('s3');

				break;

			default:
				break;
		}


		if ($query) {
			$db->setQuery($query);
			$count = $db->loadResult();
		}

		return $count;
	}

}
