!function(e){var t={};function r(o){if(t[o])return t[o].exports;var n=t[o]={i:o,l:!1,exports:{}};return e[o].call(n.exports,n,n.exports,r),n.l=!0,n.exports}r.m=e,r.c=t,r.d=function(e,t,o){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(r.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)r.d(o,n,function(t){return e[t]}.bind(null,n));return o},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=14)}({14:function(e,t){jQuery(document).ready((function(e){function t(){var t=e(".dragdrop-sortable-content li.single-sortable-item");e.each(t,(function(e,r){var o=t.eq(e);o.find("input.dragdrop-set-order").val(e),o.find("span.dragdrop-item-count").html(e+1)}))}e("#dragdrop-sortable").sortable({handle:".dragdrop-sortable-item-header",placeholder:"dragdrop-highlight"}),e("#dragdrop-sortable").on("sortupdate",(function(e,r){t()})),e(".dragdrop-sortable-content").on("click",".remove-dragdrop-sortable",(function(r){r.preventDefault(),r.stopPropagation(),e(this).parents(".single-sortable-item").remove(),t()})),e(".dragdrop-sortable-content").on("click",".add-dragdrop-sortable",(function(t){t.preventDefault(),t.stopPropagation();var r,o,n=(o='<li class="ui-state-default single-sortable-item">',o+='<div class="dragdrop-sortable-item">',o+='<div class="dragdrop-sortable-item-header">',o+='<h3 class="hndle">Document Name <span class="dragdrop-item-count">'+((r=e(".dragdrop-sortable-content li.single-sortable-item").length)+1)+"</span></h3>",o+="</div>",o+='<div class="dragdrop-sortable-item-body">',o+='<table class="wp-list-table widefat fixed striped">',o+="<thead>",o+="<tr>",o+="<th>Title</th>",o+="<th>URL</th>",o+="</tr>",o+="</thead>",o+="<tbody>",o+="<tr>",o+='<td><input type="text" class="widefat" name="council_meeting_documents['+r+'][title]"></td>',o+='<td><input type="text" class="widefat" name="council_meeting_documents['+r+'][url]"></td>',o+="</tr>",o+="</tbody>",o+="</table>",o+='<div class="dragdrop-sortable-item-bottom">',o+='<button type="button" class="button remove-dragdrop-sortable">Remove Document <span class="dragdrop-item-count">'+(r+1)+"</span></button>",o+="</div>",o+="</div>",o+='<input type="hidden" name="council_meeting_documents['+r+'][order]" value="'+r+'" class="dragdrop-set-order">',o+="</div>",o+="</li>");e("#dragdrop-sortable").append(n),e(".select2-text-select.newrow-select2").select2({width:"100%"}),setTimeout((function(){e("select.select2-text-select.newrow-select2").removeClass("newrow-select2")}),500)})),e("#dragdrop-sortable").on("sortstart",(function(t,r){var o=r.item.height();e("#dragdrop-sortable li.dragdrop-highlight").height(o)})),e(".select2-text-select").select2({width:"100%"})}))}});