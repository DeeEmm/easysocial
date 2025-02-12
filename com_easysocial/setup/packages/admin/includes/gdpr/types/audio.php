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
defined('_JEXEC') or die('Unauthorized Access');

class SocialGdprAudio extends SocialGdprAbstract
{
	public $type = 'audio';

	/**
	 * Main function to process user audio data for GDPR download.
	 *
	 * @since 2.1.11
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$audios = $this->getAudios();

		// Nothing else to process, finalize it now.
		if (!$audios) {
			return $this->tab->finalize();
		}

		foreach ($audios as $audio) {

			$item = $this->getTemplate($audio->id, $this->type);

			$item->title = $audio->getTitle();
			$item->intro = $this->getIntro($audio);
			$item->content = $this->getContent($audio);
			$item->created = $audio->created;

			$item->view = true;

			// eg. joomla:/media/audio/391/1234.mp3
			if ($audio->isUpload()) {
				$item->source = $audio->storage . ':/' . $audio->getRelativeStoragePath() . '/' . basename($audio->path);
			}

			$this->tab->addItem($item);
		}
	}

	/**
	 * Retrieves audio that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getAudios()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		// Get a list of audios that needs to be exported / downloaded
		$model = ES::model('Audios');
		$audios = $model->getAudiosGDPR($options);

		return $audios;
	}

	public function getIntro(SocialAudio $audio)
	{
		$date = ES::date($audio->created);

		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();
		
		return $contents;
	}

	public function getContent(SocialAudio $audio)
	{
		$date = ES::date($audio->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php if ($audio->isLink()) { ?>
				<div class="audio-container is-<?php echo strtolower($audio->getLinkProvider()); ?>">
					<?php echo $audio->getLinkEmbedCodes();?>
				</div>
			<?php } else { ?>
				<audio width="480" height="240" controls>
					<source src="{%MEDIA%}" type="audio/mp3">
					Your browser does not support the audio element.
				</audio> 
			<?php } ?>
			<br />
			
			<?php echo $audio->getDescription();?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

}
