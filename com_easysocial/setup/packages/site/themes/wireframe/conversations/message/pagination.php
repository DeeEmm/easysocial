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
<div class="es-convo-messages__item es-pagination" data-es-message-pagination-wrapper>
<?php if ($nextlimit >= 0) { ?>
    <a href="javascript:void(0);" class="btn btn-es-default-o btn-block" data-es-message-pagination data-id="<?php echo $id; ?>" data-limitstart="<?php echo $nextlimit;?>">
        <i class="fa fa-refresh"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_LOAD_OLDER_MESSSAGES'); ?>
        <div class="o-loader o-loader--sm"></div>
    </a>
<?php } ?>
</div>
