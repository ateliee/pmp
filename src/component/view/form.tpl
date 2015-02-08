
{block form_wedget_attr}{foreach $attr as $key => $val} {$key}="{$val}"{/foreach}{/block}
{block form_wedget_input}
{/block}
{block form_wedget_textarea}
    <textarea name="{$attr.name}"{block('form_wedget_attr')}>{$inner_html}</textarea>
{/block}
{block form_wedget_text}
    <input name="{$attr.name}"{block('form_wedget_attr')}>
{/block}
{block form_wedget_checkboxsimple}
<input name="{$attr.name}"{block('form_wedget_attr')}> <label for="{$attr.id}">{$inner_html}</label>
{/block}