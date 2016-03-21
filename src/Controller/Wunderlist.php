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
		$front = $this->wp->front;

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
                    if($params->has('view'))
                    {
	                    if(stripos($params->get('view'), DIRECTORY_SEPARATOR) !== false)
	                    {
		                    $view = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($params->get('view'), DIRECTORY_SEPARATOR);
	                    }else{

	                    }
                    }else{
                        $view = rtrim(setcooki_get_option('VIEW_PATH', $front), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR  . 'Standard.php';
                    }
                    if($params->has('template'))
                    {
                        $template = setcooki_path('root') . DIRECTORY_SEPARATOR . ltrim($params->get('template'), DIRECTORY_SEPARATOR);
                    }else{
                        $template = rtrim(setcooki_get_option('TEMPLATE_PATH', $front), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'standard' . DIRECTORY_SEPARATOR . 'list.mustache';
                    }
					$view = apply_filters('wp_wunderlist_view', $view);
                    $template = apply_filters('wp_wunderlist_template', $template);
                    if(is_file($view))
                    {
	                    if(is_file($template))
	                    {
	                        require_once $view;
	                        $view = basename($view, '.php');
	                        if(class_exists($view))
	                        {
	                            if(stristr($template, '.mustache') !== false)
	                            {
	                                $options = array
	                                (
	                                    'loader' => new \Mustache_Loader_FilesystemLoader(dirname($template)),
	                                );
	                                $view = new $view($this, new \Mustache_Engine($options));
	                                $view->execute((array)$params);
		                            $vars = apply_filters('wp_wunderlist_vars', $view->vars());
		                            $template = $view->view->loadTemplate('list');
		                            return $template->render($vars);
	                            }else{
	                                $view = new $view($this);
	                                $view->execute((array)$params);
		                            $vars = apply_filters('wp_wunderlist_vars', $view->vars());
		                            extract($vars);
		                            ob_start();
		                            require $template;
		                       		return ob_get_clean();
	                            }
	                        }else{
								throw new Exception("view class not found");
	                        }
	                    }else{
	                        throw new Exception("template not found");
	                    }
	                }else{
	                    throw new Exception("view file not found");
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
}