
{block form_wedget_attr}{foreach $attr as $key => $val} {$key}="{$val}"{/foreach}{/block}
{block form_wedget_input}
{/block}
{block form_wedget_textarea}
    <textarea name="{$attr.name}"{block('form_wedget_attr')}>{$innerHtml}</textarea>
{/block}
{block form_wedget_text}
    <input name="{$attr.name}"{block('form_wedget_attr')}>
{/block}
{block form_wedget_checkbox_simple}
{html_element_set($checkbox.input)}<input name="{$attr.name}"{block('form_wedget_attr')}>
{html_element_set($checkbox.label)}<label{block('form_wedget_attr')}>{$innerHtml}</label>
{/block}
{block form_wedget_checkbox}
    {html_element_set_checkbox($childs)}{foreach $childs as $checkbox}{block('form_wedget_checkbox_simple')}{/foreach}
{/block}
{block form_wedget_checklist}
    {html_element_set_checkbox($childs)}{foreach $childs as $checkbox}{block('form_wedget_checkbox_simple')}{/foreach}
{/block}