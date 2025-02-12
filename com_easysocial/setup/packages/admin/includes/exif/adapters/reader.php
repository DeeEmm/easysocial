<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

/**
 * PHP Exif Reader: Reads EXIF metadata from a file
 *
 * @link        http://github.com/miljar/PHPExif for the canonical source repository
 * @copyright   Copyright (c) 2013 Tom Van Herreweghe <tom@theanalogguy.be>
 * @license     http://github.com/miljar/PHPExif/blob/master/LICENSE MIT License
 * @category    PHPExif
 * @package     Reader
 */

/**
 * PHP Exif Reader
 *
 * Responsible for all the read operations on a file's EXIF metadata
 *
 * @category    PHPExif
 * @package     Reader
 * @
 */
class SocialExifReader
{
    const INCLUDE_THUMBNAIL = true;
    const NO_THUMBNAIL      = false;

    const SECTIONS_AS_ARRAYS    = true;
    const SECTIONS_FLAT         = false;

    /**
     * List of EXIF sections
     *
     * @var array
     */
    protected $sections = array();

    /**
     * Include the thumbnail in the EXIF data?
     *
     * @var boolean
     */
    protected $includeThumbnail = self::NO_THUMBNAIL;

    /**
     * Parse the sections as arrays?
     *
     * @var boolean
     */
    protected $sectionsAsArrays = self::SECTIONS_FLAT;

    /**
     * Contains the mapping of names to IPTC field numbers
     *
     * @var array
     */
    protected $iptcMapping  = array(
        'title'     => '2#005',
        'keywords'  => '2#025',
        'copyright' => '2#116',
        'caption'   => '2#120',
    );


    /**
     * Getter for the EXIF sections
     *
     * @return array
     */
    public function getRequiredSections()
    {
        return $this->sections;
    }

    /**
     * Setter for the EXIF sections
     *
     * @param array $sections List of EXIF sections
     * @return \PHPExif\Reader Current instance for chaining
     */
    public function setRequiredSections(array $sections)
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * Adds an EXIF section to the list
     *
     * @param string $section
     * @return \PHPExif\Reader Current instance for chaining
     */
    public function addRequiredSection($section)
    {
        if (!in_array($section, $this->sections)) {
            array_push($this->sections, $section);
        }

        return $this;
    }

    /**
     * Define if the thumbnail should be included into the EXIF data or not
     *
     * @param boolean $value
     * @return \PHPExif\Reader Current instance for chaining
     */
    public function setIncludeThumbnail($value)
    {
        $this->includeThumbnail = $value;

        return $this;
    }

    /**
     * Reads & parses the EXIF data from given file
     *
     * @param string $file
     * @return \PHPExif\Exif Instance of Exif object with data
     * @throws \RuntimeException If the EXIF data could not be read
     */
    public function getExifFromFile($file)
    {
        $sections   = $this->getRequiredSections();
        $sections   = implode(',', $sections);
        $sections   = (empty($sections)) ? null : $sections;

        $data       = @exif_read_data($file, $sections, $this->sectionsAsArrays, $this->includeThumbnail);

        $xmpData = $this->getIptcData($file);
        $data = array_merge($data, array(SocialExifLibrary::SECTION_IPTC => $xmpData));
        $exif = new SocialExifLibrary($data);

        return $exif;
    }

    /**
     * Returns an array of IPTC data
     *
     * @param string $file The file to read the IPTC data from
     * @return array
     */
    public function getIptcData($file)
    {
        $size = getimagesize($file, $info);
        $arrData = array();
        if(isset($info['APP13'])) {
            $iptc = iptcparse($info['APP13']);

            foreach ($this->iptcMapping as $name => $field) {
                if (!isset($iptc[$field])) {
                    continue;
                }

                if (count($iptc[$field]) === 1) {
                    $arrData[$name] = reset($iptc[$field]);
                } else {
                    $arrData[$name] = $iptc[$field];
                }
            }
        }

        return $arrData;
    }
}
