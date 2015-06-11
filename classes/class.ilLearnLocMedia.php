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
require_once('./Services/MediaObjects/classes/class.ilObjMediaObject.php');

/**
 * Application class for LearnLoc Media Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *
 * $Id$
 */
class ilLearnLocMedia {

	const INIT_IMG = './Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/templates/images/init.jpg';
	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var array
	 */
	protected $path;
	/**
	 * @var array
	 */
	protected $images;
	/**
	 * @var int
	 */
	protected $height;
	/**
	 * @var int
	 */
	protected $width;
	/**
	 * @var array
	 */
	protected $options;
	/**
	 * @var string
	 */
	protected $link;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var array FileUpload
	 */
	protected $file;


	/**
	 * @param int $a_id
	 */
	function __construct($a_id = 0) {
		if ($a_id != 0) {
			$this->setId($a_id);
			//			$this->media_object = new ilObjMediaObject($this->getId());
			$this->read();
		} else {
			$this->media_object = new ilObjMediaObject();
		}
	}


	public function read() {
		$this->media_object = new ilObjMediaObject($this->getId());
		$this->setPath(ilObjMediaObject::_getDirectory($this->media_object->getId()));
		foreach ($this->media_object->getMediaItems() as $med) {
			$med->setPurpose($med->getId());
			//$med->update();
			$images[$med->getId()] = $med->location;
		}

		if (count($images) == 0) {
			$images[0] = self::INIT_IMG;
		}

		$this->setImages($images);
	}


	/**
	 * Create New LearnLoc Media Object
	 */
	public function create($id = null, $is_ref_id = false) {
		$this->media_object->setTitle($this->getTitle());
		$this->media_object->create();
		$this->media_object->createDirectory();
		$this->media_object->update();
		//		$this->media_object->setRefId();
		$this->setId($this->media_object->getId());
		$this->read();
		if ($is_ref_id) {
			$id = ilObject2::_lookupObjId($id);
		}
		if($id) {
			ilObjMediaObject::_saveUsage($this->media_object->getId(), 'mep', $id);
		}
	}


	public function addImage() {
		$media_item = new ilMediaItem();
		$this->media_object->addMediaItem($media_item);
		$file = $this->getFile();
		$file_upload = $this->getPath() . "/" . $file['image']['name'];
		ilUtil::moveUploadedFile($file['image']['tmp_name'], $file['image']['name'], $file_upload);
		$format = ilObjMediaObject::getMimeType($file_upload);
		$location = $file['image']['name'];
		$media_item->setFormat($format);
		$media_item->setLocation($location);
		$media_item->setLocationType("LocalFile");
		ilUtil::renameExecutables($this->getPath());
		$this->media_object->update();

		//$media_item->setPurpose($media_item->getId());
		//$media_item->update();
	}


	/**
	 * @param $id
	 */
	public function removeImage($id) {
		global $ilDB;

		$ilDB->query('DELETE FROM media_item WHERE id = ' . $ilDB->quote($id, 'integer') . ';');
		// TODO Bild löschen, wird von ilMediaItem nicht unterstützt
	}


	/**
	 * @return string
	 */
	public function getFirstImage() {
		$imgs = $this->getImages();
		@reset($imgs);
		$first_key = @key($imgs);

		return $this->getImageById($first_key);
	}


	/**
	 * @param $absolut_path
	 *
	 * @return string
	 */
	protected static function getRelativePath($absolut_path) {
		return 'data' . DIRECTORY_SEPARATOR . strstr($absolut_path, $_COOKIE['ilClientId']);
	}


	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function getImageById($id) {
		//$html = "<img src='" . self::getRelativePath($this->resize($id)) . "'>";
		$html = "<img src='" . self::getRelativePath($this->resize($id)) . "'>";

		if ($this->getLink()) {
			$a_open = "<a href='" . $this->getLink() . "'>";
			$a_close = "</a>";
		}

		return $a_open . $html . $a_close;
	}


	/**
	 * @return bool|string
	 */
	public function resizeFirstImage() {
		$imgs = $this->getImages();
		@reset($imgs);
		$first_key = @key($imgs);

		return $this->resize($first_key);
	}


	/**
	 * @param $img
	 *
	 * @return string
	 * @throws Exception
	 */
	public function resize($img) {
		$images = $this->getImages();
		if ($images[0] == self::INIT_IMG OR ilObject2::_lookupType($this->getId()) != 'mob') {
			//			return false;
		}
		$root = substr(__FILE__, 0, strpos(__FILE__, 'LearnLoc')) . 'LearnLoc';
		if ($images[$img] == $root . '/templates/images/init.jpg') {
			$imagePath = $images[$img];
		} elseif ($images[$img] == '') {
			$imagePath = $root . '/templates/images/init.jpg';
		} else {
			$imagePath = $this->getPath() . "/" . $images[$img];
		}

		$base = dirname($imagePath);

		if (! is_dir($base . "/cache") AND $base) {
			mkdir($base . "/cache");
			chmod($base . "/cache", 0755);
		}

		$cacheFolder = ILIAS_ABSOLUTE_PATH . '/' . str_ireplace('./', '', $base . "/cache/");
		$remoteFolder = '';
		$defaults = array(
			'crop' => false,
			'scale' => false,
			'thumbnail' => false,
			'maxOnly' => false,
			'canvas-color' => 'transparent',
			'output-filename' => false,
			'cacheFolder' => $cacheFolder,
			'remoteFolder' => $remoteFolder,
			'quality' => 90,
			'cache_http_minutes' => 20
		);

		$opts = array_merge($defaults, $this->getOptions());

		$cacheFolder = $opts['cacheFolder'];
		$remoteFolder = $opts['remoteFolder'];

		$path_to_convert = 'convert'; # this could be something like /usr/bin/convert or /opt/local/share/bin/convert
		$path_to_convert = PATH_TO_CONVERT; # this could be something like /usr/bin/convert or /opt/local/share/bin/convert

		//		$path_to_convert = '/usr/bin/convert'; # this could be something like /usr/bin/convert or /opt/local/share/bin/convert

		## you shouldn't need to configure anything else beyond this point

		$purl = parse_url($imagePath);
		$finfo = pathinfo($imagePath);
		$ext = $finfo['extension'];

		# chececho "!",k for remote image..
		//		if (isset($purl['scheme']) && $purl['scheme'] == 'http'): # grab the image, and cache it so we have something to work with..
		//		{
		//			list($filename) = explode('?', $finfo['basename']);
		//			$local_filepath = $remoteFolder . $filename;
		//
		//			$download_image = true;
		//			if (file_exists($local_filepath)):
		//				if (filemtime($local_filepath) < strtotime('+' . $opts['cache_http_minutes'] . ' minutes')):
		//					$download_image = false;
		//				endif;
		//			endif;
		//			if ($download_image == true):
		//				$img = file_get_contents($imagePath);
		//				file_put_contents($local_filepath, $img);
		//			endif;
		//			$imagePath = $local_filepath;
		//		}
		//		endif;

		$imagePath = ILIAS_ABSOLUTE_PATH . '/' . str_ireplace('./', '', $imagePath);

		if (file_exists($imagePath) == false):
			$imagePath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
			if (file_exists($imagePath) == false):
				//				throw new Exception('image not found');
			endif;
		endif;

		if (isset($opts['w'])): $w = $opts['w']; endif;
		if (isset($opts['h'])): $h = $opts['h']; endif;

		$filename = md5_file($imagePath);

		// If the user has requested an explicit output-filename, do not use the cache directory.
		if (false !== $opts['output-filename']) {
			$newPath = $opts['output-filename'];
		} else {
			if (! empty($w) and ! empty($h)):
				$newPath = $cacheFolder . $filename . '_w' . $w . '_h' . $h . (isset($opts['crop'])
					&& $opts['crop'] == true ? "_cp" : "") . (isset($opts['scale'])
					&& $opts['scale'] == true ? "_sc" : "") . '.' . $ext;
			elseif (! empty($w)):
				$newPath = $cacheFolder . $filename . '_w' . $w . '.' . $ext;
			elseif (! empty($h)):
				$newPath = $cacheFolder . $filename . '_h' . $h . '.' . $ext;
			else:
				//throw new Exception( 'image not found');
			endif;
		}

		$create = true;

		if (file_exists($newPath) == true):
			$create = false;
			$origFileTime = date("YmdHis", filemtime($imagePath));
			$newFileTime = date("YmdHis", filemtime($newPath));
			if ($newFileTime < $origFileTime): # Not using $opts['expire-time'] ??
			{
				$create = true;
			}
			endif;
		endif;

		if ($create == true):
			if (! empty($w) and ! empty($h)):

				list($width, $height) = getimagesize($imagePath);
				$resize = $w;

				if ($width > $height):
					$resize = $w;
					if (true === $opts['crop']):
						$resize = "x" . $h;
					endif;
				else:
					$resize = "x" . $h;
					if (true === $opts['crop']):
						$resize = $w;
					endif;
				endif;

				if (true === $opts['scale']):
					$cmd = $path_to_convert . " " . escapeshellarg($imagePath) . " -resize " . escapeshellarg($resize) . " -quality "
						. escapeshellarg($opts['quality']) . " " . escapeshellarg($newPath);
				else:
					$cmd = $path_to_convert . " " . escapeshellarg($imagePath) . " -resize " . escapeshellarg($resize) . " -size " . escapeshellarg($w
							. "x" . $h) . " xc:" . escapeshellarg($opts['canvas-color']) . " +swap -gravity center -composite -quality "
						. escapeshellarg($opts['quality']) . " " . escapeshellarg($newPath);

				endif;

			else:
				$cmd = $path_to_convert . " " . escapeshellarg($imagePath) . " -thumbnail " . (! empty($h) ? 'x' : '') . $w . ""
					. (isset($opts['maxOnly']) && $opts['maxOnly'] == true ? "\>" : "") . " -quality " . escapeshellarg($opts['quality']) . " "
					. escapeshellarg($newPath);
			endif;

			//ilUtil::execConvert(str_ireplace($path_to_convert, '' ,$cmd));

			exec($cmd);

			return $newPath;

		endif;

		return $newPath;
	}


	/**
	 * @param int $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}


	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param array $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}


	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}


	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * @param int $width
	 */
	public function setWidth($width) {
		$this->width = $width;
	}


	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}


	/**
	 * @param array $images
	 */
	public function setImages($images) {
		$this->images = $images;
	}


	/**
	 * @return array
	 */
	public function getImages() {
		return $this->images;
	}


	/**
	 * @param string $link
	 */
	public function setLink($link) {
		$this->link = $link;
	}


	/**
	 * @return string
	 */
	public function getLink() {
		return $this->link;
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
	 * @param array $file
	 */
	public function setFile($file) {
		$this->file = $file;
	}


	/**
	 * @return array
	 */
	public function getFile() {
		return $this->file;
	}
}