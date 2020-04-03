const { registerPlugin } = wp.plugins;

const { __ } = wp.i18n;

const { PluginPostStatusInfo } = wp.editPost;

const { CheckboxControl } = wp.components;

const { dispatch, select, subscribe, withSelect } = wp.data;

// Gets current sticky status from localized data.
let currentStatus = window.StickyStatus.isSticky;

// Updates currentStatus if the StickyStatus attribute has been modified.
function watchStatus() {
	const newStatus = select( 'core/editor' ).getEditedPostAttribute(
		'StickyStatus'
	);

	if ( newStatus !== undefined && newStatus !== currentStatus ) {
		currentStatus = newStatus;
	}
};

// Dispatch sticky status when modified.
function onUpdateStickyStatus( value ) {
	dispatch( 'core/editor' ).editPost( { StickyStatus: value } );
};

function stickyCheckbox() {
	return (
		<CheckboxControl
			label={ __( 'Stick to the top of the blog' ) }
			checked={ currentStatus }
			onChange={ ( value ) => onUpdateStickyStatus( value ) }
		/>
	);
};

// Register stickyCheckbox as a useable component.
const StickyCheckboxControl = withSelect( () => {
	const stickyStatusKey = select( 'core/editor' ).getEditedPostAttribute(
		'StickyStatus'
	);

	return {
		stickyStatusKey,
	};
} )( stickyCheckbox );

// Subscribes to editor changes.
subscribe( () => watchStatus() );

// Register as a plugin and render inside the PluginPostStatusInfo component.
registerPlugin( 'cpt-sticky-checkbox', {
	render() {
		return (
			<PluginPostStatusInfo>
				<StickyCheckboxControl />
			</PluginPostStatusInfo>
		);
	},
} );
