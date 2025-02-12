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
?>
<div class="" data-display-description <?php if( !$params->get( 'display_description' ) ) { echo 'style="display: none;"'; } ?>>
	<div class="help-block t-fs--sm text-note">
		<strong><?php echo JText::_( 'COM_EASYSOCIAL_NOTE' );?>:</strong> <span data-description><?php echo JText::_( $params->get( 'description' ) ); ?></span>
	</div>
</div>
