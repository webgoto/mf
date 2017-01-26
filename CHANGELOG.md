# Change Log

## v0.6.0 (2017-01-2)

### Removed:
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