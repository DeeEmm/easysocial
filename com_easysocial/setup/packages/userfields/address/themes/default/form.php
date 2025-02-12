<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<input type="input" spellcheck="false" autocomplete="off" id="<?php echo $this->element; ?>-address" class="o-form-control" name="<?php echo $this->element; ?>[address]" value="<?php echo $this->field->getAddressValue()->address; ?>" />
<br />
<input type="input" spellcheck="false" autocomplete="off" id="<?php echo $this->element; ?>-address2" class="o-form-control" name="<?php echo $this->element; ?>[address2]" value="<?php echo $this->field->getAddressValue()->address2; ?>" />
<br />
<input type="input" spellcheck="false" autocomplete="off" id="<?php echo $this->element; ?>-postcode" class="o-form-control" name="<?php echo $this->element; ?>[postcode]" value="<?php echo $this->field->getAddressValue()->postcode; ?>" />
