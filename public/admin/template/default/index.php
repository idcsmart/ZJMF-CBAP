
{include file="header"}
<!-- =======内容区域======= -->
<div id="content" class="template" v-cloak>
  <!-- <p>{{lang.display_name}}</p>
  <p>{{message}}</p> -->
    <!-- wyh 20220526 -->
    {php}$hooks=hook('template_after_servicedetail_suspended');{/php}
    {if $hooks}
    {foreach $hooks as $item}
    {:htmlspecialchars_decode($item)}
    {/foreach}
    {/if}
</div>
{include file="footer"}