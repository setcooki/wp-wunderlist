<?php

namespace Setcooki\Wp\Wunderlist\Entity;

use Setcooki\Wp\Wunderlist\Wunderlist\File\Image;
use Setcooki\Wp\Wunderlist\Wunderlist\File\Media;

/**
 * Class Task
 * @package Setcooki\Wp\Wunderlist\Entity
 */
class Task extends Entity
{
	public $id = null;
	public $created_at = null;
	public $due_date = null;
	public $list_id = null;
	public $revision = null;
	public $starred = null;
	public $title = null;
	public $note = null;
	public $has_note = false;
	public $has_files = false;
	public $files = null;
	public $has_banner = false;
	public $banner = null;


	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return wunderlist_linkify($this->title, '_blank');
	}


	/**
	 * @return bool
	 */
	public function hasFiles()
	{
		return $this->has_files;
	}


	/**
	 * @param int $index
	 *
	 * @return mixed|string
	 */
	public function getFiles($index = 0)
	{
		if($this->has_files)
		{
			$file = $this->files[(int)$index];
			switch($file->content_type)
			{
				case 'image/jpeg':
					$file = new Image($file);
					break;
				default:
			}
			return $file->render();
		}
		return '';
	}


	/**
	 * @return bool
	 */
	public function hasNote()
	{
		return $this->has_note;
	}


	/**
	 * @return string
	 */
	public function getNote()
	{
		if($this->has_note)
		{
			return wunderlist_htmlize($this->note->content);
		}
		return '';
	}


	/**
	 * @return bool|null
	 */
	public function hasBanner()
	{
		return ($this->getBanner() !== '') ? true : false;
	}


	/**
	 * @return string
	 */
	public function getBanner()
	{
		if($this->banner === null)
		{
			//task has file(s)
			if($this->has_files)
			{
				$banner = $this->getFiles();
			//note has link at beginning
			}else if($this->has_note && preg_match('=^\s*((?:http(?:s)?\:\/\/[^\s]{1,}))=im', $this->note->content, $m)){
				//first pass
				if(preg_match('=\.(jpg|jpeg|png|gif|bmp|tif|tiff|svg)((\?|\#).*)?$=i', $m[1]))
				{
					$banner = (new Image(array('url' => $m[1])))->render();
				}else{
					$banner = (new Media(array('url' => $m[1])))->render();
				}
				//second pass
				if(empty($banner))
				{
					if(($headers = get_headers($m[1], 1)) !== false && array_key_exists('Content-Type', $headers))
					{
						if(stristr(strtolower($headers['Content-Type']), 'image/'))
						{
							$banner = (new Image(array('url' => $m[1])))->render();
						}else{
							$banner = ''; //not supported as featured media
						}
					}
					unset($headers);
				}
				if(!empty($banner))
				{
					$this->note->content = trim(substr($this->note->content, strlen($m[1]) + 1));
				}
			}
			if(!empty($banner))
			{
				$this->has_banner = true;
				return $this->banner = $banner;
			}else{
				$this->has_banner = false;
				return $this->banner = '';
			}
		}else{
			return (string)$this->banner;
		}
	}
}