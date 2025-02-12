<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/vendor.php');

class SocialSharingEmail extends SocialSharingVendor
{
	public function getHTML()
	{
		$theme = ES::themes();

		$url = base64_encode($this->getParamUrl());

		if (!$url) {
			return;
		}
		
		$theme->set('url', $url);

		return $theme->output('site/sharing/email');
	}

	public function getShareId()
	{
		$url	= $this->getParamUrl();
		$params = ES::json()->encode( $this->params );

		$table = ES::table( 'tmp' );

		$table->type	= 'sharing';
		$table->key		= $url;
		$table->value	= $params;

		$result = $table->store();

		if( $result === false )
		{
			return false;
		}

		return $table->id;
	}
}
