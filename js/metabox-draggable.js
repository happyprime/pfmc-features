jQuery(document).ready(function($){
	/**
	 * Initialize sortable
	 */
	$("#dragdrop-sortable").sortable({
		handle: ".dragdrop-sortable-item-header",
		placeholder: "dragdrop-highlight"
	});

	/**
	 * Update numeric order
	 */
	function dragdropChangeItemOrder(){
		var allDragdropItems = $('.dragdrop-sortable-content li.single-sortable-item');
		$.each(allDragdropItems, function(index, value){
			var childItem = allDragdropItems.eq(index);
			childItem.find('input.dragdrop-set-order').val(index);
			childItem.find('span.dragdrop-item-count').html(index+1);
		});
	}

	/**
	 * When item is dropped
	 */
	$("#dragdrop-sortable").on("sortupdate", function(event, ui){
		dragdropChangeItemOrder();
	});

	/**
	 * Remove item
	 */
	$('.dragdrop-sortable-content').on('click', '.remove-dragdrop-sortable', function(evt){
		evt.preventDefault();
		evt.stopPropagation();
		$(this).parents('.single-sortable-item').remove();
		dragdropChangeItemOrder();
	});

	/**
	 * New item
	 */
	function newDragdropHTMLContent(index){
		var html = '<li class="ui-state-default single-sortable-item">';
			html += '<div class="dragdrop-sortable-item">';
				html += '<div class="dragdrop-sortable-item-header">';
					html += '<h3 class="hndle">Document Name <span class="dragdrop-item-count">'+ (index+1) +'</span></h3>';
				html += '</div>';
				html += '<div class="dragdrop-sortable-item-body">';
					html += '<table class="wp-list-table widefat fixed striped">';
						html += '<thead>';
							html += '<tr>';
								html += '<th>Title</th>';
								html += '<th>URL</th>';
							html += '</tr>';
						html += '</thead>';
						html += '<tbody>';
							html += '<tr>';
								html += '<td><input type="text" class="widefat" name="council_meeting_documents['+index+'][title]"></td>';
								html += '<td><input type="text" class="widefat" name="council_meeting_documents['+index+'][url]"></td>';
							html += '</tr>';
						html += '</tbody>';
					html += '</table>';
					html += '<div class="dragdrop-sortable-item-bottom">';
						html += '<button type="button" class="button remove-dragdrop-sortable">Remove Document <span class="dragdrop-item-count">'+ (index+1) +'</span></button>';
					html += '</div>';
				html += '</div>';
				html += '<input type="hidden" name="council_meeting_documents['+index+'][order]" value="'+index+'" class="dragdrop-set-order">';
			html += '</div>';
		html += '</li>';
		return html;
	}

	/**
	 * Add new item
	 */
	$('.dragdrop-sortable-content').on('click', '.add-dragdrop-sortable', function(evt){
		evt.preventDefault();
		evt.stopPropagation();
		var allDragdropItems = $('.dragdrop-sortable-content li.single-sortable-item');
		var newItem = newDragdropHTMLContent(allDragdropItems.length);
		$('#dragdrop-sortable').append(newItem);

		// Re-initialize select2 for new item
		$('.select2-text-select.newrow-select2').select2({
			width: '100%'
		});

		// Remove extra class from select tag
		setTimeout(function(){
			$('select.select2-text-select.newrow-select2').removeClass('newrow-select2');
		}, 500);

	});

	/**
	 * Set height of placeholder
	 */
	$("#dragdrop-sortable").on("sortstart", function(event, ui){
		var height = ui.item.height();
		$('#dragdrop-sortable li.dragdrop-highlight').height(height);
	});

	/**
	 * Initialize select2 
	 */
	$('.select2-text-select').select2({
		width: '100%'
	});
});
