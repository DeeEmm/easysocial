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

class SocialGdprPhoto extends SocialGdprAbstract
{
	public $type = 'photo';
	private $tab = null;

	/**
	 * Process the photos
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$photos = $this->getPhotos();

		if (!$photos) {
			$this->tab->finalize();
			return;
		}

		foreach ($photos as $photo) {
			$item = $this->getTemplate($photo->id, $this->type);

			$item->created = $photo->created;
			$item->title =  '<strong>' . JText::_($photo->getAlbum()->title) . '</strong> - ' . $photo->title;
			$item->intro = $this->getIntro($photo);
			$item->content = $this->getContent($photo);
			$item->view = true;

			$item->source = $photo->storage . ':' . $photo->getPath('original', true);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Retrieve the photos related to gdpr
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getPhotos()
	{
		$ids = $this->tab->getProcessedIds();

		$model = ES::model("Photos");

		$options = array();
		$options['userId'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['ordering'] = 'album_id';
		$options['sort'] = 'ASC';
		$options['limit'] = $this->getLimit();

		// Get the photos
		$photos = $model->getPhotos($options);

		return $photos;
	}

	public function getIntro(SocialTablePhoto $photo)
	{
		$date = ES::date($photo->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $photo->caption; ?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();
		
		return $contents;
	}

	public function getContent(SocialTablePhoto $photo)
	{
		$date = ES::date($photo->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<figure>
				<img src="{%MEDIA%}" height="auto" width="100%"></img>
				<figcaption><?php echo $photo->caption; ?></figcaption>
			</figure>
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
