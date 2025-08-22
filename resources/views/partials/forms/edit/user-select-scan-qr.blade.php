<div id="assigned_user" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!! isset($style) ? ' style="' . e($style) . '"' : '' !!}>

  <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

  <div class="col-md-7">
    {{-- Hidden field that will actually be submitted --}}
    <input type="hidden" name="{{ $fieldname }}" id="assigned_user_hidden"
      value="{{ old($fieldname, isset($item) ? $item->{$fieldname} : '') }}">

    {{-- Read-only (disabled) Select2. We'll still update its UI so users can see who got selected --}}
    <select class="js-data-ajax" data-endpoint="users" data-placeholder="{{ trans('general.select_user') }}"
      style="width: 100%" id="assigned_user_select" aria-label="{{ $fieldname }}" disabled> {{-- make UI read-only --}}
      @if ($user_id = old($fieldname, isset($item) ? $item->{$fieldname} : ''))
        <option value="{{ $user_id }}" selected="selected" role="option" aria-selected="true">
          {{ \App\Models\User::find($user_id) ? \App\Models\User::find($user_id)->present()->fullName : '' }}
        </option>
      @else
        <option value="">{{ trans('general.select_user') }}</option>
      @endif
    </select>
    <small class="help-block">
      {{ trans('admin/users/general.scan_user_qr_assignee') }}
    </small>
  </div>

  <div class="text-left col-md-1 col-sm-1">
    @can('create', \App\Models\User::class)
      @if (!isset($hide_new) || $hide_new != 'true')
        <a href='{{ route('modal.show', 'user') }}' data-toggle="modal" data-target="#createModal"
          data-select='assigned_user_select' class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
      @endif
    @endcan
  </div>

  {!! $errors->first(
      $fieldname,
      '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>',
  ) !!}
</div>

<script>
  (function() {
    const MSG_USER_NOT_FOUND = @json(trans('admin/users/general.user_not_found_from_qr'));
    const MSG_API_FAIL = @json(trans('admin/users/general.failed_fetch_user_from_api'));

    const selectEl = document.getElementById('assigned_user_select');
    const hiddenEl = document.getElementById('assigned_user_hidden');
    if (!selectEl || !hiddenEl) return;

    // -------- CONFIG you can tweak --------
    const MIN_LEN = 1; // minimum chars to treat as a scan
    const AVG_MAX_MS = 35; // average key interval threshold (scanner-like)
    const GAP_RESET_MS = 150; // gap that resets detection
    const END_KEYS = new Set(['Enter', 'Tab']);
    // If your scanner can send a unique prefix, set it here (example: "[SCAN]")
    const SCAN_PREFIX = ''; // e.g., '[SCAN]'
    const REQUIRE_PREFIX = false; // set true to only accept scans with SCAN_PREFIX

    // ---------- Helpers ----------
    function endpointUrl() {
      const ep = (selectEl.getAttribute('data-endpoint')) || 'users';
      const cleaned = ep.replace(/^\/+/, '').replace(/\/selectlist$/, '');
      return '/api/v1/' + cleaned + '/selectlist';
    }

    function assetStatusQuery() {
      const t = selectEl.getAttribute('data-asset-status-type');
      return t ? '&assetStatusType=' + encodeURIComponent(t) : '';
    }

    function buildHeaders() {
      const h = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      };
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (csrf) h['X-CSRF-TOKEN'] = csrf;

      // Optional Bearer (if your layout exposes it)
      const token =
        document.querySelector('meta[name="api-token"]')?.content ||
        (window.SnipeIT && (window.SnipeIT.api_token || (window.SnipeIT.settings && window.SnipeIT.settings
          .api_token))) ||
        window.API_TOKEN || null;

      if (token) h['Authorization'] = 'Bearer ' + token;
      return h;
    }

    function parseQr(payload) {
      payload = (payload || '').trim();

      // If use a unique prefix, strip it first
      if (SCAN_PREFIX && payload.startsWith(SCAN_PREFIX)) {
        payload = payload.slice(SCAN_PREFIX.length);
      }

      const idOnly = payload.match(/^\d+$/);
      if (idOnly) return {
        type: 'id',
        value: idOnly[0]
      };

      const idKV = payload.match(/(?:^|[?&#;,\s])(?:user_id|id)[:=]\s*(\d+)/i);
      if (idKV) return {
        type: 'id',
        value: idKV[1]
      };

      // For employee number or email, switch to one of these:
      // return { type: 'search', value: 'employee_num:' + payload };
      // return { type: 'search', value: 'email:' + payload };

      return {
        type: 'search',
        value: payload
      };
    }

    async function fetchUser(match) {
      const q = match.type === 'id' ? 'id:' + match.value : match.value;
      const url = endpointUrl() + '?search=' + encodeURIComponent(q) + '&page=1' + assetStatusQuery();

      const res = await fetch(url, {
        method: 'GET',
        headers: buildHeaders(),
        credentials: 'same-origin',
      });

      if (!res.ok) {
        throw new Error('API error ' + res.status);
      }

      const data = await res.json();
      const arr = (data.results || data.data || data.rows) || [];
      if (!Array.isArray(arr) || !arr.length) return null;

      const first = arr[0];
      const id = first.id ?? first.user_id ?? first.value;
      const text = first.text || first.name || first.username || first.email || ('User #' + id);
      return id ? {
        id,
        text
      } : null;
    }

    function applyUser(user) {
      if (!user) {
        alert(MSG_USER_NOT_FOUND);
        return;
      }

      const selectEl = document.getElementById('assigned_user_select');
      const hiddenEl = document.getElementById('assigned_user_hidden');

      // 1) Set the submitted value
      hiddenEl.value = user.id;

      // 2) Ensure an option exists and is selected
      let opt = selectEl.querySelector('option[value="' + String(user.id) + '"]');
      if (!opt) {
        opt = new Option(user.text, user.id, true, true);
        selectEl.appendChild(opt);
      } else {
        opt.textContent = user.text;
        opt.selected = true;
      }

      // 3) Make sure "Checkout to" is set to User so the right column logic is enabled
      const userRadio = document.querySelector('input[name="checkout_to_type"][value="user"]');
      if (userRadio) {
        userRadio.checked = true;
        // bubble so any listeners on containers catch it
        userRadio.dispatchEvent(new Event('change', {
          bubbles: true
        }));
        userRadio.dispatchEvent(new Event('click', {
          bubbles: true
        })); // some code listens to clicks
      }

      // 4) Trigger the events the app expects (NOT 'change.select2')
      if (window.jQuery) {
        const $el = window.jQuery(selectEl);
        // keep select2's internal value in sync
        $el.val(String(user.id));

        // fire plain 'change' so vanilla handlers run
        $el.trigger('change');

        // fire select2's select event with the same shape Snipe-IT uses
        $el.trigger({
          type: 'select2:select',
          params: {
            data: {
              id: String(user.id),
              text: user.text
            }
          }
        });
      } else {
        // vanilla fallback – some handlers listen to native 'change'
        selectEl.dispatchEvent(new Event('change', {
          bubbles: true
        }));
        selectEl.dispatchEvent(new CustomEvent('select2:select', {
          bubbles: true,
          detail: {
            data: {
              id: String(user.id),
              text: user.text
            }
          }
        }));
      }

      // Optional: if your template exposes a helper to load the box directly, call it too
      if (window.SnipeIT && typeof window.SnipeIT.loadAssignedAssets === 'function') {
        window.SnipeIT.loadAssignedAssets(String(user.id));
      }

      // Optional safety: if the box is still hidden, show it (content should be filled by the handler)
      const box = document.getElementById('current_assets_box');
      if (box && (box.style.display === 'none' || getComputedStyle(box).display === 'none')) {
        box.style.display = '';
      }
    }

    // ---------- Scanner detection WITHOUT focus ----------
    let buffer = '';
    let intervals = [];
    let lastTs = 0;

    // For reverting any text that leaked into a focused field before recognition
    let startTarget = null;
    let startValue = '';
    let startSelStart = null,
      startSelEnd = null;

    function isTextInput(el) {
      if (!el) return false;
      const tag = el.tagName && el.tagName.toLowerCase();
      const type = el.type && el.type.toLowerCase();
      return (tag === 'input' && ['text', 'search', 'email', 'number', 'tel', 'url', 'password'].includes(type)) ||
        tag === 'textarea' || el.isContentEditable;
    }

    function reset() {
      buffer = '';
      intervals = [];
      lastTs = 0;
      startTarget = null;
      startValue = '';
      startSelStart = startSelEnd = null;
    }

    function likelyScanner() {
      if (REQUIRE_PREFIX && !(SCAN_PREFIX && buffer.startsWith(SCAN_PREFIX))) return false;
      const core = SCAN_PREFIX && buffer.startsWith(SCAN_PREFIX) ? buffer.slice(SCAN_PREFIX.length) : buffer;
      if (core.length < MIN_LEN) return false;
      if (!intervals.length) return false;
      const avg = intervals.reduce((a, b) => a + b, 0) / intervals.length;
      return avg <= AVG_MAX_MS;
    }

    function revertStartField() {
      if (!isTextInput(startTarget)) return;
      if (startTarget.isContentEditable) {
        startTarget.innerText = startValue;
      } else {
        startTarget.value = startValue;
        if (typeof startTarget.setSelectionRange === 'function' && startSelStart != null) {
          startTarget.setSelectionRange(startSelStart, startSelEnd);
        }
        startTarget.dispatchEvent(new Event('input', {
          bubbles: true
        }));
        startTarget.dispatchEvent(new Event('change', {
          bubbles: true
        }));
      }
    }

    window.addEventListener('keydown', async function(e) {
      const now = (window.performance && performance.now) ? performance.now() : Date.now();

      // Commit on Enter/Tab
      if (END_KEYS.has(e.key)) {
        if (buffer && likelyScanner()) {
          e.preventDefault();
          e.stopPropagation();

          const payload = buffer;
          revertStartField();

          try {
            const match = parseQr(SCAN_PREFIX && payload.startsWith(SCAN_PREFIX) ?
              payload.slice(SCAN_PREFIX.length) :
              payload);
            const user = await fetchUser(match);
            applyUser(user);
          } catch (err) {
            console.error(err);
            alert(MSG_API_FAIL);
          } finally {
            reset();
          }
        } else {
          reset(); // normal typing
        }
        return;
      }

      // Only record printable characters
      if (e.key.length === 1) {
        if (!buffer) {
          startTarget = document.activeElement;
          if (isTextInput(startTarget)) {
            startValue = startTarget.isContentEditable ? startTarget.innerText : startTarget.value;
            if (!startTarget.isContentEditable && startTarget.selectionStart != null) {
              startSelStart = startTarget.selectionStart;
              startSelEnd = startTarget.selectionEnd;
            }
          }
          lastTs = now;
          buffer += e.key;
        } else {
          const gap = now - lastTs;
          if (gap > GAP_RESET_MS) {
            reset();
            return;
          } // treat as human typing
          intervals.push(gap);
          lastTs = now;
          buffer += e.key;
        }
        // don't preventDefault here; we revert if it's a scan
        return;
      }

      // Any other key while collecting: reset if idle too long
      if (buffer) {
        const gap = now - lastTs;
        if (gap > GAP_RESET_MS) reset();
      }
    }, true); // capture phase
  })();
</script>
