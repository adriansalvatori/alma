@php
  $labels = [
    'info' => __('System notice', 'alma'),
    'error' => __('Critical error', 'alma'),
    'warning' => __('Warning', 'alma'),
    'success' => __('Success', 'alma'),
    'core' => __('WordPress core', 'alma'),
    'alert' => __('Alert', 'alma'),
    'alerts' => __('Alerts', 'alma'),
    'dismiss' => __('Dismiss notification', 'alma'),
  ];
@endphp
{{--
  The toolbar bell's drawer (AdminTheme::renderNotifications()), printed in the footer of every
  wp-admin screen and of front-end pages that show the toolbar.

  The script moves wp-admin's notices out of the page into the drawer, classified by severity,
  and counts them on the bell. A dismissed notice stays dismissed for the browser session, and
  "Restore inline" puts every notice back where WordPress printed it. A notice with the
  `alma-keep-notice` class is never moved: the console sheet only hides the others.
--}}
<div id="alma-notif-backdrop" class="alma-notif-backdrop" aria-hidden="true"></div>
<aside id="alma-notif-drawer" class="alma-notif-drawer" aria-label="{{ __('Notifications', 'alma') }}" aria-hidden="true">
  <div class="alma-notif-drawer-header">
    <div class="alma-notif-header-title-wrap">
      <svg class="alma-notif-header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
      <h2 class="alma-notif-header-title">{{ __('Notifications', 'alma') }}</h2>
      <span id="alma-notif-total-badge" class="alma-notif-pill">0</span>
    </div>
    <div class="alma-notif-header-actions">
      <button type="button" id="alma-notif-clear-all" class="alma-notif-btn-clear" title="{{ __('Dismiss all notifications', 'alma') }}">{{ __('Dismiss all', 'alma') }}</button>
      <button type="button" id="alma-notif-close-btn" class="alma-notif-btn-close" aria-label="{{ __('Close notifications', 'alma') }}">&times;</button>
    </div>
  </div>

  <div class="alma-notif-drawer-body" id="alma-notif-list"></div>

  <div class="alma-notif-empty-state" id="alma-notif-empty" style="display: none;">
    <div class="alma-notif-empty-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <p class="alma-notif-empty-title">{{ __('All caught up', 'alma') }}</p>
    <p class="alma-notif-empty-desc">{{ __('No active system alerts, environment warnings, or pending notices.', 'alma') }}</p>
  </div>

  <div class="alma-notif-drawer-footer">
    <span class="alma-notif-footer-text">{{ sprintf(__('%s system hub', 'alma'), $brand) }}</span>
    <button type="button" id="alma-notif-restore-btn" class="alma-notif-link-subtle" title="{{ __('Show notices back inline', 'alma') }}">{{ __('Restore inline', 'alma') }}</button>
  </div>
</aside>

<script>
(function () {
  var labels = @json($labels);

  function initNotificationsCenter() {
    var drawer = document.getElementById('alma-notif-drawer');
    var backdrop = document.getElementById('alma-notif-backdrop');
    var trigger = document.getElementById('alma-notif-trigger');
    var closeBtn = document.getElementById('alma-notif-close-btn');
    var clearAllBtn = document.getElementById('alma-notif-clear-all');
    var restoreBtn = document.getElementById('alma-notif-restore-btn');
    var listContainer = document.getElementById('alma-notif-list');
    var emptyState = document.getElementById('alma-notif-empty');
    var badge = document.getElementById('alma-notif-badge');
    var totalBadge = document.getElementById('alma-notif-total-badge');

    if (!drawer || !listContainer) return;

    var dismissedStorageKey = 'alma_dismissed_notices_v1';
    var dismissedHashes = [];
    try {
      dismissedHashes = JSON.parse(sessionStorage.getItem(dismissedStorageKey) || '[]');
    } catch (e) { dismissedHashes = []; }

    function hashString(str) {
      var hash = 0;
      for (var i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash |= 0;
      }
      return 'h_' + hash;
    }

    function openDrawer() {
      document.body.classList.add('alma-notif-open');
      drawer.setAttribute('aria-hidden', 'false');
      if (backdrop) backdrop.setAttribute('aria-hidden', 'false');
    }

    function closeDrawer() {
      document.body.classList.remove('alma-notif-open');
      drawer.setAttribute('aria-hidden', 'true');
      if (backdrop) backdrop.setAttribute('aria-hidden', 'true');
    }

    if (trigger) {
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        if (document.body.classList.contains('alma-notif-open')) {
          closeDrawer();
        } else {
          openDrawer();
        }
      });
    }
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('alma-notif-open')) {
        closeDrawer();
      }
    });

    // Capture WordPress admin notices from the content flow.
    var noticeSelector = '#wpbody-content .notice, #wpbody-content div.updated, #wpbody-content div.error, #wpbody-content .update-nag, .wrap > .notice, .wrap > div.updated, .wrap > div.error';
    var rawNotices = document.querySelectorAll(noticeSelector);

    rawNotices.forEach(function (el) {
      if (el.closest('#alma-notif-drawer') || el.classList.contains('alma-intercepted') || el.classList.contains('alma-keep-notice') || el.classList.contains('inline')) return;

      var rawText = (el.innerText || el.textContent || '').trim();
      if (!rawText) return;

      var hash = hashString(rawText);
      el.classList.add('alma-intercepted');

      // Hidden from the page either way; a notice dismissed this session stays out of the drawer too.
      el.style.display = 'none';
      if (dismissedHashes.indexOf(hash) !== -1) return;

      var severity = 'info';
      var tagLabel = labels.info;
      var tagClass = 'alma-tag-info';

      if (el.classList.contains('notice-error') || el.classList.contains('error')) {
        severity = 'error';
        tagLabel = labels.error;
        tagClass = 'alma-tag-error';
      } else if (el.classList.contains('notice-warning') || el.classList.contains('update-nag')) {
        severity = 'warning';
        tagLabel = labels.warning;
        tagClass = 'alma-tag-warning';
      } else if (el.classList.contains('notice-success') || el.classList.contains('updated')) {
        severity = 'success';
        tagLabel = labels.success;
        tagClass = 'alma-tag-success';
      }

      // Name the source when it is obvious from the text.
      if (rawText.indexOf('WooCommerce') !== -1) {
        tagLabel = 'WooCommerce';
      } else if (rawText.indexOf('ACF') !== -1) {
        tagLabel = 'ACF Pro';
      } else if (rawText.indexOf('WordPress') !== -1 || el.classList.contains('update-nag')) {
        tagLabel = labels.core;
      }

      var card = document.createElement('div');
      card.className = 'alma-notif-card alma-notif-' + severity;
      card.setAttribute('data-hash', hash);

      var cardHeader = document.createElement('div');
      cardHeader.className = 'alma-notif-card-header';

      var tag = document.createElement('span');
      tag.className = 'alma-notif-tag ' + tagClass;
      tag.textContent = tagLabel;

      var dismissBtn = document.createElement('button');
      dismissBtn.type = 'button';
      dismissBtn.className = 'alma-notif-dismiss-item-btn';
      dismissBtn.title = labels.dismiss;
      dismissBtn.innerHTML = '&times;';

      cardHeader.appendChild(tag);
      cardHeader.appendChild(dismissBtn);

      // The notice's own markup, which WordPress (or the plugin that printed it) already escaped.
      var cardBody = document.createElement('div');
      cardBody.className = 'alma-notif-card-body';
      cardBody.innerHTML = el.innerHTML;

      var nativeDismiss = cardBody.querySelector('.notice-dismiss');
      if (nativeDismiss) nativeDismiss.remove();

      card.appendChild(cardHeader);
      card.appendChild(cardBody);

      dismissBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        card.classList.add('is-dismissing');
        setTimeout(function () {
          card.remove();
          dismissedHashes.push(hash);
          try {
            sessionStorage.setItem(dismissedStorageKey, JSON.stringify(dismissedHashes));
          } catch (e) {}

          // Let WordPress record the dismissal too, when the notice supports it.
          var origDismiss = el.querySelector('.notice-dismiss');
          if (origDismiss) origDismiss.click();

          updateBadgeCount();
        }, 200);
      });

      listContainer.appendChild(card);
    });

    function updateBadgeCount() {
      var remaining = listContainer.querySelectorAll('.alma-notif-card').length;
      if (badge) {
        badge.textContent = remaining;
        badge.style.display = remaining > 0 ? 'inline-flex' : 'none';
      }
      if (totalBadge) {
        totalBadge.textContent = remaining + ' ' + (remaining === 1 ? labels.alert : labels.alerts);
      }
      if (emptyState) {
        emptyState.style.display = remaining === 0 ? 'flex' : 'none';
      }
    }

    if (clearAllBtn) {
      clearAllBtn.addEventListener('click', function () {
        listContainer.querySelectorAll('.alma-notif-card').forEach(function (c) {
          var h = c.getAttribute('data-hash');
          if (h && dismissedHashes.indexOf(h) === -1) {
            dismissedHashes.push(h);
          }
          c.remove();
        });
        try {
          sessionStorage.setItem(dismissedStorageKey, JSON.stringify(dismissedHashes));
        } catch (e) {}
        updateBadgeCount();
      });
    }

    if (restoreBtn) {
      restoreBtn.addEventListener('click', function () {
        rawNotices.forEach(function (el) {
          el.style.display = '';
          el.classList.add('alma-keep-notice');
        });
        closeDrawer();
      });
    }

    updateBadgeCount();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotificationsCenter);
  } else {
    initNotificationsCenter();
  }
})();
</script>
