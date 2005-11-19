<?php
/**
 * DOMPDF - PHP5 HTML to PDF renderer
 *
 * File: $RCSfile: cpdf_adapter.cls.php,v $
 * Created on: 2004-08-04
 *
 * Copyright (c) 2004 - Benj Carson <benjcarson@digitaljunkies.ca>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library in the file LICENSE.LGPL; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * Alternatively, you may distribute this software under the terms of the
 * PHP License, version 3.0 or later.  A copy of this license should have
 * been distributed with this file in the file LICENSE.PHP .  If this is not
 * the case, you can obtain a copy at http://www.php.net/license/3_0.txt.
 *
 * The latest version of DOMPDF might be available at:
 * http://www.digitaljunkies.ca/dompdf
 *
 * @link http://www.digitaljunkies.ca/dompdf
 * @copyright 2004 Benj Carson
 * @author Benj Carson <benjcarson@digitaljunkies.ca>
 * @package dompdf
 * @version 0.3
 */

/* $Id: cpdf_adapter.cls.php,v 1.4 2005-11-19 01:33:41 benjcarson Exp $ */

// FIXME: Need to sanity check inputs to this class
require_once(DOMPDF_LIB_DIR . "/class.pdf.php");

/**
 * PDF rendering interface
 *
 * CPDF_Adapter provides a simple stateless interface to the stateful one
 * provided by the Cpdf class.
 *
 * Unless otherwise mentioned, all dimensions are in points (1/72 in).  The
 * coordinate origin is in the top left corner, and y values increase
 * downwards.
 *
 * See {@link http://www.ros.co.nz/pdf/} for more complete documentation
 * on the underlying {@link Cpdf} class.
 *
 * @package dompdf
 */
class CPDF_Adapter implements Canvas {

  /**
   * Dimensions of paper sizes in points
   *
   * @var array;
   */
  static $PAPER_SIZES = array("4a0" => array(0,0,4767.87,6740.79),
                              "2a0" => array(0,0,3370.39,4767.87),
                              "a0" => array(0,0,2383.94,3370.39),
                              "a1" => array(0,0,1683.78,2383.94),
                              "a2" => array(0,0,1190.55,1683.78),
                              "a3" => array(0,0,841.89,1190.55),
                              "a4" => array(0,0,595.28,841.89),
                              "a5" => array(0,0,419.53,595.28),
                              "a6" => array(0,0,297.64,419.53),
                              "a7" => array(0,0,209.76,297.64),
                              "a8" => array(0,0,147.40,209.76),
                              "a9" => array(0,0,104.88,147.40),
                              "a10" => array(0,0,73.70,104.88),
                              "b0" => array(0,0,2834.65,4008.19),
                              "b1" => array(0,0,2004.09,2834.65),
                              "b2" => array(0,0,1417.32,2004.09),
                              "b3" => array(0,0,1000.63,1417.32),
                              "b4" => array(0,0,708.66,1000.63),
                              "b5" => array(0,0,498.90,708.66),
                              "b6" => array(0,0,354.33,498.90),
                              "b7" => array(0,0,249.45,354.33),
                              "b8" => array(0,0,175.75,249.45),
                              "b9" => array(0,0,124.72,175.75),
                              "b10" => array(0,0,87.87,124.72),
                              "c0" => array(0,0,2599.37,3676.54),
                              "c1" => array(0,0,1836.85,2599.37),
                              "c2" => array(0,0,1298.27,1836.85),
                              "c3" => array(0,0,918.43,1298.27),
                              "c4" => array(0,0,649.13,918.43),
                              "c5" => array(0,0,459.21,649.13),
                              "c6" => array(0,0,323.15,459.21),
                              "c7" => array(0,0,229.61,323.15),
                              "c8" => array(0,0,161.57,229.61),
                              "c9" => array(0,0,113.39,161.57),
                              "c10" => array(0,0,79.37,113.39),
                              "ra0" => array(0,0,2437.80,3458.27),
                              "ra1" => array(0,0,1729.13,2437.80),
                              "ra2" => array(0,0,1218.90,1729.13),
                              "ra3" => array(0,0,864.57,1218.90),
                              "ra4" => array(0,0,609.45,864.57),
                              "sra0" => array(0,0,2551.18,3628.35),
                              "sra1" => array(0,0,1814.17,2551.18),
                              "sra2" => array(0,0,1275.59,1814.17),
                              "sra3" => array(0,0,907.09,1275.59),
                              "sra4" => array(0,0,637.80,907.09),
                              "letter" => array(0,0,612.00,792.00),
                              "legal" => array(0,0,612.00,1008.00),
                              "ledger" => array(0,0,1224.00, 792.00),
                              "tabloid" => array(0,0,792.00, 1224.00),
                              "executive" => array(0,0,521.86,756.00),
                              "folio" => array(0,0,612.00,936.00),
                              "8.5x11" => array(0,0,612.00,792.00),
                              "8.5x14" => array(0,0,612.00,1008.0),
                              "11x17"  => array(0,0,792.00, 1224.00));


  /**
   * Instance of Cpdf class
   *
   * @var Cpdf
   */
  private $_pdf;

  /**
   * PDF width, in points
   *
   * @var float
   */
  private $_width;

  /**
   * PDF height, in points
   *
   * @var float;
   */
  private $_height;

  /**
   * Current page number
   *
   * @var int
   */
  private $_page_number;

  /**
   * Total number of pages
   *
   * @var int
   */
  private $_page_count;

  /**
   * Text to display on every page
   *
   * @var string
   */
  private $_page_text;
  
  /**
   * Class constructor
   *
   * @param string $paper  The size of paper to use in this PDF ({@link CPDF_Adapter::$PAPER_SIZES})
   * @param string $orientation The orienation of the document (either 'landscape' or 'portrait')
   */
  function __construct($paper = "letter", $orientation = "portrait") {    
    if ( is_array($paper) )
      $size = $paper;
    else if ( array_key_exists(strtolower($paper), self::$PAPER_SIZES) )
      $size = self::$PAPER_SIZES[$paper];
    else
      $size = self::$PAPER_SIZES["letter"];

    if ( strtolower($orientation) == "landscape" ) {
      $a = $size[3];
      $size[3] = $size[2];
      $size[2] = $a;
    }
    
    $this->_pdf = new Cpdf($size);
    $this->_pdf->addInfo("Creator", "DOMPDF Converter");
    // Silence pedantic warnings about missing TZ settings
    $tz = @date_default_timezone_get();
    date_default_timezone_set("UTC");
    $this->_pdf->addInfo("CreationDate", date("Y-m-d"));
    date_default_timezone_set($tz);
    $this->_width = $size[2];
    $this->_height= $size[3];
    $this->_pdf->openHere('Fit');
    
    $this->_page_number = $this->_page_count = 1;
    $this->_page_text = null;

  }

  /**
   * Returns the Cpdf instance
   *
   * @return Cpdf
   */
  function get_cpdf() { return $this->_pdf; }

  /**
   * Opens a new 'object'
   *
   * While an object is open, all drawing actions are recored in the object,
   * as opposed to being drawn on the current page.  Objects can be added
   * later to a specific page or to several pages.
   *
   * The return value is an integer ID for the new object.
   *
   * @see CPDF_Adapter::close_object()
   * @see CPDF_Adapter::add_object()
   *
   * @return int
   */
  function open_object() {
    $ret = $this->_pdf->openObject();
    $this->_pdf->saveState();
    return $ret;
  }

  /**
   * Reopens an existing 'object'
   *
   * @see CPDF_Adapter::open_object()
   * @param int $object  the ID of a previously opened object
   */
  function reopen_object($object) {
    $this->_pdf->reopenObject($object);
    $this->_pdf->saveState();    
  }

  /**
   * Closes the current 'object'
   *
   * @see CPDF_Adapter::open_object()
   */
  function close_object() {
    $this->_pdf->restoreState();
    $this->_pdf->closeObject();
  }

  /**
   * Adds a specified 'object' to the document
   *
   * $object int specifying an object created with {@link
   * CPDF_Adapter::open_object()}.  $where can be one of:
   * - 'add' add to current page only
   * - 'all' add to every page from the current one onwards
   * - 'odd' add to all odd numbered pages from now on
   * - 'even' add to all even numbered pages from now on
   * - 'next' add the object to the next page only
   * - 'nextodd' add to all odd numbered pages from the next one
   * - 'nexteven' add to all even numbered pages from the next one
   *
   * @see Cpdf::addObject()
   *
   * @param int $object
   * @param string $where
   */
  function add_object($object, $where = 'all') {
    $this->_pdf->addObject($object, $where);
  }

  /**
   * Stops the specified 'object' from appearing in the document.
   *
   * The object will stop being displayed on the page following the current
   * one.
   *
   * @param int $object
   */
  function stop_object($object) {
    $this->_pdf->stopObject($object);
  }

  /**
   * @access private
   */
  function serialize_object($id) {
    // Serialize the pdf object's current state for retrieval later
    return $this->_pdf->serializeObject($id);
  }

  /**
   * @access private
   */
  function reopen_serialized_object($obj) {
    return $this->_pdf->restoreSerializedObject($obj);
  }
    
  //........................................................................

  /**
   * Returns the PDF's width in points
   * @return float
   */
  function get_width() { return $this->_width; }

  /**
   * Returns the PDF's height in points
   * @return float
   */
  function get_height() { return $this->_height; }

  /**
   * Returns the current page number
   * @return int
   */
  function get_page_number() { return $this->_page_number; }

  /**
   * Returns the total number of pages in the document
   * @return int
   */
  function get_page_count() { return $this->_page_count; }

  /**
   * Sets the current page number
   *
   * @param int $num
   */
  function set_page_number($num) { $this->_page_number = $num; }

  /**
   * Sets the page count
   *
   * @param int $count
   */
  function set_page_count($count) {  $this->_page_count = $count; }
    
  /**
   * Sets the stroke colour
   *
   * See {@link Style::set_colour()} for the format of the color array.
   * @param array $color
   */
  protected function _set_stroke_color($color) {
    list($r, $g, $b) = $color;
    $this->_pdf->setStrokeColor($r, $g, $b);
  }
  
  /**
   * Sets the fill colour
   *
   * See {@link Style::set_colour()} for the format of the colour array.
   * @param array $color
   */
  protected function _set_fill_color($color) {
    list($r, $g, $b) = $color;      
    $this->_pdf->setColor($r, $g, $b);
  }

  /**
   * Sets line transparency
   * @see Cpdf::setLineTransparency()
   *
   * Valid blend modes are (case-sensitive):
   *
   * Normal, Multiply, Screen, Overlay, Darken, Lighten,
   * ColorDodge, ColorBurn, HardLight, SoftLight, Difference,
   * Exclusion
   *
   * @param string $mode the blending mode to use
   * @param float $opacity 0.0 fully transparent, 1.0 fully opaque
   */
  protected function _set_line_transparency($mode, $opacity) {
    $this->_pdf->setLineTransparency($mode, $opacity);
  }
  
  /**
   * Sets fill transparency
   * @see Cpdf::setFillTransparency()
   *
   * Valid blend modes are (case-sensitive):
   *
   * Normal, Multiply, Screen, Overlay, Darken, Lighten,
   * ColorDogde, ColorBurn, HardLight, SoftLight, Difference,
   * Exclusion
   *
   * @param string $mode the blending mode to use
   * @param float $opacity 0.0 fully transparent, 1.0 fully opaque
   */
  protected function _set_fill_transparency($mode, $opacity) {
    $this->_pdf->setFillTransparency($mode, $opacity);
  }

  /**
   * Sets the line style
   *
   * @see Cpdf::setLineStyle()
   *
   * @param float width
   * @param string cap
   * @param string join
   * @param array dash
   */
  protected function _set_line_style($width, $cap, $join, $dash) {
    $this->_pdf->setLineStyle($width, $cap, $join, $dash);
  }
  
  //........................................................................

  
  /**
   * Remaps y coords from 4th to 1st quadrant
   *
   * @param float $y
   * @return float
   */
  protected function y($y) { return $this->_height - $y; }

  // Canvas implementation

  function line($x1, $y1, $x2, $y2, $color, $width, $style = array(),
                $blend = "Normal", $opacity = 1.0) {
    //pre_r(compact("x1", "y1", "x2", "y2", "color", "width", "style"));

    $this->_set_stroke_color($color);
    $this->_set_line_style($width, "butt", "", $style);
    $this->_set_line_transparency($blend, $opacity);
    
    $this->_pdf->line($x1, $this->y($y1),
                      $x2, $this->y($y2));
  }
                              
  //........................................................................


  function rectangle($x1, $y1, $w, $h, $color, $width, $style = array(),
                     $blend = "Normal", $opacity = 1.0) {

    $this->_set_stroke_color($color);
    $this->_set_line_style($width, "square", "miter", $style);
    $this->_set_line_transparency($blend, $opacity);
    
    $this->_pdf->rectangle($x1, $this->y($y1) - $h, $w, $h);
  }

  //........................................................................
  
  function filled_rectangle($x1, $y1, $w, $h, $color, $blend = "Normal", $opacity = 1.0) {

    $this->_set_fill_color($color);
    $this->_set_line_style(1, "square", "miter", array());
    $this->_set_line_transparency($blend, $opacity);
    $this->_set_fill_transparency($blend, $opacity);
    
    $this->_pdf->filledRectangle($x1, $this->y($y1) - $h, $w, $h);
  }

  //........................................................................

  function polygon($points, $color, $width = null, $style = array(),
                   $fill = false, $blend = "Normal", $opacity = 1.0) {

    $this->_set_fill_color($color);
    $this->_set_stroke_color($color);

    $this->_set_line_transparency($blend, $opacity);
    $this->_set_fill_transparency($blend, $opacity);
    
    if ( !$fill && isset($width) )
      $this->_set_line_style($width, "square", "miter", $style);
    
    // Adjust y values
    for ( $i = 1; $i < count($points); $i += 2)
      $points[$i] = $this->y($points[$i]);
    
    $this->_pdf->polygon($points, count($points) / 2, $fill);
  }

  //........................................................................

  function circle($x, $y, $r1, $color, $width = null, $style = null,
                  $fill = false, $blend = "Normal", $opacity = 1.0) {

    $this->_set_fill_color($color);
    $this->_set_stroke_color($color);
    
    $this->_set_line_transparency($blend, $opacity);
    $this->_set_fill_transparency($blend, $opacity);

    if ( !$fill && isset($width) )
      $this->_set_line_style($width, "round", "round", $style);

    $this->_pdf->filledEllipse($x, $this->y($y), $r1, 0, 0, 8, 0, 360, 1, $fill);

  }
  
  //........................................................................

  function image($img_url, $img_type, $x, $y, $w, $h) {

    switch ($img_type) {
    case "jpeg":
    case "jpg":
      $this->_pdf->addJpegFromFile($img_url, $x, $this->y($y) - $h, $w, $h);
      break;

    case "png":
      $this->_pdf->addPngFromFile($img_url, $x, $this->y($y) - $h, $w, $h);
      break;
      
    default:      
      break;
    }
    
    return;
  }

  //........................................................................

  function text($x, $y, $text, $font, $size, $color = array(0,0,0),
                $adjust = 0, $angle = 0, $blend = "Normal", $opacity = 1.0) {

    list($r, $g, $b) = $color;
    $this->_pdf->setColor($r, $g, $b);

    $this->_set_line_transparency($blend, $opacity);
    $this->_set_fill_transparency($blend, $opacity);
    $font .= ".afm";
    
    $this->_pdf->selectFont($font);
    $this->_pdf->addText($x, $this->y($y) - Font_Metrics::get_font_height($font, $size), $size, utf8_decode($text), $angle, $adjust);

  }

  //........................................................................

  function get_text_width($text, $font, $size, $spacing = 0) {
    $this->_pdf->selectFont($font);
    return $this->_pdf->getTextWidth($size, utf8_decode($text), $spacing);
  }

  //........................................................................

  function get_font_height($font, $size) {
    $this->_pdf->selectFont($font);
    return $this->_pdf->getFontHeight($size);
  }

  /**
   * Writes text at the specified x and y coordinates on every page
   *
   * The strings '{PAGE_NUM}' and '{PAGE_COUNT}' are automatically replaced
   * with their current values.
   *
   * See {@link Style::munge_colour()} for the format of the colour array.
   *
   * @param float $x
   * @param float $y
   * @param string $text the text to write
   * @param string $font the font file to use
   * @param float $size the font size, in points
   * @param array $color
   * @param float $adjust word spacing adjustment
   * @param float $angle angle to write the text at, measured CW starting from the x-axis
   */
  function page_text($x, $y, $text, $font, $size, $color = array(0,0,0),
                     $adjust = 0, $angle = 0,  $blend = "Normal", $opacity = 1.0) {
    
    $this->_page_text = compact("x", "y", "text", "font", "size", "color", "adjust", "angle");

    $this->_set_line_transparency($blend, $opacity);
    $this->_set_fill_transparency($blend, $opacity);

    // Add the text to the first page
    $text = str_replace(array("{PAGE_NUM}","{PAGE_COUNT}"),
                        array($this->_page_number, $this->_page_count), $text);
    $this->text($x, $y, $text, $font, $size, $color, $adjust, $angle);    
  }
  
  //........................................................................

  function new_page() {
    $this->_page_number++;

    $ret = $this->_pdf->newPage();
    if ( isset($this->_page_text) ) {
      extract($this->_page_text);
      $text = str_replace(array("{PAGE_NUM}","{PAGE_COUNT}"),
                          array($this->_page_number, $this->_page_count), $text);
      $this->text($x, $y, $text, $font, $size, $color, $adjust, $angle);
    }
    return $ret;
  }
  
  //........................................................................

  /**
   * Streams the PDF directly to the browser
   *
   * @param string $filename the name of the PDF file
   * @param array  $options associative array, 'Attachment' => 0 or 1, 'compress' => 1 or 0
   */
  function stream($filename, $options = null) {
    $options["Content-Disposition"] = $filename;
    $this->_pdf->stream($options);
  }

  //........................................................................

  /**
   * Returns the PDF as a string
   *
   * @return string
   */
  function output() {
    //return $this->_pdf->ezOutput(1);
    return $this->_pdf->output(1);
  }
  
  //........................................................................

  /**
   * Returns logging messages generated by the Cpdf class
   *
   * @return string
   */
  function get_messages() { return $this->_pdf->messages; }
  
}

?>