<?php
/*!
 * mf(minimal framework) v0.5.5
 * https://github.com/webgoto/mf
 *
 * Copyright 2016, webgoto.net
 * Released under the MIT license
 */

	//path,urlの設定
	//$this->slugs_path = $this->theme_path.$this->slugs_dir.'/'.$this->slug;
	//$this->slugs_url = $this->theme_url.$this->slugs_dir.'/'.$this->slug;
Class MF{
	public $sub_dir;
	public $src_dir = '/theme';
	public $asset_dir = '/theme';

	public $root_path;
	public $site_path;
	public $asset_path;
	public $src_path;

	public $site_url;
	public $asset_url;

	public $slug;
	public $route;
	public $title;
	public $option;
	public $slugs = array('404'=>array('title'=>'','url'=>''),'405'=>array('title'=>'','url'=>''));

	private $router;

	public function __construct(){
		//初期設定
		date_default_timezone_set("Asia/Tokyo");
		// Microsoft IIS doesn't set REQUEST_URI by default
		if(!isset($_SERVER['REQUEST_URI'])){
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
			if(isset($_SERVER['QUERY_STRING'])){
				$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
			}
		}

		$this->router = new Router();

		//pathの設定
		$backtrace        = debug_backtrace();
		$index            = rtrim(str_replace('\\', '/', dirname($backtrace[ count($backtrace)-1 ]['file'])), '/');
		$root             = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
		$length           = mb_strlen($index)-mb_strlen($root)-mb_strpos($index, $root);
		$sub              = mb_substr($index, -$length, $length);
		$this->root_path  = $root;
		$this->sub_dir    = $sub;
		$this->site_path  = $root.$sub;
		$this->asset_path = $root.$sub.$this->asset_dir;
		$this->src_path = $root.$sub.$this->src_dir;

		//urlの設定
		$scheme = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
		$root_url = $scheme.'://'.$_SERVER['SERVER_NAME'];
		if(($scheme==='https' && $_SERVER['SERVER_PORT']!=443) || ($scheme==='http' && $_SERVER['SERVER_PORT']!=80)){
			$root_url .= sprintf(':%s', $_SERVER['SERVER_PORT']);
		}
		$this->site_url = $root_url.$this->sub_dir;
		$this->asset_url = $this->site_url.$this->asset_dir;
	}

	/**
	 * サイトのページを設定
	 */
	public function addRoute($route, $handler){
		$this->slugs[$handler[0]] = array('url'=>$route, 'title'=>$handler[1]);
		$this->router->addRoute(array('GET', 'POST'), $this->sub_dir.$route, $handler);
	}

	/**
	 * アクセスされたページを特定
	 */
	public function dispatch(){
		$httpMethod = $_SERVER['REQUEST_METHOD'];
		$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		$cut = mb_strlen($this->sub_dir);
		$this->route = mb_substr($uri, $cut, mb_strlen($uri)-$cut);
		$result = $this->router->dispatch($httpMethod, $uri);
		if(isset($result['error'])){
			switch($result['error']['code']){
				case 404 :
					$this->slug  = '404';
					$this->title = 'ページが見つかりません。';
					break;
				case 405 :
					$this->slug  = '405';
					$this->title = '送信されたメソッドは許可されていません。';
					break;
			}
		}else{
			$this->slug   = $result['handler'][0];
			$this->title  = $result['handler'][1];
			$this->option = $result['params'];
		}
	}

	/**
	 * タイトルタグ用のテキストを作成
	 */
	public function title($site_name = '', $site_desc = ''){
		if($this->route==='/'){
			$text = $site_name.' '.$site_desc;
		}else{
			$text = $this->title.' - '.$site_name;
		}
		return $text;
	}

	/**
	 * 現在のスラッグと比較
	 */
	public function match_slug($slug, $output_text = null){
		foreach((array)$slug as $value){
			if(mb_strpos($this->slug, $value)!==false){
				if(is_null($output_text)){
					return true;
				}else{
					return $output_text;
				}
			}
		}
		if(is_null($output_text)){
			return false;
		}else{
			return '';
		}
	}

	/**
	 * スラッグ名+オプション値からurlを取得
	 */
	public function slug_url($slug, $option=array()){
		$url = $this->slugs[$slug]['url'];
		foreach($option as $key=>$val){
			preg_replace('/\{.*?'.$key.'.*?\}/g', $val, $url);
		}
		return $this->site_url.$url;
	}

}

/*
 * TreeRoute
 * https://github.com/baryshev/TreeRoute
 * Copyright (c) 2015, Vadim Baryshev All rights reserved.
 */

class Router
{
    const PARAM_REGEXP = '/^{((([^:]+):(.+))|(.+))}$/';
    const SEPARATOR_REGEXP = '/^[\s\/]+|[\s\/]+$/';

    private $routes = array('childs' => array(), 'regexps' => array());

    private function match($url)
    {
        $parts = explode('?', $url, 2);
        $parts = explode('/', preg_replace(self::SEPARATOR_REGEXP, '', $parts[0]));
        if (sizeof($parts) === 1 && $parts[0] === '') {
            $parts = array();
        }
        $params = array();
        $current = $this->routes;

        for ($i = 0, $length = sizeof($parts); $i < $length; $i++) {
            if (isset($current['childs'][$parts[$i]])) {
                $current = $current['childs'][$parts[$i]];
            } else {
                foreach ($current['regexps'] as $regexp => $route) {
                    if (preg_match('/^' . addcslashes($regexp, '/') . '$/', $parts[$i])) {
                        $current = $route;
                        $params[$current['name']] = $parts[$i];
                        continue 2;
                    }
                }

                if (!isset($current['others'])) {
                    return null;
                }

                $current = $current['others'];
                $params[$current['name']] = $parts[$i];
            }
        }

        if (!isset($current['methods'])) {
            return null;
        }

        return array(
            'methods' => $current['methods'],
            'route' => $current['route'],
            'params' => $params
        );
    }

    public function addRoute($methods, $route, $handler)
    {
        $methods = (array) $methods;

        $parts = explode('/', preg_replace(self::SEPARATOR_REGEXP, '', $route));
        if (sizeof($parts) === 1 && $parts[0] === '') {
            $parts = array();
        }

        $current = &$this->routes;
        for ($i = 0, $length = sizeof($parts); $i < $length; $i++) {
            $paramsMatch = preg_match(self::PARAM_REGEXP, $parts[$i], $paramsMatches);
            if ($paramsMatch) {
                if (!empty($paramsMatches[2])) {
                    if (!isset($current['regexps'][$paramsMatches[4]])) {
                        $current['regexps'][$paramsMatches[4]] = array('childs' => array(), 'regexps' => array(), 'name' => $paramsMatches[3]);
                    }
                    $current = &$current['regexps'][$paramsMatches[4]];
                } else {
                    if (!isset($current['others'])) {
                        $current['others'] = array('childs' => array(), 'regexps' => array(), 'name' => $paramsMatches[5]);
                    }
                    $current = &$current['others'];
                }
            } else {
                if (!isset($current['childs'][$parts[$i]])) {
                    $current['childs'][$parts[$i]] = array('childs' => array(), 'regexps' => array());
                }
                $current = &$current['childs'][$parts[$i]];
            }
        }

        $current['route'] = $route;
        for ($i = 0, $length = sizeof($methods); $i < $length; $i++) {
            if (!isset($current['methods'])) {
                $current['methods'] = array();
            }
            $current['methods'][strtoupper($methods[$i])] = $handler;
        }
    }

    public function getOptions($url)
    {
        $route = $this->match($url);
        if (!$route) {
            return null;
        }
        return array_keys($route['methods']);
    }

    public function dispatch($method, $url)
    {
        $route = $this->match($url);

        if (!$route) {
            return array(
                'error' => array(
                    'code' => 404,
                    'message' => 'Not Found'
                ),
                'method' => $method,
                'url' => $url
            );
        }

        if (isset($route['methods'][$method])) {
            return array(
                'method' => $method,
                'url' => $url,
                'route' => $route['route'],
                'params' => $route['params'],
                'handler' => $route['methods'][$method]
            );
        }

        return array(
            'error' => array(
                'code' => 405,
                'message' => 'Method Not Allowed'
            ),
            'method' => $method,
            'url' => $url,
            'route' => $route['route'],
            'params' => $route['params'],
            'allowed' => array_keys($route['methods'])
        );
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    public function options($route, $handler)
    {
        $this->addRoute('OPTIONS', $route, $handler);
    }

    public function get($route, $handler)
    {
        $this->addRoute('GET', $route, $handler);
    }

    public function head($route, $handler)
    {
        $this->addRoute('HEAD', $route, $handler);
    }

    public function post($route, $handler)
    {
        $this->addRoute('POST', $route, $handler);
    }

    public function put($route, $handler)
    {
        $this->addRoute('PUT', $route, $handler);
    }

    public function delete($route, $handler)
    {
        $this->addRoute('DELETE', $route, $handler);
    }

    public function trace($route, $handler)
    {
        $this->addRoute('TRACE', $route, $handler);
    }

    public function connect($route, $handler)
    {
        $this->addRoute('CONNECT', $route, $handler);
    }
}
