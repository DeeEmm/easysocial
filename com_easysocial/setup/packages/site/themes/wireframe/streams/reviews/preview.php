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
<div class="es-stream-apps type-discuss">
    <div class="es-stream-apps__hd">
        <a href="<?php echo $permalink;?>" class="es-stream-apps__title">
            <?php echo $reviews->title; ?>
        </a>
        <?php if ($appParams->get('stream_display_date', true)) { ?>
        <div class="es-stream-apps__meta t-fs--sm">
            <i class="fa fa-calendar"></i>&nbsp; <?php echo $reviews->getCreatedDate()->format(JText::_('DATE_FORMAT_LC')); ?>
        </div>
        <?php } ?>
        <div class="es-rating" data-es-ratings-stars-<?php echo $reviews->id; ?> data-score="<?php echo $reviews->value; ?>"></div>
    </div>

    <div class="es-stream-apps__bd es-stream-apps--border">
        <div class="es-stream-apps__desc"><?php echo $reviews->message; ?></div>
    </div>
</div>