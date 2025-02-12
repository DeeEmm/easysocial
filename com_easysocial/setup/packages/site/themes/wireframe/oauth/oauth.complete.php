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
<div class="center">
	<h3><?php echo JText::_( 'COM_EASYSOCIAL_REGISTRATION_REGISTRATION_COMPLETED_HEADING' );?></h3>
	<hr />
</div>

<div class="es-complete-wrap mt-20">
	<?php echo $this->loadTemplate( 'site/registration/oauth.complete.' . $type , array( 'user' => $user ) ); ?>
</div>
