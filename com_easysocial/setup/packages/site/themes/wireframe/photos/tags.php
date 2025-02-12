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
<div data-photo-tag-viewport class="es-photo-tag-viewport">
	<?php if( $tags ){ ?>
		<?php foreach( $tags as $tag ){ ?>
			<?php echo $this->includeTemplate('site/photos/tags.item', array('tag' => $tag)); ?>
		<?php } ?>
	<?php } ?>
</div>
