<?php
/*
Barcode Render Class for PHP using the GD graphics library
Copyright (C) 2001  Karim Mribti

   Version  0.0.7a  2001-04-01

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Copy of GNU Lesser General Public License at: http://www.gnu.org/copyleft/lesser.txt

Source code home page: http://www.mribti.com/barcode/
Contact author at: barcode@mribti.com
*/

//require('debug.php');

/* NB: all GD call's is here */

/* Styles */

/* Global */
define('BCS_BORDER'         ,    1);
define('BCS_TRANSPARENT'    ,    2);
define('BCS_ALIGN_CENTER'   ,    4);
define('BCS_ALIGN_LEFT'     ,    8);
define('BCS_ALIGN_RIGHT'    ,   16);
define('BCS_IMAGE_JPEG'     ,   32);
define('BCS_IMAGE_PNG'      ,   64);
define('BCS_IMAGE_GIF'      ,  128);
define('BCS_DRAW_TEXT'      ,  256);
define('BCS_STRETCH_TEXT'   ,  512);
//define('BCS_REVERSE_COLOR'  , 1024); // Unused
/* For the I25 Only  */
//define('BCS_I25_DRAW_CHECK' , 2048); // Unused

/* Default values */
//Margins
define('BCD_DEFAULT_MAR_Y1'          ,  10);
define('BCD_DEFAULT_MAR_Y2'          ,  10);
define('BCD_DEFAULT_TEXT_OFFSET'     ,   2);
//For the I25 Only
define('BCD_I25_NARROW_BAR'          ,   1);
define('BCD_I25_WIDE_BAR'            ,   2);
//For the C39 Only
define('BCD_C39_NARROW_BAR'          ,   1);
define('BCD_C39_WIDE_BAR'            ,   2);
//For Code 128
define('BCD_C128_BAR_1'              ,   1);
define('BCD_C128_BAR_2'              ,   2);
define('BCD_C128_BAR_3'              ,   3);
define('BCD_C128_BAR_4'              ,   4);

class BarcodeObject {
	var $mWidth, $mHeight, $mStyle, $mBgcolor, $mBrush;
	var $mImg, $mFont;
	var $mError;
	var $mColors;

	function __construct($Width = null, $Height = null, $Style = null) {
		$this->mWidth   = $Width;
		$this->mHeight  = $Height;
		$this->mStyle   = $Style;
		$this->mFont    = BCD_DEFAULT_FONT;
		$this->mImg  	= ImageCreate($this->mWidth, $this->mHeight);
		$this->mColors  = array(
			'aliceblue' => '#F0F8FF',
			'antiquewhite' => '#FAEBD7',
			'aqua' => '#00FFFF',
			'aquamarine' => '#7FFFD4',
			'azure' => '#F0FFFF',
			'beige' => '#F5F5DC',
			'bisque' => '#FFE4C4',
			'black' => '#000000',
			'blanchedalmond' => '#FFEBCD',
			'blue' => '#0000FF',
			'blueviolet' => '#8A2BE2',
			'brown' => '#A52A2A',
			'burlywood' => '#DEB887',
			'cadetblue' => '#5F9EA0',
			'chartreuse' => '#7FFF00',
			'chocolate' => '#D2691E',
			'coral' => '#FF7F50',
			'cornflowerblue' => '#6495ED',
			'cornsilk' => '#FFF8DC',
			'crimson' => '#DC143C',
			'cyan' => '#00FFFF',
			'darkblue' => '#00008B',
			'darkcyan' => '#008B8B',
			'darkgoldenrod' => '#B8860B',
			'darkgray' => '#A9A9A9',
			'darkgreen' => '#006400',
			'darkkhaki' => '#BDB76B',
			'darkmagenta' => '#8B008B',
			'darkolivegreen' => '#556B2F',
			'darkorange' => '#FF8C00',
			'darkorchid' => '#9932CC',
			'darkred' => '#8B0000',
			'darksalmon' => '#E9967A',
			'darkseagreen' => '#8FBC8F',
			'darkslateblue' => '#483D8B',
			'darkslategray' => '#2F4F4F',
			'darkturquoise' => '#00CED1',
			'darkviolet' => '#9400D3',
			'deeppink' => '#FF1493',
			'deepskyblue' => '#00BFFF',
			'dimgray' => '#696969',
			'dodgerblue' => '#1E90FF',
			'firebrick' => '#B22222',
			'floralwhite' => '#FFFAF0',
			'forestgreen' => '#228B22',
			'fuchsia' => '#FF00FF',
			'gainsboro' => '#DCDCDC',
			'ghostwhite' => '#F8F8FF',
			'gold' => '#FFD700',
			'goldenrod' => '#DAA520',
			'gray' => '#808080',
			'green' => '#008000',
			'greenyellow' => '#ADFF2F',
			'honeydew' => '#F0FFF0',
			'hotpink' => '#FF69B4',
			'indianred' => '#CD5C5C',
			'indigo' => '#4B0082',
			'ivory' => '#FFFFF0',
			'khaki' => '#F0E68C',
			'lavender' => '#E6E6FA',
			'lavenderblush' => '#FFF0F5',
			'lawngreen' => '#7CFC00',
			'lemonchiffon' => '#FFFACD',
			'lightblue' => '#ADD8E6',
			'lightcoral' => '#F08080',
			'lightcyan' => '#E0FFFF',
			'lightgoldenrodyellow' => '#FAFAD2',
			'lightgrey' => '#D3D3D3',
			'lightgreen' => '#90EE90',
			'lightpink' => '#FFB6C1',
			'lightsalmon' => '#FFA07A',
			'lightseagreen' => '#20B2AA',
			'lightskyblue' => '#87CEFA',
			'lightslategray' => '#778899',
			'lightsteelblue' => '#B0C4DE',
			'lightyellow' => '#FFFFE0',
			'lime' => '#00FF00',
			'limegreen' => '#32CD32',
			'linen' => '#FAF0E6',
			'magenta' => '#FF00FF',
			'maroon' => '#800000',
			'mediumaquamarine' => '#66CDAA',
			'mediumblue' => '#0000CD',
			'mediumorchid' => '#BA55D3',
			'mediumpurple' => '#9370D8',
			'mediumseagreen' => '#3CB371',
			'mediumslateblue' => '#7B68EE',
			'mediumspringgreen' => '#00FA9A',
			'mediumturquoise' => '#48D1CC',
			'mediumvioletred' => '#C71585',
			'midnightblue' => '#191970',
			'mintcream' => '#F5FFFA',
			'mistyrose' => '#FFE4E1',
			'moccasin' => '#FFE4B5',
			'navajowhite' => '#FFDEAD',
			'navy' => '#000080',
			'oldlace' => '#FDF5E6',
			'olive' => '#808000',
			'olivedrab' => '#6B8E23',
			'orange' => '#FFA500',
			'orangered' => '#FF4500',
			'orchid' => '#DA70D6',
			'palegoldenrod' => '#EEE8AA',
			'palegreen' => '#98FB98',
			'paleturquoise' => '#AFEEEE',
			'palevioletred' => '#D87093',
			'papayawhip' => '#FFEFD5',
			'peachpuff' => '#FFDAB9',
			'peru' => '#CD853F',
			'pink' => '#FFC0CB',
			'plum' => '#DDA0DD',
			'powderblue' => '#B0E0E6',
			'purple' => '#800080',
			'red' => '#FF0000',
			'rosybrown' => '#BC8F8F',
			'royalblue' => '#4169E1',
			'saddlebrown' => '#8B4513',
			'salmon' => '#FA8072',
			'sandybrown' => '#F4A460',
			'seagreen' => '#2E8B57',
			'seashell' => '#FFF5EE',
			'sienna' => '#A0522D',
			'silver' => '#C0C0C0',
			'skyblue' => '#87CEEB',
			'slateblue' => '#6A5ACD',
			'slategray' => '#708090',
			'snow' => '#FFFAFA',
			'springgreen' => '#00FF7F',
			'steelblue' => '#4682B4',
			'tan' => '#D2B48C',
			'teal' => '#008080',
			'thistle' => '#D8BFD8',
			'tomato' => '#FF6347',
			'turquoise' => '#40E0D0',
			'violet' => '#EE82EE',
			'wheat' => '#F5DEB3',
			'white' => '#FFFFFF',
			'whitesmoke' => '#F5F5F5',
			'yellow' => '#FFFF00',
			'yellowgreen' => '#9ACD32'
		);
		//__TRACE__("OBJECT CONSTRUCTION: {$this->mWidth} {$this->mHeight} {$this->mStyle}");
	}

	/*
	 * Color the barcode using our configurable colors.
	 *
	 * @author Zak Huber <zak@sciactive.com>
	 * @param string $background_color The background color.
	 * @param string $barcode_color The barcode and text color.
	 */
	function ColorObject($background_color, $barcode_color) {
		//Create a background for our image.
		if (isset($this->mColors[strtolower($background_color)]))
			$background_color = $this->mColors[strtolower($background_color)];
		if (preg_match('/^\d+,\d+,\d+$/', $background_color)) {
			$colors = explode(',', $background_color);
			$bgred = (int) $colors[0];
			$bggreen = (int) $colors[1];
			$bgblue = (int) $colors[2];
		} else if (preg_match('/^#[0-9a-fA-F]{3}$/', $background_color)) {
			$bgred = hexdec(substr($background_color, 1, 1).substr($background_color, 1, 1));
			$bggreen = hexdec(substr($background_color, 2, 1).substr($background_color, 2, 1));
			$bgblue = hexdec(substr($background_color, 3, 1).substr($background_color, 3, 1));
		} else if (preg_match('/^#[0-9a-fA-F]{6}$/', $background_color)) {
			$bgred = hexdec(substr($background_color, 1, 2));
			$bggreen = hexdec(substr($background_color, 3, 2));
			$bgblue = hexdec(substr($background_color, 5, 2));
		}
		$this->mBgcolor = ImageColorAllocate($this->mImg, (int) $bgred, (int) $bggreen, (int) $bgblue);
		// If transparency is enabled, set the transparent color to the bgcolor.
		if ($this->mStyle & BCS_TRANSPARENT)
			$this->mBgcolor = ImageColorTransparent($this->mImg, $this->mBgcolor);
		// Fill the image with the bgcolor.
		ImageFill($this->mImg, $this->mWidth, $this->mHeight, $this->mBgcolor);
		//Create a foreground brush with our specified color.
		if (isset($this->mColors[strtolower($barcode_color)]))
			$barcode_color = $this->mColors[strtolower($barcode_color)];
		if (preg_match('/^\d+,\d+,\d+$/', $barcode_color)) {
			$colors = explode(',', $barcode_color);
			$red = (int) $colors[0];
			$green = (int) $colors[1];
			$blue = (int) $colors[2];
		} else if (preg_match('/^#[0-9a-fA-F]{3}$/', $barcode_color)) {
			$red = hexdec(substr($barcode_color, 1, 1).substr($barcode_color, 1, 1));
			$green = hexdec(substr($barcode_color, 2, 1).substr($barcode_color, 2, 1));
			$blue = hexdec(substr($barcode_color, 3, 1).substr($barcode_color, 3, 1));
		} else if (preg_match('/^#[0-9a-fA-F]{6}$/', $barcode_color)) {
			$red = hexdec(substr($barcode_color, 1, 2));
			$green = hexdec(substr($barcode_color, 3, 2));
			$blue = hexdec(substr($barcode_color, 5, 2));
		}
		$this->mBrush = ImageColorAllocate($this->mImg, (int) $red, (int) $green, (int) $blue);
	}

	function DrawObject($xres) {
		/* there is not implementation neded, is simply the asbsract function. */
		//__TRACE__('OBJECT DRAW: NEED VIRTUAL FUNCTION IMPLEMENTATION');
		return false;
	}

	function DrawBorder() {
		ImageRectangle($this->mImg, 0, 0, $this->mWidth-1, $this->mHeight-1, $this->mBrush);
		//__TRACE__('DRAWING BORDER');
	}

	function DrawChar($Font, $xPos, $yPos, $Char) {
		ImageString($this->mImg,$Font,$xPos,$yPos,$Char,$this->mBrush);
	}

	function DrawText($Font, $xPos, $yPos, $Char) {
		ImageString($this->mImg,$Font,$xPos,$yPos,$Char,$this->mBrush);
	}

	function DrawSingleBar($xPos, $yPos, $xSize, $ySize) {
		if ($xPos >= 0 && $xPos <= $this->mWidth  && ($xPos + $xSize) <= $this->mWidth &&
			$yPos >= 0 && $yPos <= $this->mHeight && ($yPos + $ySize) <= $this->mHeight) {
			for ($i = 0; $i < $xSize; $i++) {
				ImageLine($this->mImg, $xPos + $i, $yPos, $xPos + $i, $yPos + $ySize, $this->mBrush);
			}
			return true;
		}
		//__DEBUG__('DrawSingleBar: Out of range');
		return false;
	}

	function GetError() {
		return $this->mError;
	}

	function GetFontHeight($font) {
		return ImageFontHeight($font);
	}

	function GetFontWidth($font) {
		return ImageFontWidth($font);
	}

	function SetFont($font) {
		$this->mFont = $font;
	}

	function GetStyle() {
		return $this->mStyle;
	}

	function SetStyle($Style) {
		//__TRACE__('CHANGING STYLE');
		$this->mStyle = $Style;
	}

	function FlushObject() {
		if (($this->mStyle & BCS_BORDER))
			$this->DrawBorder();
		if ($this->mStyle & BCS_IMAGE_PNG) {
			if (isset($this->filename)) {
				ImagePng($this->mImg, $this->filename);
				echo $this->filename;
			} else {
				Header('Content-Type: image/png');
				ImagePng($this->mImg);
			}
		} else if ($this->mStyle & BCS_IMAGE_JPEG) {
			if (isset($this->filename)) {
				ImageJpeg($this->mImg, $this->filename);
				echo $this->filename;
			} else {
				Header('Content-Type: image/jpeg');
				ImageJpeg($this->mImg);
			}
		} else if ($this->mStyle & BCS_IMAGE_GIF) {
			if (isset($this->filename)) {
				ImageGif($this->mImg, $this->filename);
				echo $this->filename;
			} else {
				Header('Content-Type: image/gif');
				ImageGif($this->mImg);
			}
		} //else __DEBUG__('FlushObject: No output type');
	}

	function DestroyObject() {
		ImageDestroy($obj->mImg);
	}
}
?>