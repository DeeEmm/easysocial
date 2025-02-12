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

class SocialGdprVideo extends SocialGdprAbstract
{
	public $type = 'video';
	private $tab = null;

	/**
	 * Process video downloads in accordance to GDPR rules
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$videos = $this->getVideos();


		// Nothing else to process, finalize it now.
		if (!$videos) {
			return $this->tab->finalize();
		}

		foreach ($videos as $video) {
			$item = $this->getTemplate($video->id, $this->type);

			$item->created = $video->created;

			$item->title = $video->getTitle();
			$item->intro = $this->getIntro($video);
			$item->content = $this->getContent($video);

			$item->view = true;


			// It could be from remote storage using amazon:
			if ($video->isUpload()) {
				$item->source = $video->storage . ':/' . $video->getRelativeStoragePath() . '/' . basename($video->path);
			}

			$this->tab->addItem($item);
		}
	}

	/**
	 * Retrieves videos that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getVideos()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		// Get a list of videos that needs to be exported / downloaded
		$model = ES::model('Videos');
		$videos = $model->getVideosGDPR($options);


		return $videos;
	}

	public function getIntro(SocialVideo $video)
	{
		$date = ES::date($video->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $video->getDescription(); ?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();
		
		return $contents;
	}

	public function getContent(SocialVideo $video)
	{
		$date = ES::date($video->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<div class="video-container">
				<?php if ($video->isLink()) { ?>
					<?php echo $video->getLinkEmbedCodes();?>
				<?php } else { ?>
					<video width="480" height="240" controls>
						<source src="{%MEDIA%}" type="video/mp4">
						Your browser does not support the video tag.
					</video> 
				<?php } ?>
			</div>
			<br /><br />
			<?php echo $video->getDescription();?>
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
