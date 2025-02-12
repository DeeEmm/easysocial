<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

ES::import( 'site:/views/views' );

class EasySocialViewShare extends EasySocialSiteView
{
	/**
	 * type : 'profile' == profile status,
	 * content : shared content.
	 */

	public function add()
	{
		$type     = $this->input->get('type', '', 'string');
		$content  = $this->input->get('text', '', 'default');

		$my         = ES::get( 'People' );
		$streamId   = '';

		switch($type)
		{
		    case 'profile':
		        $data   = array();
				$data['actor_node_id'] 	= $my->get('node_id');
				$data['node_id'] 		= '1';
				$data['content'] 		= $content;

				$storyTbl   = ES::table('Story');
				$storyTbl->bind($data);
				$storyTbl->store();

		        $streamId   = $storyTbl->streamId;

		        if( !empty($streamId) )
		        {
		            $story	= ES::get( 'Stream' )->get('people', '', '', false, $streamId);
		            ES::get( 'AJAX' )->success( $story[0] );
		            return;
		        }

				break;
			default:
			    break;
		}

		ES::get( 'AJAX' )->success();
	}
}
