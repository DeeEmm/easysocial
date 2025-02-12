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

class SocialGdprMarketplace extends SocialGdprAbstract
{
	public $type = 'marketplace';

	/**
	 * Main function to process user post data for GDPR download.
	 *
	 * @since 4.0
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$listings = $this->getListings();

		if (!$listings) {
			$this->tab->finalize();
			return;
		}

		foreach ($listings as $listing) {
			$listing = ES::marketplace($listing->id);

			$item = $this->getTemplate($listing->id, $this->type);

			$item->created = $listing->created;
			$item->title = $listing->title;
			$item->intro = $this->getIntro($listing);
			$item->view = true;
			$item->content = $this->getContent($listing);

			$photos = $listing->getPhotos(true);
			$item->source = 'joomla:' . $listing->getDefaultPhoto(true);

			if ($photos) {
				$photo = $photos[0];
				$item->source = $photo->storage . ':' . $photo->getPath('original', true);
			}

			$this->tab->addItem($item);
		}
	}

	public function getListings()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$model = ES::model('Marketplaces');
		$listings = $model->getMarketplaceGDPR($options);

		return $listings;
	}

	public function getIntro($listing)
	{
		$date = ES::date($listing->created);
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

	public function getContent($listing)
	{
		$date = ES::date($listing->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<figure>
				<img src="{%MEDIA%}" height="auto" width="100%"></img>
			</figure>
			<?php echo $listing->getDescription();?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $listing->getPriceTag(); ?> - <?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
