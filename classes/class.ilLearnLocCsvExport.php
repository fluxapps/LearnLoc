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

require_once('class.ilLearnLocDummyExport.php');

/**
 * Application class for LearnLoc Csv Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *
 * $Id$
 */

class ilLearnLocCsvExport extends ilLearnLocDummyExport {
	const SEP = ",";

	/**
	 * @var string
	 */
	protected $csv_data;

	/**
	 * @param ilObjLearnLoc $parent_obj
	 * @param string        $type
	 */
	function __construct(ilObjLearnLoc $parent_obj, $type) {
		parent::__construct($parent_obj, $type);
		$this->convertCoordinateSystem(self::UTM);
	}

	/**
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function getExportData($data) {
		$this->addCsvHeader($data);
		$this->addCsvBody($data);

		return $this->getCsvData();
	}

	/**
	 * @param $data
	 */
	public function addCsvHeader($data) {
		$headers = array('FID_', 'elevation', 'name', 'POINT_X', 'POINT_Y');
		$this->addCsvData(implode(self::SEP, array_merge($headers, array_keys($data))));
	}

	/**
	 * @param $data
	 */
	public function addCsvBody($data) {
		$body = array($this->parent_obj->getId(), $this->getElevation(), $this->getTitle(), $this->getLatitude(), $this->getLongitude());
		$this->addCsvData(implode(self::SEP, array_merge($body, array_values($data))));
	}

	/**
	 * @param string $csv_data
	 */
	public function setCsvData($csv_data) {
		$this->csv_data = $csv_data;
	}

	/**
	 * @return string
	 */
	public function getCsvData() {
		return $this->csv_data;
	}

	/**
	 * @param string $csv_data
	 */
	public function addCsvData($csv_data) {
		$this->csv_data = $this->csv_data.$csv_data."\n\r";
	}
}