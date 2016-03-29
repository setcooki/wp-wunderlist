<?php

if(!function_exists('wunderlist_nonce'))
{
	/**
	 * @param null $action
	 * @return string
	 */
	function wunderlist_nonce($action = null)
	{
		return substr(hash_hmac('md5', (\Setcooki\Wp\Request::getClientIp() . '|' . $action . '|' . time()), 'nonce'), -12, 10);
	}
}

if(!function_exists('wunderlist_linkify'))
{
	/**
	 * @param $string
	 * @param null $target
	 * @return mixed
	 */
	function wunderlist_linkify($string, $target = null)
	{
		return setcooki_linkify($string, $target);
	}
}


if(!function_exists('wunderlist_htmlize'))
{
	/**
	 * @param $html
	 * @return mixed|string
	 */
	function wunderlist_htmlize($html)
	{
		$html = trim($html);
		$pattern =
		'~(?xi)
              (?:
                ((ht|f)tps?://)                    # scheme://
                |                                  #   or
                www\d{0,3}\.                       # "www.", "www1.", "www2." ... "www999."
                |                                  #   or
                www\-                              # "www-"
                |                                  #   or
                [a-z0-9.\-]+\.[a-z]{2,4}(?=/)      # looks like domain name followed by a slash
              )
              (?:                                  # Zero or more:
                [^\s()<>]+                         # Run of non-space, non-()<>
                |                                  #   or
                \(([^\s()<>]+|(\([^\s()<>]+\)))*\) # balanced parens, up to 2 levels
              )*
              (?:                                  # End with:
                \(([^\s()<>]+|(\([^\s()<>]+\)))*\) # balanced parens, up to 2 levels
                |                                  #   or
                [^\s`!\-()\[\]{};:\'".,<>?«»“”‘’]  # not a space or one of these punct chars
              )
        ~';
		$html = preg_replace_callback($pattern, function($m)
		{
			if(!(bool)preg_match('~^(ht|f)tps?://~', $m[0]))
			{
				$m[0] = 'http://' . $m[0];
		    }
			if(preg_match('~\.(jpg|jpeg|png|gif|bmp|tif|tiff|svg)((\?|\#).*)?$~i', $m[0]))
			{
				$m[0] = (new \Setcooki\Wp\Wunderlist\Wunderlist\File\Image(array('url' => $m[0])))->render();
			}else{
				$m[0] = '<a href="'.$m[0].'" target="_blank">'.$m[0].'</a>';
			}
			return $m[0];
		}, $html);

		return $html;
	}
}