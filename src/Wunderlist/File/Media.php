<?php

namespace Setcooki\Wp\Wunderlist\Wunderlist\File;

use MediaEmbed\MediaEmbed;

/**
 * Class Media
 * @package Setcooki\Wp\Wunderlist\Wunderlist\File
 */
class Media extends File
{
	/**
	 *
	 */
	public function render()
	{
		$media = new MediaEmbed();
		$object = $media->parseUrl($this->params->url);
		if(!empty($object))
		{
			return '<div class="wpwl-media">' . $object->getEmbedCode() . '</div>';
		}else{
			return '';
		}
	}
}
