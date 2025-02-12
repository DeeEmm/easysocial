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
?>
<div class="es-stream-apps type-discuss is-locked">
    <div class="es-stream-apps__hd">
        <div class="o-flag">
            <div class="o-flag__image">
                <?php echo $this->html('avatar.' . $reply->getAuthor()->getType(), $reply->getAuthor(), 'sm', false, false);?>
            </div>
            <div class="o-flag__body">
            	<a href="<?php echo $reply->getAuthor()->getPermalink();?>"><?php echo $reply->getAuthor()->getName();?></a>
                <span><?php echo JText::_('APP_DISCUSSIONS_REPLIED');?>:</span>
            </div>
        </div>
        <i class="fa fa-check-circle es-stream-apps__state"></i>
    </div>
    <div class="es-stream-apps__bd es-stream-apps--border">
        <div class="es-stream-apps__desc is-quote">
            <?php echo $content; ?>
        </div>
        <ol class="g-list--horizontal has-dividers--right">
            <li class="g-list__item">
                <a href="<?php echo $permalink;?>">
                    <?php echo JText::_('APP_GROUP_DISCUSSIONS_VIEW_DISCUSSION'); ?>
                </a>
            </li>
        </ol>
    </div>
</div>