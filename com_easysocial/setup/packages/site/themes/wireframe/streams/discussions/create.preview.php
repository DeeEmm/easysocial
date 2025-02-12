<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-apps type-discuss">
    <div class="es-stream-apps__hd">
        <a href="<?php echo $permalink;?>" class="es-stream-apps__title">
            <?php echo $discussion->_('title'); ?>
        </a>
        <div class="es-stream-apps__meta t-fs--sm">
            <?php echo JText::sprintf('APP_GROUP_DISCUSSIONS_CONTENT_POSTED_ON_META', ES::date($discussion->created)->format(JText::_('DATE_FORMAT_LC1')));?>
        </div>
    </div>

    <div class="es-stream-apps__bd es-stream-apps--border">
        <div class="es-stream-apps__desc">
            <?php echo $content;?>
        </div>

        <ol class="g-list--horizontal has-dividers--right">
            <li class="g-list__item">
                <a href="<?php echo $permalink;?>">
                    <?php echo JText::_('APP_GROUP_DISCUSSIONS_VIEW_DISCUSSION'); ?>
                </a>
            </li>
        </ol>

        <?php if ($files) { ?>
        <hr class="es-hr" />
            <div class="es-stream-apps__desc is-file">
            <?php foreach ($files as $file) { ?>

                <span class="t-lg-mb--md">
                    <a href="<?php echo $file->getPreviewURI();?>" target="_blank"><?php echo $file->name;?></a> (<?php echo $file->getSize();?>kb)
                </span>

            <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
