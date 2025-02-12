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
<ul class="g-list-unstyled es-avatar-list ml-10 mb-10">
<?php if( count( $users ) > 0 ) { ?>
	<?php foreach( $users as $user ){ ?>
	<li>
		<a class="es-avatar es-avatar-xs pull-left mr-10"
		data-original-title="<?php echo $user->getName();?>"
		data-placement="top"
		data-es-provide="tooltip"
		href="<?php echo $user->getPermalink();?>" title="<?php echo $user->getName();?>">
			<img class="" src="<?php echo $user->getAvatar();?>" alt="<?php echo $user->getName();?>" title="<?php echo $user->getName();?>">
		</a>
	</li>
	<?php } ?>
<?php } ?>
</ul>
