# mf
PHP minimal framework

## 使い方 How to use.

### ディレクトリ構造 Directory
Sample
```
www/demo
  index.php
  MF.php
  .htaccess
  asset/
    style.css
  src/
    top.php
    about.php
    contact.php
    404.php
```

### リライト Rewrite

.htaccessを作成
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

### ルーティング Routing
index.phpを作成
Sample
```php
<?php
require 'MF.php';
$mf = new MF();

$mf->addRoute('/', array('top', 'トップ'));
$mf->addRoute('/about/', array('about', 'サイトについて'));
$mf->addRoute('/contact/', array('contact', 'お問い合わせ'));
$mf->dispatch();

$path = $mf->src_path.'/'.$mf->slug.'.php'; if(file_exists($path)) include $path;
```

### API

### コンストラクタ Constructor
new MF()

### メソッド Method

#### addRoute($path, $data [, $method])

#### dispatch()

### プロパティ Property
上記のデモを/home/user/www/demo/に設置した場合  
またURLはhttp://example.net/demo/になるとする。  

sub_dir: '/demo'  
src_dir: '/src'  
asset_dir: '/asset'  

root_path: '/home/user/www/'  
ドキュメントルートのパスを返す。  

site_path: '/home/user/www/demo/'  
サイトトップのパスを返す。  

src_path: '/home/user/www/demo/src/'  
ソース（非公開）ファイルを設置するパスを返す。  

asset_path: '/home/user/www/demo/asset/'  
画像などの公開ファイルを設置するパスを返す。  

site_url: 'http://example.net/demo/'  
サイトトップページのURLを返す。  

asset_url:　'http://example.net/demo/asset/'  
画像などの公開ファイルディレクトリのURLを返す。  

slugs: 登録されたルートの内容の連想配列  

#### アクセスしたURLによって値が変わるプロパティ
http://example.net/demo/other1/にアクセスした場合

slug: 'other'  
route: '/other1/'  
title: 'Other - Demo Site'  
option: [name=>'other1']
