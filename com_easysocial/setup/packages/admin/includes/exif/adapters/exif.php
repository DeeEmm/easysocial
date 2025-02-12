<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

/**
 * PHP Exif Reader: Reads EXIF metadata from a file, without having to install additional PHP modules
 *
 * @link        http://github.com/miljar/PHPExif for the canonical source repository
 * @copyright   Copyright (c) 2013 Tom Van Herreweghe <tom@theanalogguy.be>
 * @license     http://github.com/miljar/PHPExif/blob/master/LICENSE MIT License
 * @category    PHPExif
 * @package     Exif
 */

/**
 * PHP Exif Reader
 *
 * Responsible for all the read operations on a file's EXIF metadata
 *
 * @category    PHPExif
 * @package     Exif
 * @
 */
class SocialExifLibrary
{
	const SECTION_FILE      = 'FILE';
	const SECTION_COMPUTED  = 'COMPUTED';
	const SECTION_IFD0      = 'IFD0';
	const SECTION_THUMBNAIL = 'THUMBNAIL';
	const SECTION_COMMENT   = 'COMMENT';
	const SECTION_EXIF      = 'EXIF';
	const SECTION_ALL       = 'ANY_TAG';
	const SECTION_IPTC      = 'IPTC';


	// Orientations
	const ORIENTATION_TOP_LEFT 	= 1;
	const ORIENTATION_TOP_RIGHT = 2;
	const ORIENTATION_BOTTOM_RIGHT = 3;
	const ORIENTATION_BOTTOM_LEFT = 4;
	const ORIENTATION_LEFT_TOP = 5;
	const ORIENTATION_RIGHT_TOP = 6;
	const ORIENTATION_RIGHT_BOTTOM = 7;
	const ORIENTATION_LEFT_BOTTOM = 8;

	/**
	 * The EXIF data
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Class constructor
	 *
	 * @param array $data
	 */
	public function __construct(array $data = array())
	{
		$this->setRawData($data);
	}

	/**
	 * Sets the EXIF data
	 *
	 * @param array $data The data to set
	 * @return \PHPExif\Exif Current instance for chaining
	 */
	public function setRawData(array $data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Returns all EXIF data in the raw original format
	 *
	 * @return array
	 */
	public function getRawData()
	{
		return $this->data;
	}

	/**
	 * Returns the Aperture F-number
	 *
	 * @return string|boolean
	 */
	public function getAperture()
	{
		if (!isset($this->data[self::SECTION_COMPUTED]['ApertureFNumber'])) {
			return false;
		}

		return $this->data[self::SECTION_COMPUTED]['ApertureFNumber'];
	}

	/**
	 * Returns the ISO speed
	 *
	 * @return int|boolean
	 */
	public function getIso()
	{
		if (!isset($this->data['ISOSpeedRatings'])) {
			return false;
		}

		return $this->data['ISOSpeedRatings'];
	}

	/**
	 * Returns the Exposure
	 *
	 * @return string|boolean
	 */
	public function getExposure()
	{
		if (!isset($this->data['ExposureTime'])) {
			return false;
		}

		return $this->data['ExposureTime'];
	}

	/**
	 * Returns the Exposure
	 *
	 * @return float|boolean
	 */
	public function getExposureMilliseconds()
	{
		if (!isset($this->data['ExposureTime'])) {
			return false;
		}

		$exposureParts  = explode('/', $this->data['ExposureTime']);

		return (int)reset($exposureParts) / (int)end($exposureParts);
	}

	/**
	 * Returns the focus distance, if it exists
	 *
	 * @return string|boolean
	 */
	public function getFocusDistance()
	{
		if (!isset($this->data[self::SECTION_COMPUTED]['FocusDistance'])) {
			return false;
		}

		return $this->data[self::SECTION_COMPUTED]['FocusDistance'];
	}

	/**
	 * Returns the width in pixels, if it exists
	 *
	 * @return int|boolean
	 */
	public function getWidth()
	{
		if (!isset($this->data[self::SECTION_COMPUTED]['Width'])) {
			return false;
		}

		return $this->data[self::SECTION_COMPUTED]['Width'];
	}

	/**
	 * Returns the height in pixels, if it exists
	 *
	 * @return int|boolean
	 */
	public function getHeight()
	{
		if (!isset($this->data[self::SECTION_COMPUTED]['Height'])) {
			return false;
		}

		return $this->data[self::SECTION_COMPUTED]['Height'];
	}

	/**
	 * Returns the title, if it exists
	 *
	 * @return string|boolean
	 */
	public function getTitle()
	{
		if (!isset($this->data[self::SECTION_IPTC]['title'])) {
			return false;
		}

		return $this->data[self::SECTION_IPTC]['title'];
	}

	/**
	 * Returns the caption, if it exists
	 *
	 * @return string|boolean
	 */
	public function getCaption()
	{
		if (!isset($this->data[self::SECTION_IPTC]['caption'])) {
			return false;
		}

		return $this->data[self::SECTION_IPTC]['caption'];
	}

	/**
	 * Returns the copyright, if it exists
	 *
	 * @return string|boolean
	 */
	public function getCopyright()
	{
		if (!isset($this->data[self::SECTION_IPTC]['copyright'])) {
			return false;
		}

		return $this->data[self::SECTION_IPTC]['copyright'];
	}

	/**
	 * Returns the keywords, if they exists
	 *
	 * @return array|boolean
	 */
	public function getKeywords()
	{
		if (!isset($this->data[self::SECTION_IPTC]['keywords'])) {
			return false;
		}

		return $this->data[self::SECTION_IPTC]['keywords'];
	}

	/**
	 * Returns the camera, if it exists
	 *
	 * @return string|boolean
	 */
	public function getCamera()
	{
		if (!isset($this->data['Model'])) {
			return false;
		}

		return $this->data['Model'];
	}

	/**
	 * Returns the orientation of the image
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getOrientation()
	{
		if( !isset( $this->data[ 'Orientation' ] ) )
		{
			return false;
		}

		$orientation 	= $this->data[ 'Orientation' ];

		return $orientation;
	}

	/**
	 * Returns the horizontal resolution in DPI, if it exists
	 *
	 * @return int|boolean
	 */
	public function getHorizontalResolution()
	{
		if (!isset($this->data['XResolution'])) {
			return false;
		}

		$resolutionParts = explode('/', $this->data['XResolution']);
		return (int)reset($resolutionParts);
	}

	/**
	 * Returns the vertical resolution in DPI, if it exists
	 *
	 * @return int|boolean
	 */
	public function getVerticalResolution()
	{
		if (!isset($this->data['YResolution'])) {
			return false;
		}

		$resolutionParts = explode('/', $this->data['YResolution']);
		return (int)reset($resolutionParts);
	}

	/**
	 * Returns the software, if it exists
	 *
	 * @return string|boolean
	 */
	public function getSoftware()
	{
		if (!isset($this->data['Software'])) {
			return false;
		}

		return $this->data['Software'];
	}

	/**
	 * Returns the focal length in mm, if it exists
	 *
	 * @return float|boolean
	 */
	public function getFocalLength()
	{
		if (!isset($this->data['FocalLength'])) {
			return false;
		}

		$parts  = explode('/', $this->data['FocalLength']);
		return (int)reset($parts) / (int)end($parts);
	}

	/**
	 * Returns the creation datetime, if it exists
	 *
	 * @return \DateTime|boolean
	 */
	public function getCreationDate()
	{
		if (!isset($this->data['DateTimeOriginal']))
		{
			return false;
		}

		$ts 	= strtotime( $this->data[ 'DateTimeOriginal' ] );

		if( $ts === false )
		{
			return false;
		}

		$dt 	= ES::date( $this->data[ 'DateTimeOriginal' ] )->toSql();

		return $dt;
	}

	/**
	 * Returns the latitude and longitude, if it exists
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getLocation()
	{
		if (!isset($this->data['GPSLongitude']) || !isset($this->data['GPSLongitudeRef'])) {
			return false;
		}

		// Construct the new location data
		$location = new stdClass();

		$location->longitude = $this->toDecimal($this->data['GPSLongitude'], $this->data['GPSLongitudeRef']);
		$location->latitude = $this->toDecimal($this->data['GPSLatitude'], $this->data['GPSLatitudeRef']);

		return $location;
	}

	/**
	 * Converts a GPS coordinate to a decimal based value
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function toDecimal($exifCoord, $hemi)
	{

	    $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
	    $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
	    $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

	    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

	    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

	}

	public function gps2Num($coordPart)
	{

	    $parts = explode('/', $coordPart);

	    if (count($parts) <= 0)
	        return 0;

	    if (count($parts) == 1)
	        return $parts[0];

	    return floatval($parts[0]) / floatval($parts[1]);
	}

}
