<?php

/**
 * Class Enum
 */
abstract class Enum
{
    private $scalar;

    function __construct($value)
    {
        $ref = new ReflectionObject($this);
        $consts = $ref->getConstants();
        if (! in_array($value, $consts, true)) {
            throw new InvalidArgumentException;
        }

        $this->scalar = $value;
    }

    final static function __callStatic($label, $args)
    {
        $class = get_called_class();
        $const = constant("$class::$label");
        return new $class($const);
    }
    final function value()
    {
        return $this->scalar;
    }

    final function __toString()
    {
        return (string)$this->scalar;
    }
}
/*
// トランプのスート型を定義する。4種類しか値を取らない。
// Enumをextendして、定数をセット
final class Suit extends Enum
{
const SPADE = 'spade';
const HEART = 'heart';
const CLUB = 'club';
const DIAMOND = 'diamond';
}

//インスタンス化
$suit = new Suit(Suit::SPADE);
echo $suit; //toString実装済みなので文字列キャスト可能

echo $suit->valueOf(); //生の値を取り出す。intやfloat等の場合に。

// 適当な値を突っ込もうとすると、InvalidArgumentExceptionが発生して停止
//$suit = new Suit('uso800');



//__callStaticを定義してあるのでnewを使わずこんな感じでも書ける(PHP5.3以降)
$suit = Suit::SPADE();
 */