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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerLicenseVerify extends EasySocialSetupController
{
	/**
	 * Verifies the user's license
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$key = SI_KEY;
		$result = new stdClass();

		if (!$key) {
			$result->state = 400;

			return $this->output($result);
		}

		// Verify the key
		$response = $this->verifyApiKey($key);

		if ($response === false) {
			$result->state = 400;
			$result->message = JText::_('Unable to verify your license or your hosting provider has blocked outgoing connections.');
			return $this->output($result);
		}

		if ($response->state == 400) {
			return $this->output($response);
		}

		ob_start();
?>
		<?php if (count($response->licenses) == 1) { ?>
		<input type="hidden" name="license" value="<?php echo $response->licenses[0]->reference;?>" />
		<?php } else { ?>
		<select class="form-control" name="license" data-source-license>
			<?php foreach ($response->licenses as $license) { ?>
			<option value="<?php echo $license->reference;?>"><?php echo $license->title;?> - <?php echo $license->reference; ?></option>
			<?php } ?>
		</select>
		<?php } ?>
<?php
		$output = ob_get_contents();
		ob_end_clean();

		$response->html = $output;
		return $this->output($response);
	}
}
