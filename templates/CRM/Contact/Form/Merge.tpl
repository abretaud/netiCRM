{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
<div class="crm-block crm-form-block crm-contact-merge-form-block">
<div id="help">
{ts}Click <strong>Merge</strong> to move data from the Duplicate Contact on the left into the Main Contact. In addition to the contact data (address, phone, email...), you may choose to move all or some of the related activity records (groups, contributions, memberships, etc.).{/ts} {help id="intro"}
</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
<div class="action-link-button">
    	<a href="{crmURL q="reset=1&cid=$other_cid&oid=$main_cid"}">&raquo; {ts}Flip between original and duplicate contacts.{/ts}</a>
</div>

<div class="action-link-button">
       <a id='notDuplicate' href="#" title={ts}Mark this pair as not a duplicate.{/ts} onClick="processDupes( {$main_cid}, {$other_cid}, 'dupe-nondupe', 'merge-contact', '{$userContextURL}' );return false;">&raquo; {ts}Mark this pair as not a duplicate.{/ts}</a>
</div>	

{literal}
<style type="text/css">
  .is-erase{
    color: red;
    text-decoration: line-through;
  }
</style>
{/literal}

<table>
  <tr class="columnheader">
    <th>&nbsp;</th>
    <th><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$other_cid"}">{$other_name}&nbsp;<em>{$other_contact_subtype}</em></a> ({ts}duplicate{/ts})</th>
    <th>{ts}Mark All{/ts}<br />=={$form.toggleSelect.html} ==&gt;</th>
    <th><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$main_cid"}">{$main_name}&nbsp;<em>{$main_contact_subtype}</em></a> ({ts}Reserved{/ts}) </th>
  </tr>
  {foreach from=$rows item=row key=field}
     <tr class="{cycle values="odd-row,even-row"}">
        <td>{$row.title}</td>
        <td>
           {if !is_array($row.other)}
               {$row.other}
           {else}
               {$row.other.fileName}
           {/if} 
        </td>
        <td style='white-space: nowrap'>{if $form.$field}=={$form.$field.html}==&gt;{else}{ts}Skip{/ts}{/if}</td>
        <td>
            {if $row.title|substr:0:5 == "Email"   OR 
                $row.title|substr:0:7 == "Address" OR 
                $row.title|substr:0:2 == "IM"      OR 
                $row.title|substr:0:6 == "OpenID"  OR 
                $row.title|substr:0:5 == "Phone"}

	        {assign var=position  value=$field|strrpos:'_'}
                {assign var=blockId   value=$field|substr:$position+1}
                {assign var=blockName value=$field|substr:14:$position-14}

                {$form.location.$blockName.$blockId.locTypeId.html}&nbsp;
                {if $blockName eq 'address'}
                <span id="main_{$blockName}_{$blockId}_overwrite">{if $row.main}({ts}overwrite{/ts}){else}({ts}add{/ts}){/if}</span>
                {/if} 

                {$form.location.$blockName.$blockId.operation.html}&nbsp;<br />
            {/if}
            <span id="main_{$blockName}_{$blockId}">{$row.main}</span>
        </td>
     </tr>
  {/foreach}

  {foreach from=$rel_tables item=params key=paramName}
    {if $paramName eq 'move_rel_table_users'}
      <tr class="{cycle values="even-row,odd-row"}">
      <th>{ts}Move related...{/ts}</th><td><a href="{$params.other_url}">{$params.other_title}</a></td><td style='white-space: nowrap'>{if $otherUfId}=={$form.$paramName.html}==&gt;{/if}</td><td>{if $mainUfId}<a href="{$params.main_url}">{$params.main_title}</a>{/if}</td>
    </tr>
    {else}
    <tr class="{cycle values="even-row,odd-row"}">
      <th>{ts}Move related...{/ts}</th><td><a href="{$params.other_url}">{$params.title}</a></td><td style='white-space: nowrap'>=={$form.$paramName.html}==&gt;</td><td><a href="{$params.main_url}">{$params.title}</a>{if $form.operation.$paramName.add.html}&nbsp;{$form.operation.$paramName.add.html}{/if}</td>
    </tr>
    {/if}
  {/foreach}
</table>
<div class='form-item'>
  <!--<p>{$form.moveBelongings.html} {$form.moveBelongings.label}</p>-->
  <!--<p>{$form.deleteOther.html} {$form.deleteOther.label}</p>-->
</div>
<div class="form-item">
    <p><strong>{ts}WARNING: The duplicate contact record WILL BE DELETED after the merge is complete.{/ts}</strong></p>
    {if $user}
      <p><strong>{ts}There are Drupal user accounts associated with both the original and duplicate contacts. If you continue with the merge, the user record associated with the duplicate contact will not be deleted, but will be un-linked from the associated contact record (which will be deleted). If that user logs in again, a new contact record will be created for them.{/ts}</strong></p>
    {/if}
    {if $other_contact_subtype}
      <p><strong>The duplicate contact (the one that will be deleted) is a <em>{$other_contact_subtype}</em>. Any data related to this will be lost forever (there is no undo) if you complete the merge.</strong></p>
    {/if}
</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>

{literal}
<script type="text/javascript">

cj(document).ready(function(){ 
    cj('table td input.form-checkbox').each(function() {
       var ele = null;
       var element = cj(this).attr('id').split('_',3);

       switch ( element['1'] ) {
           case 'addressee':
                 var ele = '#' + element['0'] + '_' + element['1'];
                 break;

           case 'email':
           case 'postal':
                 var ele = '#' + element['0'] + '_' + element['1'] + '_' + element['2'];
                 break;
       }

       if( ele ) {
          cj(this).bind( 'click', function() {
 
              if( cj( this).attr( 'checked' ) ){
                  cj('input' + ele ).attr('checked', true );
                  cj('input' + ele + '_custom' ).attr('checked', true );
              } else {
                  cj('input' + ele ).attr('checked', false );
                  cj('input' + ele + '_custom' ).attr('checked', false );
              }
          });
       }
    });
    
    cj('[id^="move_"]').change(onChangeOverlayCheckBox);
    cj('[id^="location"][type=checkbox]').change(onChangeAddnewCheckbox);
    doCheckAllIsReplace();

    cj('#toggleSelect').change(function(){
      if(cj(this).attr('checked')){
        alert("{/literal}{ts}WARNING: The duplicate contact record WILL BE DELETED after the merge is complete.{/ts}{literal}");
        
      }
      var is_checked = cj(this).attr('checked')== 'checked';
      cj('[id^="location"][type=checkbox][disabled!=disabled]').each(function(){
        cj(this).attr('checked',is_checked );
      })
      setTimeout(checkDataIsErase,100);
    })

    
});

function mergeAddress( element, blockId ) {
   var allAddress = {/literal}{$mainLocAddress}{literal};
   var address    = eval( "allAddress." + 'main_' + element.value );
   var label      = '({/literal}{ts}overwrite{/ts}{literal})';

   if ( !address ) { 
     address = '';
     label   = '({/literal}{ts}Add{/ts}{literal})';
   }

   cj( "#main_address_" + blockId ).html( address );	
   cj( "#main_address_" + blockId +"_overwrite" ).html( label );
}

/**
 * Check all the ==[]==> checkbox 
 * Only do once when page ready.
 */
function doCheckAllIsReplace(){
  cj('[id^="move_"]').each(function(){
    var cj_this = cj(this);
    var cj_left_td = cj_this.parent().prev();
    var cj_right_td = cj_this.parent().next();

    if(cj_right_td.text().split(/\s+/)[1] == ""){
      cj_this.click();
    }else if(cj_this.attr('id').match(/^move_location_/)){
      if(cj_right_td.find('span').text() == ""){
        cj_this.click();
        cj_right_td.find('input[type="checkbox"]').attr('checked',true).attr("disabled", true);
      }
    }else{
      var cj_left_left_td = cj_left_td.prev();
      if(cj_left_left_td.text().match("{/literal}{ts}Move related...{/ts}{literal}")){
        cj_this.attr('checked',true);
      }
    }
  })
}

/**
 * When click ==[]==> checkbox
 */
function onChangeOverlayCheckBox(){
  var cj_this = cj(this);
  var cj_left_td = cj_this.parent().prev();
  var cj_right_td = cj_this.parent().next();


  if(cj_this.attr('id').match(/^move_location_/)){
    if(cj_right_td.find('span').text() !== ""){
      cj_right_td.find('input[type="checkbox"]').attr('checked',cj_this.attr('checked')=='checked');
    }
  }

  checkDataIsErase(cj_this);

  

  cj('#toggleSelect').removeAttr('checked');
}

/**
 * When click "Add new " checkbox on location type field
 */
function onChangeAddnewCheckbox(){
  $this = cj(this);
  if($this.attr('checked')){
    $this.closest('td').prev().find('[id^="move_"]').attr('checked',true);
  }
  checkDataIsErase($this);
}


/**
 * Check if right column need to show "will be erased" or not.
 * @param  jQuery_element cjCheckboxElement The cj checkbox element which want to check.
*                                           If null. than check all the ==[]==> element.
 */
function checkDataIsErase(cjCheckboxElement){
  if(!cjCheckboxElement){
    cj('[id^="move_"]').each(function(){
      checkDataIsErase(cj(this));  
    })
    
    return ;
  }

  var cj_left_td = cjCheckboxElement.closest('td').prev();
  var cj_right_td = cjCheckboxElement.closest('td').next();

  if(cjCheckboxElement.length <= 0){
    console.log('The query element cjCheckboxElement have error, Please check your code.');
    return ;
  }

  if(cjCheckboxElement.attr('id').match(/^location/)){
    checkDataIsErase(cj_left_td.find('input[id^="move_"]'));
  }else if(cjCheckboxElement.attr('id').match(/^move_/)){

    var is_erase = false;

    if(cjCheckboxElement.attr('checked')){
      if(!cjCheckboxElement.attr('id').match(/^move_location_/)){
        is_erase = true;
      }else if(cj_right_td.find('input[type="checkbox"]').length > 0 && typeof cj_right_td.find('input[type="checkbox"]').attr('checked') == "undefined"){
        is_erase = true;
      }
      
    }

    is_erase?cj_right_td.children('span').addClass('is-erase'):cj_right_td.children('span').removeClass('is-erase');

  }

}

</script>
{/literal}

{* process the dupe contacts *}
{include file="CRM/common/dedupe.tpl"}
