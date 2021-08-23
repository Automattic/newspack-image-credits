const jQuery = window?.jQuery;

jQuery( function( $ ) {
	let frame;
	const imageSetting = document.querySelector( '.image-setting-wrapper' );
	const chooseButton = imageSetting.querySelector( 'input[name="choose-image"]' );
	const clearButton = imageSetting.querySelector( 'input[name="clear-image"]' );
	const input = imageSetting.querySelector( '#newspack_image_credits_placeholder' );
	const chooseImage = e => {
		e.preventDefault();

		// If we've already opened the Media Library.
		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media.frames.media = wp.media( {
			title: 'Select a placeholder image',
			button: {
				text: 'Choose Image',
			},
			library: {
				type: 'image',
			},
			multiple: false,
		} );

		// When an image is selected, fill the hidden input field.
		frame.on( 'select', function() {
			const attachment = frame
				.state()
				.get( 'selection' )
				.first()
				.toJSON();
			const { id = null, sizes = {} } = attachment;

			if ( id && sizes.medium?.url ) {
				let existingPreview = imageSetting.querySelector(
					'.newspack-image-credits-placeholder-preview'
				);
				let newClearButton = imageSetting.querySelector( 'input[name="clear-image"]' );

				if ( existingPreview ) {
					existingPreview.parentElement.removeChild( existingPreview );
				}

				const newPreview = document.createElement( 'img' );
				newPreview.className = 'newspack-image-credits-placeholder-preview';
				newPreview.setAttribute( 'data-id', id );
				newPreview.src = sizes.medium.url;
				imageSetting.insertBefore( newPreview, input.nextSibling );

				if ( ! newClearButton ) {
					const newClearButton = document.createElement( 'input' );
					newClearButton.className = 'button';
					newClearButton.name = 'clear-image';
					newClearButton.type = 'button';
					newClearButton.value = 'Clear Image';

					chooseButton.parentElement.insertBefore( newClearButton, chooseButton.nextSibling );
					newClearButton.addEventListener( 'click', clearImage );
				}

				input.value = id;
			}
		} );

		frame.on( 'open', () => {
			const preview = imageSetting.querySelector( '.newspack-image-credits-placeholder-preview' );
			if ( preview ) {
				const selected = frame.state().get( 'selection' );
				const attachment = wp.media.attachment( parseInt( preview.getAttribute( 'data-id' ) ) );
				selected.add( attachment || [] );
			}
		} );

		frame.open();
	};

	const clearImage = e => {
		e.preventDefault();
		const previewImage = document.querySelector( '.newspack-image-credits-placeholder-preview' );
		input.value = '';
		previewImage.parentElement.removeChild( previewImage );
		e.currentTarget.parentElement.removeChild( e.currentTarget );
	};

	if ( chooseButton ) {
		chooseButton.addEventListener( 'click', chooseImage );
	}

	if ( clearButton ) {
		clearButton.addEventListener( 'click', clearImage );
	}
} );
