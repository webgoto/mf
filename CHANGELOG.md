# Change Log

## v0.9.2 (2020-06-23)

### Change;
- dispatchメソッドが解析結果を返すように変更。
- routerプロパティをprivateからprotectedに変更。

### Fix
- 404,405エラー時のロジックを修正。

## v0.9.1 (2019-02-06)

### Change:
- スラッグが404,405の時、レスポンスコードで404,405を返す。

## v0.9.0 (2018-06-14)

### Breaking change:
- コンストラクタの引数を連想配列(src_dir, asset_dir, root_path, root_url, sub_path, sub_url)に変更。

### Add;
- array_getメソッド(private)を追加。
- root_pathがうまく推測できない場合注意分を出す。

## v0.8.2 (2018-06-13)

### Fix
- slug_urlメソッドで指定していないオプションは戻り値のURLから削除する。

## v0.8.1 (2018-06-07)

### Fix
- slug_urlメソッド内のpreg_replaceでgオプションは暗黙的に実行されるので削除。

## v0.8.0 (2017-04-07)

### Breaking change:
- コンストラクタでのタイムゾーンの指定を削除。

## v0.7.0 (2017-03-04)

### Change:
- slug_urlメソッドでslugの指定がない場合、表示中のslugとする。

## v0.6.0 (2017-01-27)

### Remove:
- titleメソッドを削除。
- slugs_urlプロパティを削除。
- slugs_dirプロパティを削除。

### Breaking change:
- コンストラクタの引数にsrc_dirとasset_dirを追加。
- addRouteメソッドの引数にmethodを追加。
- ルータのデフォルトhttpメソッドをGET、POSTからGET、POST、OPTIONS、HEAD、PUT、DELETE、TRACE、CONNECTに変更。
- デフォルトディレクトリを'/theme'からsrc_dirは'/src'、asset_dirは'/asset'に変更。

### Change:
- TreeRouteのクラス名をRouterからTreeRouteに変更。
- 404、405エラーページのタイトルをslugsプロパティで指定する。