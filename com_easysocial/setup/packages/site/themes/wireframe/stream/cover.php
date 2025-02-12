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
<div data-es-photo-group="album:<?php echo $photo->album_id;?>">
    <div class="es-photo es-cover">
        <a class="es-cover-container"
           href="<?php echo $photo->getPermalink();?>"
           title="<?php echo $this->html('string.escape', $photo->title . (($photo->caption!=='') ? ' - ' . $photo->caption : '')); ?>"
           data-es-photo="<?php echo $photo->id; ?>">
            <u class="es-cover-viewport">
                <b><img src="<?php echo $photo->getSource(SOCIAL_PHOTOS_LARGE); ?>" alt="<?php echo $this->html('string.escape', $photo->title . (($photo->caption!=='') ? ' - ' . $photo->caption : '')); ?>" /></b>
                <em style="background-image: url('<?php echo $photo->getSource(SOCIAL_PHOTOS_LARGE); ?>'); background-position: <?php echo $cover->getPosition(); ?>;"></em>
           </u>
        </a>
    </div>
</div>
