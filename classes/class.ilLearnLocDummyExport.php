<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once('./Services/Export/classes/class.ilExport.php');

/**
 * Application class for LearnLoc Dummy Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *
 * $Id$
 */
class ilLearnLocDummyExport {

	/**
	 * Coordinate System
	 */
	const DEC = 1; // Float

	const WGS84 = 2; // ° " '

	const CH1903 = 3; // Schweizer Koordinates-System (y, x)

	const UTM = 4; // MGRS / UTMREF-Koordinaten (Z, E, N)

	/**
	 * @var string
	 */
	protected $type;
	/**
	 * @var int
	 */
	protected $parent_obj;
	/**
	 * @var float
	 */
	protected $latitude;
	/**
	 * @var float
	 */
	protected $longitude;
	/**
	 * @var int
	 */
	protected $elevation;
	/**
	 * @var int
	 */
	protected $coordinate_system;
	/**
	 * @var int
	 */
	protected $time;
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var array
	 */
	protected $fields;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @param ilObjLearnLoc $parent_obj
	 * @param string        $type
	 */
	function __construct(ilObjLearnLoc $parent_obj, $type) {
		$this->setParentObj($parent_obj);
		$this->setType($type);
		$this->read();
	}

	/**
	 * Read All the Values
	 */
	public function read() {
		$this->setCoordinateSystem(self::DEC);
		$this->setLatitude($this->parent_obj->getLatitude());
		$this->setLongitude($this->parent_obj->getLongitude());
		$this->setTitle($this->parent_obj->getTitle());
		$this->setDescription($this->parent_obj->getDescription());
		$this->setElevation($this->parent_obj->getElevation());

		return true;
	}

	/**
	 * @param int $to
	 */
	public function convertCoordinateSystem($to) {
		$lat = $this->getLatitude();
		$lon = $this->getLongitude();

		switch($to) {
			case self::DEC :
				break;
			case self::WGS84 :
				$lat = $this->FLOATtoDEG($lat);
				$lon = $this->FLOATtoDEG($lon);
				break;
			case self::CH1903 :
				$lat = $this->WGStoCHx($lat, $lon);
				$lon = $this->WGStoCHy($lat, $lon);
				break;
			case self::UTM :
				require_once('lib/gPoint.php');
				$gpoint = new gPoint();
				$gpoint->setLongLat($lon, $lat);
				$gpoint->convertLLtoTM();
				$lon = $gpoint->N();
				$lat = $gpoint->E();
				break;
		}

		$this->setLatitude($lat);
		$this->setLongitude($lon);
	}

	/**
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function getExportData($data) {
		return "dummy";
	}

	final function buildExportFile($data = array()) {
		ilExport::_createExportDirectory($this->parent_obj->getId(), $this->getType(), "xlel");
		$file = ilExport::_getExportDirectory($this->parent_obj->getId(), $this->getType(), "xlel") . "/" . time() . "__" . IL_INST_ID . "__" . $this->parent_obj->getType() . "_" . $this->parent_obj->getId();
		file_put_contents($file . '.' . $this->getType(), $this->getExportData($data));
		ilUtil::zip($file . '.' . $this->getType(), $file . '.zip');
		//unlink($file . '.' . $this->getType());
	}


//
// SETTERS AND GETTERS
//

	/**
	 * @param int $parent_obj
	 */
	public function setParentObj($parent_obj) {
		$this->parent_obj = $parent_obj;
	}

	/**
	 * @return int
	 */
	public function getParentObj() {
		return $this->parent_obj;
	}

	/**
	 * @param int $coordinate_system
	 */
	public function setCoordinateSystem($coordinate_system) {
		$this->coordinate_system = $coordinate_system;
	}

	/**
	 * @return int
	 */
	public function getCoordinateSystem() {
		return $this->coordinate_system;
	}

	/**
	 * @param array $fields
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param float $latitude
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * @return float
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * @param float $longitude
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * @return float
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * @param int $elevation
	 */
	public function setElevation($elevation) {
		$this->elevation = $elevation;
	}

	/**
	 * @return int
	 */
	public function getElevation() {
		return $this->elevation;
	}


	/**
	 * @param int $ref_id
	 */
	public function setRefId($ref_id) {
		$this->ref_id = $ref_id;
	}

	/**
	 * @return int
	 */
	public function getRefId() {
		return $this->ref_id;
	}

	/**
	 * @param int $time
	 */
	public function setTime($time) {
		$this->time = $time;
	}

	/**
	 * @return int
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


//
// Helpers
//

	function SEXtoDEC($angle) {
		$deg = intval($angle);
		$min = intval(($angle - $deg) * 100);
		$sec = ((($angle - $deg) * 100) - $min) * 100;

		// Result in degrees sex (dd.mmss)
		return $deg + ($sec / 60 + $min) / 60;

	}

	function DECtoSEX($angle) {
		$deg = intval($angle);
		$min = intval(($angle - $deg) * 60);
		$sec = ((($angle - $deg) * 60) - $min) * 60;

		// Result in degrees sex (dd.mmss)
		return $deg + $min / 100 + $sec / 10000;

	}

	function DEGtoSEC($angle) {
		$deg = intval($angle);
		$min = intval(($angle - $deg) * 100);
		$sec = ((($angle - $deg) * 100) - $min) * 100;

		// Result in degrees sex (dd.mmss)

		return $sec + $min * 60 + $deg * 3600;

	}

	function FLOATtoDEG($angle) {
		$vars = explode(".", $angle);
		$deg = $vars[0];
		$tempma = "0.".$vars[1];
		$tempma = $tempma * 3600;
		$min = floor($tempma / 60);
		$sec = $tempma - ($min*60);

		return $deg."° ".$min.'"'.$sec."'";
	}

	function WGStoCHy($lat, $long) {

		// Converts degrees dec to sex
		$lat = $this->DECtoSEX($lat);
		$long = $this->DECtoSEX($long);

		// Converts degrees to seconds (sex)
		$lat = $this->DEGtoSEC($lat);
		$long = $this->DEGtoSEC($long);

		// Axiliary values (% Bern)
		$lat_aux = ($lat - 169028.66)/10000;
		$long_aux = ($long - 26782.5)/10000;

		// Process Y
		$y = 600072.37
			+ 211455.93 * $long_aux
			-  10938.51 * $long_aux * $lat_aux
			-      0.36 * $long_aux * pow($lat_aux,2)
			-     44.54 * pow($long_aux,3);

		return $y;
	}

// Convert WGS lat/long (� dec) to CH x
	function WGStoCHx($lat, $long) {

		// Converts degrees dec to sex
		$lat = $this->DECtoSEX($lat);
		$long = $this->DECtoSEX($long);

		// Converts degrees to seconds (sex)
		$lat = $this->DEGtoSEC($lat);
		$long = $this->DEGtoSEC($long);

		// Axiliary values (% Bern)
		$lat_aux = ($lat - 169028.66)/10000;
		$long_aux = ($long - 26782.5)/10000;

		// Process X
		$x = 200147.07
			+ 308807.95 * $lat_aux
			+   3745.25 * pow($long_aux,2)
			+     76.63 * pow($lat_aux,2)
			-    194.56 * pow($long_aux,2) * $lat_aux
			+    119.79 * pow($lat_aux,3);

		return $x;

	}

	function CHtoWGSlat($y, $x) {
		$y_aux = ($y - 600000)/1000000;
		$x_aux = ($x - 200000)/1000000;
		$lat = 16.9023892
			+  3.238272 * $x_aux
			-  0.270978 * pow($y_aux,2)
			-  0.002528 * pow($x_aux,2)
			-  0.0447   * pow($y_aux,2) * $x_aux
			-  0.0140   * pow($x_aux,3);
		$lat = $lat * 100/36;

		return $lat;

	}

	function CHtoWGSlon($y, $x) {
		$y_aux = ($y - 600000)/1000000;
		$x_aux = ($x - 200000)/1000000;
		$long = 2.6779094
			+ 4.728982 * $y_aux
			+ 0.791484 * $y_aux * $x_aux
			+ 0.1306   * $y_aux * pow($x_aux,2)
			- 0.0436   * pow($y_aux,3);
		$long = $long * 100/36;

		return $long;

	}

}