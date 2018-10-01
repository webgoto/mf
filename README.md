# mf
PHP minimal framework

v0.9.0

## 使い方 How to use.

### ディレクトリ構成例 Directory sample
Sample
```
www/sample
  .htaccess
  index.php
  MF.php
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

echo $mf->slug;
```

### API

### コンストラクタ Constructor
new MF([array('src_dir'=>'', 'asset_dir'=>'', 'root_path'=>'', 'root_url'=>'', 'sub_path'=>'', 'sub_url'=>'')])  
  
src_dir = '/src'  
ソースファイル等の非公開ファイルの相対ディレクトリを指定  
asset_dir = '/asset'  
ソースファイル等の開ファイルの相対ディレクトリを指定  
root_path  
ドキュメントルートパスを手動で設定 例)/root/public_html  
root_url  
ルートURLを手動で設定 例)https://example.com  
sub_path  
ドキュメントルートからのパスを手動で設定 例)/shop  
sub_url  
トップURLからのディレクトリを手動で設定 例)/shop

### メソッド Method

#### addRoute($route, $handler [, $method]) void
サイトのページを追加
##### $route string
urlのルール  
例 '/'、'/about/'、'/item/page{id}/'

ルーティングライブラリには「TreeRoute」を使用  
https://github.com/baryshev/TreeRoute

##### $handler array
1番目スラッグ名、2番目タイトル  
例 ['about', 'サイトについて']
##### $method string|array
対応するメソッド  
例 'post'、['post', 'delete']

#### dispatch() void
アクセスされたページを特定

#### match_slug($slug [, $output_text]) bool|string
現在のスラッグと比較し、一致した場合trueか$output_textを、不一致の場合falseか空文字を返す。
##### $slug string|array
調べるスラッグ名  
例 'about'、['about', 'item']
##### $output_text string
出力するテキスト  
例 'current'、'active'

#### slug_url($slug [, $option]) string
スラッグ名とオプション値から登録されてるurlを返す。
##### $slug string
調べるスラッグ名  
例 'about'
##### $option array
登録時に応じたオプション  
例 ['id'=>5]

### プロパティ Property
ドキュメントルート/home/user/www/の環境にて  
/home/user/www/demo/にindex.phpを設置した場合  

##### sub_dir
ドキュメントルートから設置ディレクトリまでの相対パス  
例 '/demo'

##### src_dir
mf読み込みファイルからphpファイルなどのソースファイル設置ディレクトまでの相対パス  
例 '/src'

##### asset_dir
mf読み込みファイルから画像ファイルなどのアセットファイル設置ディレクトまでの相対パス  
例 '/asset'

##### root_path
ドキュメントルートのパスを返す。  
例 '/home/user/www/'

##### site_path
サイトトップのパスを返す。  
例 '/home/user/www/demo/'

##### src_path
ソースファイルなど非公開ファイルを設置するパスを返す。  
例 '/home/user/www/demo/src/'

##### asset_path
画像などの公開ファイルを設置するパスを返す。  
例 '/home/user/www/demo/asset/'

##### site_url
サイトトップページのURLを返す。  
例 'http://example.net/demo/'

##### asset_url
画像などの公開ファイルディレクトリのURLを返す。  
例 'http://example.net/demo/asset/'

##### slugs
登録されたルートのurlとtitleをスラッグ別にした連想配列  
例
```
[
  'about'=>[
   'url'=>'xxxxxx',
   'title'=>'xxxxxxx',
  ],
  'item'=>[
   'url'=>'xxxxxx',
   'title'=>'xxxxxxx',
  ],
]
```

#### アクセスしたURLによって値が変わるプロパティ
http://example.net/demo/other1/にアクセスした場合

##### slug: 'other1'  
##### route: '/other1/'  
##### title: 'Other - Demo Site'  
##### option: [name=>'other1']
