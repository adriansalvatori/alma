import apiFetch from '@wordpress/api-fetch';
import { dispatch, select, subscribe } from '@wordpress/data';
import domReady from '@wordpress/dom-ready';

/**
 * Code-owned pattern pages (App\Domains\Content\Patterns\PatternPageEditing).
 *
 * A page whose content is one `wp:pattern` reference is rendered from patterns/<file>.php. Block
 * edits to it are not saved (the server keeps the reference), so say so up front, and offer the
 * one way to make the blocks editable here: detach the page from the pattern.
 */
domReady(() => {
  const page = window.almaPatternPage;

  if (! page) {
    return;
  }

  let detaching = false;

  // Add the detach flag to the save request for this page only, and only once asked.
  apiFetch.use((options, next) => {
    const method = (options.method || 'GET').toUpperCase();
    const target = new RegExp(`/wp/v2/[^/]+/${page.postId}(\\?|$)`);

    if (detaching && (method === 'POST' || method === 'PUT') && target.test(options.path || options.url || '')) {
      options.data = { ...(options.data || {}), [page.param]: true };
    }

    return next(options);
  });

  const notices = dispatch('core/notices');

  notices.createWarningNotice(
    `This page is managed in code: ${page.file}. Changes to its blocks are not saved; title, slug, featured image and SEO fields are.`,
    {
      id: 'alma-pattern-page',
      isDismissible: false,
      actions: [
        {
          label: 'Detach from pattern and edit here',
          onClick: () => {
            // eslint-disable-next-line no-alert
            if (! window.confirm('Detach this page? Its blocks become editable here, and later changes to the pattern file will no longer reach it.')) {
              return;
            }

            detaching = true;
            dispatch('core/editor').savePost();
          },
        },
      ],
    },
  );

  // Once the detaching save has gone through, the page is ordinary content.
  const unsubscribe = subscribe(() => {
    const editor = select('core/editor');

    if (detaching && ! editor.isSavingPost() && editor.didPostSaveRequestSucceed()) {
      detaching = false;
      window.almaPatternPage = null;
      notices.removeNotice('alma-pattern-page');
      notices.createSuccessNotice('Detached. This page is now edited here.', { type: 'snackbar' });
      unsubscribe();
    }
  });
});
