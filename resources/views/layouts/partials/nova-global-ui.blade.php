{{-- NovaSMM Global UI: Search Overlay, Loading, Empty helpers --}}
{{-- Search Overlay --}}
<div id="nova-search-overlay" class="nova-search-overlay" aria-hidden="true" role="dialog" aria-label="{{ __t('Tìm kiếm') }}">
  <div class="nova-search-backdrop" data-nova-search-close></div>
  <div class="nova-search-panel">
    <div class="nova-search-input-wrap">
      <i class="fe fe-search nova-search-icon" aria-hidden="true"></i>
      <input type="search" id="nova-search-input" class="nova-search-input" placeholder="{{ __t('Tìm đơn hàng, dịch vụ...') }}" autocomplete="off" aria-label="{{ __t('Tìm kiếm') }}">
      <kbd class="nova-search-kbd">ESC</kbd>
    </div>
    <div class="nova-search-body">
      <div class="nova-search-hint text-muted small px-3 py-2" id="nova-search-hint">
        {{ __t('Gõ để tìm kiếm') }} · <kbd>Ctrl</kbd>+<kbd>K</kbd>
      </div>
      <div class="nova-search-loading d-none" id="nova-search-loading">
        <div class="nova-spinner mx-auto my-4"></div>
      </div>
      <div class="nova-search-results d-none" id="nova-search-results" role="listbox"></div>
      <div class="nova-search-empty d-none" id="nova-search-empty">
        <div class="text-center py-4">
          <i class="fe fe-inbox fs-2" style="color:#64748B"></i>
          <p class="text-muted mt-2 mb-0 small">{{ __t('Không tìm thấy kết quả') }}</p>
        </div>
      </div>
      <div class="nova-search-shortcuts px-3 pb-3">
        <div class="small text-muted mb-2">{{ __t('Lối tắt') }}</div>
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ route('home') }}" class="nova-search-chip"><i class="fe fe-shopping-cart me-1"></i>{{ __t('Tạo đơn') }}</a>
          <a href="{{ route('account.orders') }}" class="nova-search-chip"><i class="fe fe-list me-1"></i>{{ __t('Đơn hàng') }}</a>
          @auth
          <a href="{{ route('account.deposits.transfer') }}" class="nova-search-chip"><i class="fe fe-credit-card me-1"></i>{{ __t('Nạp tiền') }}</a>
          <a href="{{ route('account.profile.index') }}" class="nova-search-chip"><i class="fe fe-user me-1"></i>{{ __t('Tài khoản') }}</a>
          @endauth
          @if(auth()->check() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
          <a href="{{ route('admin.dashboard') }}" class="nova-search-chip"><i class="fe fe-shield me-1"></i>Admin</a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const overlay = document.getElementById('nova-search-overlay');
  const input = document.getElementById('nova-search-input');
  const trigger = document.getElementById('nova-search-trigger');
  if (!overlay || !input) return;

  function openSearch() {
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    setTimeout(() => input.focus(), 50);
  }
  function closeSearch() {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    input.value = '';
    document.getElementById('nova-search-results')?.classList.add('d-none');
    document.getElementById('nova-search-empty')?.classList.add('d-none');
    document.getElementById('nova-search-loading')?.classList.add('d-none');
    document.getElementById('nova-search-hint')?.classList.remove('d-none');
  }

  trigger?.addEventListener('click', (e) => { e.preventDefault(); openSearch(); });
  overlay.querySelectorAll('[data-nova-search-close]').forEach(el => el.addEventListener('click', closeSearch));

  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      openSearch();
    }
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
      closeSearch();
    }
  });

  // Client-side filter of shortcut chips only — no fake API results
  let debounce;
  input.addEventListener('input', () => {
    clearTimeout(debounce);
    const q = input.value.trim().toLowerCase();
    const hint = document.getElementById('nova-search-hint');
    const empty = document.getElementById('nova-search-empty');
    const results = document.getElementById('nova-search-results');
    if (!q) {
      hint?.classList.remove('d-none');
      empty?.classList.add('d-none');
      results?.classList.add('d-none');
      return;
    }
    hint?.classList.add('d-none');
    // Filter visible chips by label text
    const chips = overlay.querySelectorAll('.nova-search-chip');
    let match = 0;
    chips.forEach(chip => {
      const show = chip.textContent.toLowerCase().includes(q);
      chip.style.display = show ? '' : 'none';
      if (show) match++;
    });
    if (match === 0) {
      empty?.classList.remove('d-none');
    } else {
      empty?.classList.add('d-none');
    }
  });
})();
</script>
