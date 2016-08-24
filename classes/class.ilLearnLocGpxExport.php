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
require_once('class.ilLearnLocDummyExport.php');

/**
 * Application class for LearnLoc Gpx Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *
 * $Id$
 */
class ilLearnLocGpxExport extends ilLearnLocDummyExport {

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	public function getExportData($data) {
		/*


		<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="Wikipedia"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">
 <!-- Kommentare sehen so aus -->
 <metadata>
  <name>Dateiname</name>
  <desc>Validiertes GPX-Beispiel ohne Sonderzeichen</desc>
  <author>
   <name>Autorenname</name>
  </author>
 </metadata>
 <wpt lat="52.518611" lon="13.376111">
  <ele>35.0</ele>
  <time>2011-12-31T23:59:59Z</time>
  <name>Reichstag (Berlin)</name>
  <sym>City</sym>
 </wpt>
</gpx>

		 */
//		$xml = new SimpleXMLElement("<gpx></gpx>");
		$xml = simplexml_load_file('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/garmin.gpx');
		$xml->addAttribute('creator', 'ILIAS LearnLoc');


		$metadata = $xml->addChild('metadata');
		$metadata->addChild("name", $this->getTitle());
		$metadata->addChild("desc", $this->getDescription());
		$metadata->addChild("copyright", "ILIAS E-Learning");
		$metadata->addChild("time", time());

		if(count($data) > 0) {
			$extensions = $metadata->addChild("extensions");
			foreach($data as $k => $v) {
				$extensions->addChild($k, $v);
			}
		}

		$wp = $xml->addChild('wpt');
		$wp->addAttribute('lat', (float)$this->getLatitude());
		$wp->addAttribute('lon', (float)$this->getLongitude());
		$wp->addChild("time", time());
		$wp->addChild("ele", 500);
		$wp->addChild("sym", "Waypoint");
		$wp->addChild("name", $this->getTitle());
		$wp->addChild("desc", $this->getDescription());
		$extensions = $wp->addChild("extensions");
		$gpxxWaypointExtension = $extensions->addChild("gpxx:WaypointExtension");
		$gpxxWaypointExtension->addChild("gpxx:DisplayMode", "SymbolAndName");


		return $xml->asXML();
	}
}