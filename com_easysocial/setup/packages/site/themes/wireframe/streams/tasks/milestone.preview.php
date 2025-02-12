<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-apps type-milestone">
    <div class="es-stream-apps__hd">      
        <a class="es-stream-apps__title" href="<?php echo $permalink;?>"><?php echo $milestone->title;?></a>
        <div class="es-stream-apps__meta t-fs--sm">
        	<?php if ($milestone->user_id) { ?>
        	<span class="t-lg-mr--md"><i class="fa fa-user"></i> <?php echo JText::sprintf('APP_PAGE_TASKS_STREAM_RESPONSIBILITY_OF', $this->html('html.user', $milestone->user_id));?></span>
        	<?php } ?>

        	<span>
            <?php echo JText::sprintf('APP_PAGE_TASKS_DUE_ON', ES::date(strtotime($milestone->due))->format(JText::_('DATE_FORMAT_LC1'))); ?>
            </span>
        </div>
    </div>

    <div class="es-stream-apps__bd es-stream-apps--border">
        <div class="es-stream-apps__desc">
            <?php if( $milestone->description ){ ?>
            <hr />
            <p><?php echo $milestone->getContent();?></p>
            <?php } ?>
        </div>
        
    </div>
</div>


