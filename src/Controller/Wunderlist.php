<?php

namespace Setcooki\Wp\Wunderlist\Controller;

use Setcooki\Wp\Controller\Front;
use Setcooki\Wp\Controller\View\View;
use Setcooki\Wp\Option;
use Setcooki\Wp\Template;
use Setcooki\Wp\Util\Params;
use Setcooki\Wp\Wp;
use Setcooki\Wp\Wunderlist\Exception;

/**
 * Class Wunderlist
 * @package Setcooki\Wp\Wunderlist\Controller\Front
 */
class Wunderlist extends Front
{
	/**
	 * Settings constructor.
	 *
	 * @param Wp $wp
	 * @param null $options
	 */
	public function __construct(Wp $wp, $options = null)
	{
		parent::__construct($wp, $options);
	}


	/**
	 * @param Params $params
	 *
	 * @return string
	 */
	public function render(Params $params)
	{
		try
		{
			if($params->is('id'))
            {
	            $id = (int)$params->get('id');
				if($this->whitelist($id))
				{
					if(Option::get('wp_wunderlist_options.live.mode') === 'push')
					{
				        $this->wp->webhook->create($id, 'list');
					}

					$view       = apply_filters('wp_wunderlist_view', $this->getView($params));
                    $template   = apply_filters('wp_wunderlist_template', $this->getTemplate($params));
					$class      = basename($view, '.php');

                    require_once $view;

                    if(class_exists($class))
                    {
                        if(stristr($template, '.mustache') !== false)
                        {
                            $options = array
                            (
                                'loader' => new \Mustache_Loader_FilesystemLoader(dirname($template)),
                            );
                            $view = new $class($this, new \Mustache_Engine($options));
                            if($view->execute((array)$params) !== false)
                            {
	                            $vars = apply_filters('wp_wunderlist_vars', $view->vars());
	                            $template = $view->view->loadTemplate('index');
	                            return $template->render($vars);
                            }else{
	                            return '';
                            }
                        }else{
                            $view = new $class($this);
                            if($view->execute((array)$params) !== false)
                            {
	                            $vars = apply_filters('wp_wunderlist_vars', $view->vars());
	                            extract($vars);
	                            ob_start();
	                            require $template;
	                            return ob_get_clean();
                            }else{
	                            return '';
                            }
                        }
                    }else{
						throw new Exception("view class not found");
                    }
				}else{
					throw new Exception("not in whitelist");
				}
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}


	/**
	 * @param $id
	 * @return bool
	 */
	protected function whitelist($id)
	{
		$whitelist = Option::get('wp_wunderlist_options.security.whitelist', '');
		if(!empty($whitelist))
		{
			$whitelist = array_map(function(&$value)
			{
				return $value = (int)trim($value);
			}, array_unique(explode("\r\n", trim($whitelist))));
		}else{
			$whitelist = array();
		}
		return (in_array($id, $whitelist)) ? true : false;
	}


	/**
	 * @param Params $params
	 * @return string
	 * @throws Exception
	 */
	protected function getView(Params $params)
	{
		$root = rtrim(setcooki_path('root'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
 		$path = rtrim(setcooki_get_option('VIEW_PATH', $this->wp->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if($params->has('view'))
		{
			if(stripos($params->get('view'), DIRECTORY_SEPARATOR) !== false)
            {
                $view = $root . trim($params->get('view'), ' ' . DIRECTORY_SEPARATOR);
            }else{
				$view = $root . $path . trim($params->get('view'), ' ' . DIRECTORY_SEPARATOR);
            }
		}else{
			if(Option::has('wp_wunderlist_options.list.default_view', true))
			{
				$view = $root . ltrim(Option::get('wp_wunderlist_options.list.default_view'), DIRECTORY_SEPARATOR);
			}else{
				$view = $root . $path . 'Standard.php';
			}
		}
		if(is_file($view))
		{
			return $view;
		}else{
			throw new Exception("shit");
		}
	}


	/**
	 * @param Params $params
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function getTemplate(Params $params)
	{
		$root = rtrim(setcooki_path('root'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$path = rtrim(setcooki_get_option('TEMPLATE_PATH', $this->wp->front), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if($params->has('template'))
        {
	        if(stripos($params->get('template'), DIRECTORY_SEPARATOR) !== false)
	        {
	            $template = $root . trim($params->get('template'), ' ' . DIRECTORY_SEPARATOR);
	        }else if(stripos($params->get('template'), '.') !== false){
	     	    $template = $root . $path . trim($params->get('template'), ' ' . DIRECTORY_SEPARATOR);
	        }else{
		        $template = $root . $path . trim($params->get('template'), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.mustache';
	        }
        }else{
			$template = $root . $path . strtolower(basename($this->getView($params), PHP_EXT)) . DIRECTORY_SEPARATOR . 'index.mustache';
        }
		if(is_file($template))
		{
			return $template;
		}else{
			throw new Exception("fuck");
		}
	}
}