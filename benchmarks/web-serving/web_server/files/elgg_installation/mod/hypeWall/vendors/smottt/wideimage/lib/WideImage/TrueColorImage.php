<?php
	/**
##DOC-SIGNATURE##

    This file is part of WideImage.
		
    WideImage is free software; you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation; either version 2.1 of the License, or
    (at your option) any later version.
		
    WideImage is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.
		
    You should have received a copy of the GNU Lesser General Public License
    along with WideImage; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  **/

namespace WideImage;

use WideImage\Exception\InvalidImageDimensionException;

/**
 * A class for truecolor image objects
 * 
 * @package WideImage
 */
class TrueColorImage extends Image
{
	/**
	 * Creates the object
	 *
	 * @param resource $handle
	 */
	public function __construct($handle)
	{
		parent::__construct($handle);
		
		$this->alphaBlending(false);
		$this->saveAlpha(true);
	}
	
	/**
	 * Factory method that creates a true-color image object
	 *
	 * @param int $width
	 * @param int $height
	 * @return \WideImage\TrueColorImage
	 */
	public static function create($width, $height)
	{
		if ($width * $height <= 0 || $width < 0) {
			throw new InvalidImageDimensionException("Can't create an image with dimensions [$width, $height].");
		}
		
		return new TrueColorImage(imagecreatetruecolor($width, $height));
	}
	
	public function doCreate($width, $height)
	{
		return static::create($width, $height);
	}
	
	public function isTrueColor()
	{
		return true;
	}
	
	/**
	 * Sets alpha blending mode via imagealphablending()
	 *
	 * @param bool $mode
	 * @return bool
	 */
	public function alphaBlending($mode)
	{
		return imagealphablending($this->handle, $mode);
	}
	
	/**
	 * Toggle if alpha channel should be saved with the image via imagesavealpha()
	 *
	 * @param bool $on
	 * @return bool
	 */
	public function saveAlpha($on)
	{
		return imagesavealpha($this->handle, $on);
	}
	
	/**
	 * Allocates a color and returns its index
	 * 
	 * This method accepts either each component as an integer value,
	 * or an associative array that holds the color's components in keys
	 * 'red', 'green', 'blue', 'alpha'.
	 *
	 * @param mixed $R
	 * @param int $G
	 * @param int $B
	 * @param int $A
	 * @return int
	 */
	public function allocateColorAlpha($R, $G = null, $B = null, $A = null)
	{
		if (is_array($R)) {
			return imageColorAllocateAlpha($this->handle, $R['red'], $R['green'], $R['blue'], $R['alpha']);
		}
		
		return imageColorAllocateAlpha($this->handle, $R, $G, $B, $A);
	}
	
	/**
	 * @see \WideImage\Image#asPalette($nColors, $dither, $matchPalette)
	 */
	public function asPalette($nColors = 255, $dither = null, $matchPalette = true)
	{
		$nColors = intval($nColors);
		
		if ($nColors < 1) {
			$nColors = 1;
		} elseif ($nColors > 255) {
			$nColors = 255;
		}
		
		if ($dither === null) {
			$dither = $this->isTransparent();
		}
		
		$temp = $this->copy();
		
		imagetruecolortopalette($temp->handle, $dither, $nColors);
		
		if ($matchPalette == true && function_exists('imagecolormatch')) {
			imagecolormatch($this->handle, $temp->handle);
		}
		
		// The code below isn't working properly; it corrupts transparency on some palette->tc->palette conversions.
		// Why is this code here?
		/*
		if ($this->isTransparent())
		{
			$trgb = $this->getTransparentColorRGB();
			$tci = $temp->getClosestColor($trgb);
			$temp->setTransparentColor($tci);
		}
		/**/
		
		$temp->releaseHandle();
		
		return new PaletteImage($temp->handle);
	}
	
	/**
	 * Returns the index of the color that best match the given color components
	 *
	 * This method accepts either each component as an integer value,
	 * or an associative array that holds the color's components in keys
	 * 'red', 'green', 'blue', 'alpha'.
	 *
	 * @param mixed $R Red component value or an associative array
	 * @param int $G Green component
	 * @param int $B Blue component
	 * @param int $A Alpha component
	 * @return int The color index
	 */
	public function getClosestColorAlpha($R, $G = null, $B = null, $A = null)
	{
		if (is_array($R)) {
			return imagecolorclosestalpha($this->handle, $R['red'], $R['green'], $R['blue'], $R['alpha']);
		}
		
		return imagecolorclosestalpha($this->handle, $R, $G, $B, $A);
	}
	
	/**
	 * Returns the index of the color that exactly match the given color components
	 *
	 * This method accepts either each component as an integer value,
	 * or an associative array that holds the color's components in keys
	 * 'red', 'green', 'blue', 'alpha'.
	 *
	 * @param mixed $R Red component value or an associative array
	 * @param int $G Green component
	 * @param int $B Blue component
	 * @param int $A Alpha component
	 * @return int The color index
	 */
	public function getExactColorAlpha($R, $G = null, $B = null, $A = null)
	{
		if (is_array($R)) {
			return imagecolorexactalpha($this->handle, $R['red'], $R['green'], $R['blue'], $R['alpha']);
		}
		
		return imagecolorexactalpha($this->handle, $R, $G, $B, $A);
	}
	
	/**
	 * @see \WideImage\Image#getChannels()
	 */
	public function getChannels()
	{
		$args = func_get_args();
		
		if (count($args) == 1 && is_array($args[0])) {
			$args = $args[0];
		}
		
		return OperationFactory::get('CopyChannelsTrueColor')->execute($this, $args);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \WideImage\Image#copyNoAlpha()
	 */
	public function copyNoAlpha()
	{
		$prev   = $this->saveAlpha(false);
		$result = WideImage::loadFromString($this->asString('png'));
		$this->saveAlpha($prev);
		//$result->releaseHandle();
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \WideImage\Image#asTrueColor()
	 */
	public function asTrueColor()
	{
		return $this->copy();
	}
}
