<?php

namespace Setcooki\Wp\Wunderlist\Wunderlist\File;

/**
 * Class Image
 * @package Setcooki\Wp\Wunderlist\Wunderlist\File
 */
class Image extends File
{
	/**
	 *
	 */
	public function render()
	{
		if(isset($this->params->width))
		{
			$width = (int)$this->params->width;
		}else{
			$width = '';
		}
		if(isset($this->params->height))
		{
			$height = (int)$this->params->height;
		}else{
			$height = '';
		}
		if(isset($this->params->file_name))
		{
			$alt = setcooki_basename($this->params->file_name, '.*');
		}else{
			$alt = '';
		}

		$html =
		'
			<img src="'.$this->params->url.'" width="'.$width.'" height="'.$height.'" alt="'.$alt.'" />
		';
		return $html;
	}
}