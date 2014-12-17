# テンプレートエンジン
テンプレートエンジンは独自のテンプレートエンジンを利用しています。
構成によっては別のテンプレートエンジンを利用することも可能です。
テンプレートエンジンのコアプログラムは全てclass/Template.class.phpに記載しております。
もちろん、単独でも利用することができるので興味のある方はソースをご覧下さい。

## テンプレートの読み込み
```
$template = new Template();
$template->load($filename);
```
読み込んだテンプレートに変数を当てはめたものを取得するにはget_display_template()を使用します。
```
$html = $template->get_display_template(true);
// output template
$template->display();
```

## 変数の設定
変数は文字列、数字もしくは配列、連想配列をサポートしています。(オブジェクトは利用できません。)
変数の設定は簡単で、assign()を利用します。
```
$template->assign('val',1);
$template->assign_vars(array('val' => 1));
```

## 変数の参照
読み込んだテンプレートから変数を参照するには、デリミタで区切る必要があります。
```
{ $val }    -> output 「1」
```

## 関数の利用
テンプレートから関数を利用することも可能です。
もちろん独自の関数も設定することができます。
また、初期値では自動エスケープをサポートされているため、htmlentitiesによってエスケープされます。
```
{ htmlentities($val) }
```

## 計算式
計算式を埋め込むことができます。
```
{ 1 + 2 }    -> output 「3」
{ 7 % 2 }    -> output 「1」
{ 5 * 2 }    -> output 「10」
{ 4 - (1 + 2) }    -> output 「1」
{ 5 * ($a + 5) }
```

## if文
```
{if(true)}print{else}not print{/if}
{if(15 > 4)}print{/if}
```

## foreach文
```
{foreach $items as $key => $val}
{/foreach}
```

## for文
```
{for $i = 1 to 5}
{/for}
```

## block文
ブロック文は宣言することで上書きすることができます
```
{block test}
テスト
{/block}
{block test}
こちらが表示されます。
{/block}
```
