<?php
namespace PMP;

/**
 * Class Debug
 * @package PMP
 */
class Debug{

    /**
     * @param bool $enable
     */
    static function enable($enable=true){
        if($enable){
            ini_set( 'display_errors', true );
            error_reporting(-1);
        }else{
            ini_set( 'display_errors', false );
            error_reporting(-1);
        }
    }

    /**
     * @param $html
     */
    public function appendRendarDebug(&$html)
    {
        $tmp = <<< 'EOM'
{strip}
<style type="text/css">
    .pmp_debug{
        padding:60px 0 0 ;
    }
    .pmp_debug_area{
        font-size:12px;
        line-height:140%;
        position: fixed;
        left: 0;
        bottom:0;
        width: 100%;
        padding:0 0 0 60px;
        color:#fff;
        background: #555;
        border-top: solid 1px #333;
        z-index:10000;
    }
    .pmp_debug_menu{
        float: left;
        white-space: nowrap;
        border-right:solid 1px #888;
        position: relative;
    }
    .pmp_debug_menu .pmp_menu{
        padding: 12px 20px;
        display: block;
        cursor:pointer;
    }
    .pmp_debug_menu .pmp_menu_value{
        position: absolute;
        display: none;
        left: 0;
        bottom: 42px;
        background: #FFF;
        border:solid 1px #666;
        padding:20px;
        margin:0;
        z-index:10;
        list-style: none;
        min-width:500px;
        max-height:400px;
        overflow:auto;
        color:#333;
    }
    .pmp_debug_menu .pmp_menu_value li{
        padding:3px 0px;
        margin:0;
        border-top:solid 1px #CCC;
        list-style: none;
    }
    .pmp_debug_menu .pmp_menu_value li:first-child{
        border-top:none;
    }
    .pmp_debug_menu:hover .pmp_menu_value{
        display: block;
    }
</style>
<div class="pmp_debug">
<div class="pmp_debug_area">
    <div class="pmp_debug_menu">
        <div class="pmp_menu">Server</div>
        <ul class="pmp_menu_value">
        {foreach $SERVER as $key => $val}
        <li>{$key} : {if(is_array($val))}{dump($val)}{else}{$val}{/if}</li>
        {/foreach}
        </ul>
    </div>
    <div class="pmp_debug_menu">
        <div class="pmp_menu">Request</div>
        <ul class="pmp_menu_value">
        {foreach $REQUEST as $key => $val}
        <li>{$key} : {if(is_array($val))}{dump($val)}{else}{$val}{/if}</li>
        {/foreach}
        </ul>
    </div>
    <div class="pmp_debug_menu">
        <div class="pmp_menu">Get</div>
        <ul class="pmp_menu_value">
        {foreach $GET as $key => $val}
        <li>{$key} : {if(is_array($val))}{dump($val)}{else}{$val}{/if}</li>
        {/foreach}
        </ul>
    </div>
    <div class="pmp_debug_menu">
        <div class="pmp_menu">Session</div>
        <div class="pmp_menu_value">
        <pre>{dump($SESSION)}</pre>
        </div>
    </div>
</div>
</div>
{/strip}
EOM;
        $tp = new Template();
        // template value
        $tp->assign("SERVER",Request::getServer()->getVars());
        $tp->assign("REQUEST",Request::getRequest()->getVars());
        $tp->assign("GET",Request::getQuery()->getVars());
        $tp->assign("SESSION",$_SESSION);
        // template set
        $tp->setTemplateStr($tmp);

        $html = preg_replace("/(<\/body>)/",$tp->get_display_template(true)."$1",$html);
    }
}