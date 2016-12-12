# mf
PHP minimal framework

## 使い方 How to use.

### ディレクトリ構造 Directory
Sample
```
www/
  index.php
  MF.php
  .htaccess
  theme/
    view-top.php
    view-about.php
    view-contact.php
    view-404.php
```

### ルーティング Routing

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

### メソッド Method

#### addRoute($path, $data [, $method])

#### dispatch()

### プロパティ Property
