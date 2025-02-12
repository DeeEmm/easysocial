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

class HeadlineFieldWidgetsProfile
{
	public function afterName( $key, $user, $field )
	{
		$value = $field->data;

		if( empty( $value ) )
		{
			return;
		}

		$my			= ES::user();
		$privacyLib = ES::privacy( $my->id );
		if( !$privacyLib->validate( 'core.view' , $field->id, SOCIAL_TYPE_FIELD , $user->id ) )
		{
			return;
		}

		$theme 	= ES::themes();
		$theme->set( 'value'	, $value );
		$theme->set( 'params'	, $field->getParams() );

		echo $theme->output( 'fields/user/headline/widgets/display' );
	}
}
