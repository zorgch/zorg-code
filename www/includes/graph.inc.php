<?php
/**
 * The Line Graph generator by Ashish Kasturia (http://www.123ashish.com)
 *
 * Copyright (C) 2003 Ashish Kasturia (ashish at 123ashish.com)
 * 
 * The Line Graph generator is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
 * USA.
 *
 * @link http://developers-heaven.net/display_source_code.php?id=117
 * @link http://www.123ashish.com/
 *
 * @package zorg\Vendor
 * @author [z]cylander
 * @version 2.0
 * @since 1.0 `[z]cylander` File added
 * @since 1.5 `[z]cylander` zorg customizing
 * @since 2.0 `IneX` Fixed __construct, added Jahreszahlen
 */
class Line
{
	var $width = 600;
	var $height = 300;
	var $bgCol;

	var $title = "Bar Graph - 123ashish.com";
	var $titleCol;

	var $dataValues = array();
	var $dataXLabels = array();
	var $dataXLabels2 = array();
	var $dataSeriesLabels = array();
	var $barCol = array();
	var $axesCol;
	var $image;

	/**
	 * @version 2.0
	 * @since 1.0 `ashish` Original Method
	 * @since 2.0 `09.12.2019` `IneX` Fixed Class constructor from "function Line()" => __construct(), added option to pass imgage width & height
	 */
	function __construct($imgWidth=600, $imgHeight=300)
	{
		$this->width = $imgWidth;
		$this->height = $imgHeight;
		$this->image = ImageCreate($this->width, $this->height);
		$this->InitializeColors();
		ImageFill($this->image, 0, 0, $this->bgCol);	
	}

	function InitializeColors()
	{
		$this->bgCol = ImageColorAllocate($this->image, 255, 255, 255);
		$this->titleCol = ImageColorAllocate($this->image, 0, 0, 0);

		$this->barCol[0] = ImageColorAllocate($this->image, 0, 0, 200);
		$this->barCol[1] = ImageColorAllocate($this->image, 0, 255, 0);
		$this->barCol[2] = ImageColorAllocate($this->image, 255, 0, 0);
		$this->barCol[3] = ImageColorAllocate($this->image, 0, 255, 255);
		$this->barCol[4] = ImageColorAllocate($this->image, 255, 0, 255);
		$this->barCol[5] = ImageColorAllocate($this->image, 255, 255, 0);
		$this->barCol[6] = ImageColorAllocate($this->image, 100, 100, 55);
		$this->barCol[7] = ImageColorAllocate($this->image, 55, 100, 100);
		$this->barCol[8] = ImageColorAllocate($this->image, 100, 55, 100);

		$this->axesCol = ImageColorAllocate($this->image, 100, 100, 100);
	}

	function SetDimensions($width, $height)
	{
		ImageDestroy($this->image);
		$this->height = $height;
		$this->width = $width;

		$this->image = ImageCreate($this->width, $this->height);
		InitializeColors();
		ImageFill($this->image, 0, 0, $this->bgCol);
	}

	function SetBGJPEGImage($file)
	{
		// SetBGJPEGImage() and SetDimensions() cannot be used together
		ImageDestroy($this->image);
		$this->image = ImageCreateFromJPEG($file);
		$this->width = ImageSX($this->image);
		$this->height = ImageSY($this->image);
		$this->InitializeColors();
	}
	function SetBGPngImage($file)
	{
		// SetBGPngImage() and SetDimensions() cannot be used together
		ImageDestroy($this->image);
		$this->image = ImageCreateFromPng($file);
		$this->width = ImageSX($this->image);
		$this->height = ImageSY($this->image);
		$this->InitializeColors();
	}
	function SetBGGifImage($file)
	{
		// SetBGGifImage() and SetDimensions() cannot be used together
		ImageDestroy($this->image);
		$this->image = ImageCreateFromGif($file);
		$this->width = ImageSX($this->image);
		$this->height = ImageSY($this->image);
		$this->InitializeColors();
	}
	function SetBGColor($bgR, $bgG, $bgB)
	{
		ImageColorDeallocate($this->image, $this->bgCol);
		$this->bgCol = ImagecolorAllocate($this->image, $bgR, $bgG, $bgB);
	}

	function SetTitle($title)
	{
		// @FIXME 09.12.2019: Needs a different Font to support UTF8 - aktuell Umlaute "garbled" (IneX)
		$this->title = $title;
	}

	function SetTitleColor($bgR, $bgG, $bgB)
	{
		ImageColorDeallocate($this->image, $this->titleCol);
		$this->titleCol = ImagecolorAllocate($this->image, $bgR, $bgG, $bgB);
	}

	function AddValue($xVal, $yVal, $xVal2)
	{
		// $yVal is an array of y values
		$this->dataValues[] = $yVal;
		$this->dataXLabels[] = $xVal;
		$this->dataXLabels2[] = $xVal2;
	}

	function SetSeriesLabels($labels)
	{
		$this->dataSeriesLabels = $labels;
	}

	function SetBarColor($bgR, $bgG, $bgB)
	{
		ImageColorDeallocate($this->image, $this->barCol);
		$this->barCol = ImagecolorAllocate($this->image, $bgR, $bgG, $bgB);
	}

	function SetAxesColor($bgR, $bgG, $bgB)
	{
		ImageColorDeallocate($this->image, $this->axesCol);
		$this->axesCol = ImagecolorAllocate($this->image, $bgR, $bgG, $bgB);
	}

	/**
	 * Spit out the graph
	 *
	 * @version 3.0
	 * @since 1.0 `ashish` Original Method
	 * @since 2.0 `[z]cylander` zorg customizing, disabled some defaults
	 * @since 2.5 `[z]cylander` added option for 2nd x-axis label bar (Jahreszahlen)
	 * @since 3.0 `09.12.2019` `IneX` Enabled 2nd x-axis label by [z]cylander, some Styling improvement
	 */
	function spit($type)
	{
		$black = ImageColorAllocate($this->image, 0, 0, 0);

		// draw the box
		/*ImageLine($this->image, 0, 0, $this->width - 1, 0, $black);
		ImageLine($this->image, $this->width - 1, 0, $this->width - 1, $this->height - 1, $black);
		ImageLine($this->image, $this->width - 1, $this->height - 1, 0, $this->height - 1, $black);
		ImageLine($this->image, 0, $this->height - 1, 0, 0, $black);*/

		// draw the axes
		// Y
		for($i = 0; $i <= 4; $i++)
		{
			$tmpVal = 4 - $i;
			$y1 = 40 + (($tmpVal * ($this->height - 80)) / 4);
			ImageLine($this->image, 40, $y1, $this->width - 80, $y1, $this->axesCol);
		}
		// X
		ImageLine($this->image, 40, $this->height - 40, 40, 40, $this->axesCol);
		ImageLine($this->image, $this->width - 80, $this->height - 40, $this->width - 80, 40, $this->axesCol);

		// calculate the max of each range
		$tmpArray = Array();
		$maxValues = Array();
		$numSequences = sizeof($this->dataValues[0]);

		for($i = 0; $i < $numSequences; $i++)
		{
			$tmpArray[$i] = Array();
			for($j = 0; $j < sizeof($this->dataValues); $j++)
			{
				$tmpArray[$i][] = $this->dataValues[$j][$i];
			}
		}

		for($i = 0; $i < $numSequences; $i++)
		{
			$maxValues[$i] = max($tmpArray[$i]);
		}

		// put the y axis values
		for($i = 0; $i <= 4; $i++)
		{
			$tmpVal = 4 - $i;
			$y1 = 40 + (($i * ($this->height - 80)) / 4);
			
			for($j = 0; $j < $numSequences; $j++)
			{
				$str = sprintf("%.2f", ($maxValues[$j] * (4 - $i) / 4));
				$strHeight = ImageFontHeight(2);
				ImageString($this->image, 2, 5, $y1 + (($j - $numSequences / 2) * $strHeight), $str, $this->barCol[$j % 9]);
			}
		}
		
		// put the title
		$titleWidth = ImageFontWidth(3) * strlen($this->title);
		ImageString($this->image, 3, ($this->width - $titleWidth) / 2, 10, $this->title, $this->titleCol);

		// put the series legend
		/*$legendWidth = ImageFontWidth(3) * strlen("Legend");
		ImageString($this->image, 3, $this->width - $legendWidth - 5, 40, "Legend", $this->titleCol);
		for($i = 0; $i < sizeof($this->dataSeriesLabels); $i++)
		{
			$legendWidth = ImageFontWidth(3) * strlen($this->dataSeriesLabels[$i]);
			ImageString($this->image, 3, $this->width - $legendWidth - 5, 60 + $i * ImageFontWidth(2) * 2, $this->dataSeriesLabels[$i], $this->barCol[$i % 9]);
		}*/

		// divide the area for the values
		$xUnit = ($this->width - 120) / sizeof($this->dataValues);
		$xUnit2 = ($this->width - 120) / sizeof($this->dataXLabels2);
		
		// finally draw the graphs
		$x2 = Array();
		$y2 = Array();		
		for($i = 0; $i < sizeof($this->dataValues); $i++)
		{ $n++;
			$labelWidth = ImageFontWidth(1) * strlen($this->dataXLabels[$i]);
			$labelHeight = ImageFontHeight(1);

			ImageString($this->image, (count($this->dataXLabels2) > 1 ? 1 : 2), 
				40 + $xUnit * ($i + 0.5) - $labelWidth / 2, 
				$this->height - 35 + ($i % 2) * $labelHeight, 
				$this->dataXLabels[$i], $this->titleCol); // 2nd param = font-size. Higher = larger (only if dataXLabels2 = empty)
			
			/** @TODO Dieser String sollte nun die Jahreszahlen ausgeben...?! ([z]cylander) */
			ImageString($this->image, 2, 
				40 + $xUnit2 * ($i + 1) - $labelWidth / 2, 
				$this->height - 22 + ($i % 2) * $labelHeight, 
				$this->dataXLabels2[$i], $this->titleCol); // 2nd param = font-size. Higher = larger.

			for($j = 0; $j < sizeof($this->dataValues[$i]); $j++)
			{
				$x1 = 40 + ($xUnit * ($i + 0.5));

				// $maxValues[$j] corresponds to $this->height - 80
				$tmpVal = $maxValues[$j] - $this->dataValues[$i][$j];
				// $tmpVal corresponds to ($tmpVal * ($this->height - 80)) / $maxValues[$j];
				$y1 = 40 + (($tmpVal * ($this->height - 80)) / $maxValues[$j]);
				ImageFilledRectangle($this->image, $x1 - 2, $y1 - 2, $x1 + 2, $y1 + 2, $this->barCol[$j % 9]);
				if($i != 0)
				{
					ImageLine($this->image, $x1, $y1, $x2[$j], $y2[$j], $this->barCol[$j % 9]);
				}
				$x2[$j] = $x1;
				$y2[$j] = $y1;
			}
		}

		if($type == "jpg")
		{
			Header("Content-type: image/jpeg");
			ImageJpeg($this->image);
		}
		if($type == "png")
		{
			Header("Content-type: image/png");
			ImagePng($this->image);
		}
		if($type == "gif")
		{
			Header("Content-type: image/gif");
			ImageGif($this->image);
		}

		ImageDestroy($this->image);
	}
}
