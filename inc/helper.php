<?php

if(!function_exists('wunderlist_nonce'))
{
	function wunderlist_nonce()
	{
		return \Setcooki\Wp\Request::getClientIp();
	}
}