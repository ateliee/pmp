# 高機能php frameworks

![image](/documents/images/icon.png)

pmpはsymfonyを模した超軽量フレームワークスです。

symfonyほどの高度な機能はありませんが、その分超軽量で快適に動作致します。

カスタマイズ性に優れており、管理ページはもとよりECサイト、ポータルサイトなど、

小規模サイトから中大規模サイトの構築にもオススメです。

完全日本産で、一通りの機能を最短構築できます。

また、phpのバージョンに依存しない作りを目指しており、

あらゆる環境のサーバーでの構築を目指しています。

### composer install
```
    "require": {
        "ateliee/pmp": "dev-master"
    }
```

一部の機能は他のライブラリに依存しています。

インストールされていない場合は下記にてインストールしてください。
```
    "require": {
        ...
        "pear-pear.php.net/PEAR" : "*",
        "pear-pear.php.net/Mail" : "*"
    }
```

### autoload
```
require 'vendor/autoload.php';
```


## ファイル構成
```
/app/
/src/
/vendor/
/web/
```
