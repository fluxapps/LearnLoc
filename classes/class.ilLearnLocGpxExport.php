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

require_once('./Services/Database/classes/class.ilDB.php');
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
	public function getExportData($data) {
		$xml = new SimpleXMLElement("<gpx></gpx>");
		$xml->addAttribute('encoding', 'UTF-8');

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
		$wp->addAttribute('lat', $this->getLatitude());
		$wp->addAttribute('lon', $this->getLongitude());
		$wp->addChild("time", time());
		$wp->addChild("ele", 500);
		$wp->addChild("name", $this->getTitle());
		$wp->addChild("desc", $this->getDescription());

		return $xml->asXML();
	}
}