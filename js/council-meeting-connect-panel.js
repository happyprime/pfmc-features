// WordPress dependencies.
import { CheckboxControl } from '@wordpress/components';

import { dispatch, withSelect } from '@wordpress/data';

import { addFilter } from '@wordpress/hooks';

import { __ } from '@wordpress/i18n';

function addPinnedTermControl( OriginalComponent ) {
	return ( props ) => {
		const { slug, terms } = props;

		if ( 'council_meeting_connect' !== slug ) {
			return <OriginalComponent { ...props } />;
		}

		const PinnedTermControl = withSelect( ( select ) => {
			const query = {
				meta_key: '_pinned',
				meta_value: 1,
			};

			return {
				pinnedTerms: select( 'core' ).getEntityRecords( 'taxonomy', 'council_meeting_connect', query ),
			};
		} )( ( { pinnedTerms } ) => {
			// Return early if there are no pinned terms.
			if ( ! pinnedTerms ) {
				return null;
			}

			return (
				<>
					<p>{ __( 'Pinned Terms' ) }</p>
					<div style={ {
						margin: '-6px 0 1em -6px',
						maxHeight: '10.5em',
						overflow: 'auto',
						padding: '6px 0 2px 6px',
					} } >
						{ pinnedTerms.map( ( term ) => (
							<CheckboxControl
								label={ term.name }
								checked={ ( props.terms && props.terms.includes( term.id ) ) }
								onChange={ () => {
									let newTerms = terms;

									if ( ! terms.includes( term.id ) ) {
										newTerms = [ ...terms, term.id ];
									} else {
										newTerms = terms.filter( ( t ) => t !== term.id );
									}

									dispatch( 'core/editor' ).editPost( {
										council_meeting_connect: newTerms,
									} );
								} }
							/>
						) ) }
					</div>
				</>
			);
		} );

		return (
			<>
				<PinnedTermControl />
				<OriginalComponent { ...props } />
			</>
		);
	};
}

addFilter(
	'editor.PostTaxonomyType',
	'pfmc-feature-set/add-pinned-term-control',
	addPinnedTermControl
);
