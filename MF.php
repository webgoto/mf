<?php
/**!
 * mf(minimal framework) v0.9.0
 * https://github.com/webgoto/mf
 *
 * Copyright 2016, webgoto.net
 * Released under the MIT license
 */

Class MF{
	public $src_dir;
	public $asset_dir;

	public $root_path;
	public $sub_path;
	public $site_path;
	public $asset_path;
	public $src_path;

	public $root_url;
	public $sub_url;
	public $site_url;
	public $asset_url;

	public $slug;
	public $route;
	public $title;
	public $option;
	public $slugs = array('404'=>array('title'=>'ページが見つかりません。','url'=>''),'405'=>array('title'=>'送信されたメソッドは許可されていません。','url'=>''));

	private $router;

	/**
	 * コンストラクタ
	 *
	 * @param array $opt_arr src_dir='/src', asset_dir'/asset', root_path, root_url, sub_path, sub_url
	 */
	public function __construct($opt_arr=array()){
		// Microsoft IIS doesn't set REQUEST_URI by default
		if(!isset($_SERVER['REQUEST_URI'])){
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
			if(isset($_SERVER['QUERY_STRING'])){
				$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
			}
		}

		$this->src_dir = $this->array_get($opt_arr['src_dir'], '/src');
		$this->asset_dir = $this->array_get($opt_arr['asset_dir'], '/asset');

		$this->router = new TreeRoute();

		//pathの設定
		$backtrace        = debug_backtrace();
		$index_path       = rtrim(str_replace('\\', '/', dirname($backtrace[count($backtrace)-1]['file'])), '/');
		$this->root_path  = $this->array_get($opt_arr['root_path'], rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'));
		if(mb_strpos($index_path, $this->root_path)===false) echo 'MF:ドキュメントルートとインデックスファイルのPATHが一致しないため、root_pathが正しく推測できませんでした。<br>index_pathから[/サブディレクトリ]を除いたPATHをコンストラクタのroot_pathで指定してみて下さい。<br>index_path:'.$index_path.'<br> root_path:&nbsp;&nbsp;'.$this->root_path;
		$length           = mb_strlen($index_path)-mb_strlen($this->root_path)-mb_strpos($index_path, $this->root_path);
		$this->sub_path   = $this->array_get($opt_arr['sub_path'], mb_substr($index_path, -$length, $length));
		$this->site_path  = $this->root_path.$this->sub_path;
		$this->asset_path = $this->site_path.$this->asset_dir;
		$this->src_path   = $this->site_path.$this->src_dir;

		//urlの設定
		$scheme = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
		$this->root_url = $scheme.'://'.$_SERVER['SERVER_NAME'];
		if(($scheme==='https' && $_SERVER['SERVER_PORT']!=443) || ($scheme==='http' && $_SERVER['SERVER_PORT']!=80)){
			$this->root_url .= sprintf(':%s', $_SERVER['SERVER_PORT']);
		}
		$this->root_url = $this->array_get($opt_arr['root_url'], $this->root_url);
		$this->sub_url =  $this->array_get($opt_arr['sub_url'], mb_substr($index_path, -$length, $length));
		$this->site_url = $this->root_url.$this->sub_url;
		$this->asset_url = $this->site_url.$this->asset_dir;
	}

	/**
	 * サイトのページを設定
	 *
	 * @param string $route
	 * @param array $handler
	 * @param string|array $method
	 */
	public function addRoute($route, $handler, $method = array('GET', 'POST', 'OPTIONS', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'CONNECT')){
		$this->slugs[$handler[0]] = array('url'=>$route, 'title'=>$handler[1]);
		$this->router->addRoute($method, $this->sub_url.$route, $handler);
	}

	/**
	 * アクセスされたページを特定
	 */
	public function dispatch(){
		$httpMethod = $_SERVER['REQUEST_METHOD'];
		$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		$cut = mb_strlen($this->sub_url);
		$this->route = mb_substr($uri, $cut, mb_strlen($uri)-$cut);
		$result = $this->router->dispatch($httpMethod, $uri);
		if(isset($result['error'])){
			switch($result['error']['code']){
				case 404 :
					$this->slug  = '404';
					$this->title = $this->slugs['404']['title'];
					break;
				case 405 :
					$this->slug  = '405';
					$this->title = $this->slugs['404']['title'];
					break;
			}
		}else{
			$this->slug   = $result['handler'][0];
			$this->title  = $result['handler'][1];
			$this->option = $result['params'];
		}
	}

	/**
	 * 現在のスラッグと比較し、一致した場合にtrueまたは$output_textで指定した文字列を返す。
	 *
	 * @param string|array $slug
	 * @param string $output_text
	 *
	 * @return bool|string
	 */
	public function match_slug($slug, $output_text = ''){
		foreach((array)$slug as $value){
			if(mb_strpos($this->slug, $value)!==false){
				if($output_text===''){
					return true;
				}else{
					return $output_text;
				}
			}
		}
		if($output_text===''){
			return false;
		}else{
			return '';
		}
	}

	/**
	 * スラッグ名とオプション値から登録されてるurlを返す。
	 *
	 * @param string $slug
	 * @param array $option
	 *
	 * @return string
	 */
	public function slug_url($slug = '', $option = array()){
		if($slug==='') $slug = $this->slug;
		$url = $this->slugs[$slug]['url'];
		foreach($option as $key=>$val){
			$url = preg_replace('/\{'.$key.':.*?\}/', $val, $url);
		}
		$url = preg_replace('/\{.*?:.*?\}\/?/', '', $url);
		return $this->site_url.$url;
	}


	/**
	 * 配列の値などをエラーを出さずに取り出す。
	 *
	 * @param mixed $var
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	private function array_get(&$var, $default = null){
		if(isset($var)) return $var;
		return $default;
	}
}

/*
 * TreeRoute
 * https://github.com/baryshev/TreeRoute
 * Copyright (c) 2015, Vadim Baryshev All rights reserved.
 */

class TreeRoute
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
