import '@wordpress/edit-post';
import domReady from '@wordpress/dom-ready';
import '../styles/editor.scss';
import {
  unregisterBlockStyle,
  registerBlockStyle,
} from '@wordpress/blocks';

domReady(() => {
  unregisterBlockStyle('core/button', 'outline');

  registerBlockStyle('core/button', {
    name: 'outline',
    label: 'Outline',
  });
});
