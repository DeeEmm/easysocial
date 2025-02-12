<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<label for="<?php echo $inputName; ?>[<?php echo $key; ?>]" class="t-hidden">File</label>
<input type="file" id="<?php echo $inputName; ?>[<?php echo $key; ?>]" class="input" name="<?php echo $inputName; ?>[<?php echo $key; ?>]" data-field-file-upload data-key="<?php echo $key; ?>" />
<span data-field-file-progress></span>
