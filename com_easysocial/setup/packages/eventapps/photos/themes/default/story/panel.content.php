<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<?php if (!empty($exceeded)) { ?>
<div class="o-alert o-alert--warning o-alert--dismissible">
    <button data-story-attachment-clear-button class="o-alert__close" type="button">×</button>
    <strong><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_EXCEEDED'); ?></strong><br/><?php echo $exceeded ?>
</div>
<?php } else { ?>

<div data-album-view class="es-album-view es-media-group">
    <div data-album-content class="es-album-content">
        <div data-album-upload-button class="es-album-upload-button">
            <span>
                <b class="add-hint"><i class="fa fa-plus"></i><?php echo JText::_("COM_EASYSOCIAL_STORY_ADD_PHOTO"); ?></b>
                <b class="drop-hint"><i class="fa fa-upload"></i><?php echo JText::_("COM_EASYSOCIAL_STORY_DROP_PHOTO"); ?></b>
            </span>
        </div>
        <div data-photo-item-group class="es-photo-item-group">
        </div>
    </div>
</div>

<?php } ?>
