<header class="app-header">

  <!-- Start::main-header-container -->
  <div class="main-header-container container-fluid">

    <!-- Start::header-content-left -->
    <div class="header-content-left">

      <!-- Start::header-element -->
      <div class="header-element">
        <div class="horizontal-logo">
          <a href="{{ route('home') }}" class="header-logo d-flex align-items-center gap-2">
            <img src="/assets/images/brand-logos/nova-icon.svg" alt="NovaSMM" class="toggle-logo toggle-dark" width="32" height="32" style="min-width:32px">
            <span class="desktop-logo desktop-dark fw-bold d-none d-lg-inline" style="font-size:1.15rem;background:linear-gradient(135deg,#8B5CF6,#2563EB);-webkit-background-clip:text;-webkit-text-fill-color:transparent">NovaSMM</span>
            <img src="{{ setting('logo_dark', '/assets/images/brand-logos/nova-logo.svg') }}" alt="NovaSMM" class="desktop-logo d-none" width="160">
            <img src="{{ setting('favicon', '/assets/images/brand-logos/nova-icon.svg') }}" alt="NovaSMM" class="toggle-logo d-none" width="32">
          </a>
        </div>
      </div>
      <!-- End::header-element -->

      <!-- Start::header-element -->
      <div class="header-element">
        <!-- Start::header-link -->
        <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle mx-0 my-auto" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
        <!-- End::header-link -->
      </div>
      <!-- End::header-element -->

    </div>
    <!-- End::header-content-left -->

    <!-- Start::header-content-right -->
    <div class="header-content-right">

      <!-- Start::header-element -->
      @php
        $langs = getLangs();
        $current_lang = json_decode(currentLang(true), true);
      @endphp
      <div class="header-element">
        <!-- Start::header-link|dropdown-toggle -->
        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown">
          <img src="{{ $current_lang['flag'] ?? '/assets/images/flags/us_flag.jpg' }}" alt="img" width="20" class="">
        </a>
        <!-- End::header-link|dropdown-toggle -->
        <ul class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none" style="max-width: 200px">
          @foreach ($langs as $lang)
            <li>
              <a class="dropdown-item d-flex align-items-center @if ($lang['code'] == ($current_lang['code'] ?? 'vn')) active @endif" href="{{ route('set-locale', ['locale' => $lang['code']]) }}">
                <span class="avatar avatar-xs lh-1 me-2">
                  <img src="{{ $lang['flag'] }}" alt="img">
                </span>
                {{ $lang['name'] }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
      <!-- End::header-element -->

      <!-- Start::header-element -->
      <div class="header-element">
        <!-- Start::header-link|dropdown-toggle -->
        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown">
          <i class="fe fe-dollar-sign header-link-icon"></i>
          <span class="pulse-danger"></span>
        </a>
        <!-- End::header-link|dropdown-toggle -->
        <!-- Start::main-header-dropdown -->
        @php
          $currency_list = \App\Models\CurrencyList::get();
        @endphp
        <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
          <div class="p-3">
            <div class="d-flex align-items-center justify-content-between">
              <p class="mb-0 fs-17 fw-semibold">{{ __t('Chọn loại tiền tệ') }}</p>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <ul class="list-unstyled mb-0" id="header-notification-scroll">
            @foreach ($currency_list as $value)
              <li class="dropdown-item cursor-pointer @if ($value['currency_code'] === (auth()->user()->currency_code ?? 'VND')) bg-info text-white @endif" onclick="setCurrency({{ $value['id'] ?? 'null' }})">
                <div class="d-flex align-items-start">
                  <div class="pe-2">
                    <span
                      class="avatar avatar-md @if ($value['currency_code'] === (auth()->user()->currency_code ?? 'VND')) bg-primary box-shadow-primary @else bg-primary-gradient box-shadow-primary @endif avatar-rounded">{{ $value['currency_symbol'] }}</span>
                  </div>
                  <div class="flex-grow-1 d-flex align-items-center justify-content-between">
                    <div>
                      <p class="mb-0 fw-semibold">
                        {{ $value['currency_code'] }}
                      </p>
                      <span class="text-muted fw-normal fs-12 header-notification-text @if ($value['currency_code'] === (auth()->user()->currency_code ?? 'VND')) text-white @endif">{{ formatCurrencyF(auth()->user()->balance ?? 0, $value) }}</span>
                    </div>
                  </div>
                </div>
              </li>
            @endforeach
          </ul>
          {{-- <div class="p-3 empty-header-item1 border-top">
            <div class="d-grid">
              <a href="javascript:void(0)" class="btn text-muted p-0 border-0" onclick="setCurrency(null)">{{ __t('Đặt lại mặt định') }}</a>
            </div>
          </div> --}}
          <div class="p-5 empty-item1 d-none">
            <div class="text-center">
              <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                <i class="ri-notification-off-line fs-2"></i>
              </span>
              <h6 class="fw-semibold mt-3">{{ __t('Không có loại tiền tệ thay thế') }}</h6>
            </div>
          </div>
        </div>
        <!-- End::main-header-dropdown -->
      </div>
      <!-- End::header-element -->

      <!-- Start::header-element -->
      <div class="header-element header-theme-mode">
        <!-- Start::header-link|layout-setting -->
        <a href="javascript:void(0);" class="header-link layout-setting">
          <span class="light-layout">
            <!-- Start::header-link-icon -->
            <i class="fe fe-moonfe fe-moon header-link-icon align-middle"></i>
            <!-- End::header-link-icon -->
          </span>
          <span class="dark-layout">
            <!-- Start::header-link-icon -->
            <i class="fe fe-sun header-link-icon"></i>
            <!-- End::header-link-icon -->
          </span>
        </a>
        <!-- End::header-link|layout-setting -->
      </div>
      <!-- End::header-element -->


      {{-- NovaSMM Global Search Trigger --}}
      <div class="header-element d-none d-md-flex">
        <a href="javascript:void(0);" class="header-link" id="nova-search-trigger" aria-label="{{ __t('Tìm kiếm') }}" title="Ctrl + K">
          <i class="fe fe-search header-link-icon"></i>
        </a>
      </div>

      {{-- NovaSMM Notification Center (UI shell — no mock data) --}}
      <div class="header-element notifications-dropdown">
        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-label="{{ __t('Thông báo') }}" aria-expanded="false">
          <i class="fe fe-bell header-link-icon"></i>
          <span class="nova-notif-badge d-none" id="nova-notif-count" aria-hidden="true">0</span>
        </a>
        <div class="main-header-dropdown dropdown-menu dropdown-menu-end nova-notif-dropdown" data-popper-placement="none" style="min-width:320px;max-width:360px">
          <div class="p-3 border-bottom border-white-10 d-flex align-items-center justify-content-between">
            <p class="mb-0 fs-16 fw-semibold" style="color:#F8FAFC">{{ __t('Thông báo') }}</p>
            <button type="button" class="btn btn-sm btn-link p-0 text-muted nova-mark-all-read d-none" id="nova-mark-all-read">{{ __t('Đánh dấu đã đọc') }}</button>
          </div>
          <ul class="list-unstyled mb-0 nova-notif-list" id="header-notification-scroll" style="max-height:320px;overflow-y:auto">
            {{-- Populated by backend when notification API exists. Empty state below. --}}
          </ul>
          <div class="p-4 nova-notif-empty" id="nova-notif-empty">
            <div class="text-center">
              <span class="avatar avatar-xl avatar-rounded" style="background:rgba(139,92,246,0.15)">
                <i class="ri-notification-off-line fs-2" style="color:#A78BFA"></i>
              </span>
              <h6 class="fw-semibold mt-3 mb-1" style="color:#F8FAFC">{{ __t('Không có thông báo mới') }}</h6>
              <p class="text-muted mb-0 small" style="color:#64748B !important">{{ __t('Khi có cập nhật đơn hàng hoặc hệ thống, bạn sẽ thấy tại đây.') }}</p>
            </div>
          </div>
        </div>
      </div>


      @if (auth()->check() && auth()->user()->isAdmin())
        <!-- Start::header-element -->
        <div class="header-element">
          <!-- Start::header-link -->
          <a href="{{ route('admin.dashboard') }}" class="header-link" data-bs-toggle="tooltip" data-bs-title="{{ __t('Truy cập trang quản trị') }}">
            <i class="ti ti-shield header-link-icon"></i>
          </a>
          <!-- End::header-link -->
        </div>
        <!-- End::header-element -->
      @endif

      <!-- Start::header-element -->
      <div class="header-element profile-1">
        <!-- Start::header-link|dropdown-toggle -->
        <a href="#" class=" dropdown-toggle leading-none d-flex px-1" id="mainHeaderProfile" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
          <div class="d-flex align-items-center">
            <div class="">
              <img src="{{ setting('avatar_user', '/assets/images/faces/9.jpg') }}" alt="img" class="rounded-circle avatar  profile-user brround cover-image">
            </div>
          </div>
        </a>
        <!-- End::header-link|dropdown-toggle -->
        <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end" aria-labelledby="mainHeaderProfile">
          @auth
            <li><a class="dropdown-item d-flex" href="{{ route('account.deposits.transfer') }}"><i class="ti ti-credit-card fs-18 me-2 op-7"></i>{{ __t('Nạp tiền') }}</a></li>
            <li><a class="dropdown-item d-flex" href="{{ route('account.profile.index') }}"><i class="ti ti-user-circle fs-18 me-2 op-7"></i>{{ __t('Tài khoản') }}</a></li>
            <li><a class="dropdown-item d-flex" href="javascript:void(0)" onclick="$logout()"><i class="ti ti-logout fs-18 me-2 op-7"></i>{{ __t('Đăng xuất') }}</a></li>
          @else
            <li><a class="dropdown-item d-flex" href="{{ route('register') }}"><i class="ti ti-user-circle fs-18 me-2 op-7"></i>{{ __t('Đăng ký') }}</a></li>
            <li><a class="dropdown-item d-flex" href="{{ route('login') }}"><i class="ti ti-lock fs-18 me-2 op-7"></i>{{ __t('Đăng nhập') }}</a></li>
          @endauth
        </ul>
      </div>
      <!-- End::header-element -->

      <!-- Start::header-element -->
      {{-- <div class="header-element">
        <!-- Start::header-link|switcher-icon -->
        <a href="javascript:void(0)" class="header-link switcher-icon" data-bs-toggle="offcanvas" data-bs-target="#switcher-canvas">
          <i class="fe fe-settings header-link-icon"></i>
        </a>
        <!-- End::header-link|switcher-icon -->
      </div> --}}
      <!-- End::header-element -->

    </div>
    <!-- End::header-content-right -->

  </div>
  <!-- End::main-header-container -->

</header>
