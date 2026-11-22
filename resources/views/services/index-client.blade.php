@php use App\Helpers\Helper; @endphp
@extends('layouts.app')
@section('title', $pageTitle)
@section('css')
  <style>
    .new-feeds {
      max-height: 480px;
      overflow-y: auto;
    }
    [v-cloak] {
      min-height: 420px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .nova-balance-card {
      background: linear-gradient(135deg, rgba(139, 92, 246, 0.25) 0%, rgba(37, 99, 235, 0.2) 60%, rgba(8, 11, 24, 0.9) 100%);
      border: 1px solid rgba(139, 92, 246, 0.3);
      border-radius: 16px;
      overflow: hidden;
    }
    .nova-balance-header {
      background: linear-gradient(to right, rgba(67, 97, 238, 0.4), rgba(22, 15, 107, 0.5));
      padding: 1.25rem 1.25rem 1.5rem;
    }
    .nova-mini-stat {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 0.85rem;
      transition: all 0.2s ease;
    }
    .nova-mini-stat:hover {
      background: rgba(139, 92, 246, 0.1);
      border-color: rgba(139, 92, 246, 0.25);
    }
    .nova-order-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      backdrop-filter: blur(12px);
    }
    .nova-order-card .card-header {
      background: transparent;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.25rem;
    }
    .nova-order-card .card-title {
      font-weight: 700;
      background: linear-gradient(90deg, #F8FAFC, #A78BFA);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin: 0;
    }
  </style>
@endsection
@section('content')
  @php
    $userDiscount = Helper::getDiscountByRank(auth()->user()->rank ?? 'bronze');
  @endphp

  @if(Helper::getNotice('page_new_order'))
  <div class="alert mb-3" style="background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.3);color:#E2E8F0;border-radius:12px">
    {!! Helper::getNotice('page_new_order') !!}
  </div>
  @endif

  <div class="row g-3">
    {{-- Order Form Column --}}
    <div class="col-12 col-lg-8">
      <div class="card nova-order-card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="card-title d-flex align-items-center gap-2">
            <i class="fe fe-shopping-cart" style="color:#A78BFA"></i>
            {{ __t('Tạo đơn hàng') }}
          </h3>
          <span class="badge" style="background:rgba(34,211,238,0.15);color:#22D3EE;border:1px solid rgba(34,211,238,0.3)">
            NovaSMM
          </span>
        </div>
        <div class="card-body" id="app" v-cloak>
          <baocms-form-buy />
        </div>
      </div>
    </div>

    {{-- Sidebar: Balance + Announcements --}}
    <div class="col-12 col-lg-4">
      <div class="nova-balance-card mb-3">
        <div class="nova-balance-header">
          <div class="mb-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center rounded-pill bg-opacity-50 bg-dark p-1 text-white fw-semibold pe-3">
              <img class="rounded-circle border-white me-2" src="{{ setting('avatar_user', '/assets/images/faces/9.jpg') }}" alt="avatar" style="height: 2rem; width: 2rem; object-fit: cover;">
              {{ auth()->user()->username ?? 'Guest' }}
            </div>
            <a href="{{ route('account.deposits.transfer') }}" class="btn btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
               style="height: 2.25rem; width: 2.25rem; background:rgba(139,92,246,0.4);border:1px solid rgba(139,92,246,0.5);color:#fff">
              <i class="fe fe-plus"></i>
            </a>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <p class="mb-0 text-white-50" style="font-size:0.85rem">{{ __t('Số dư') }}</p>
            <h4 class="mb-0 text-white fw-bold"><span class="user-balance">{{ formatCurrency(auth()->user()->balance ?? 0) }}</span></h4>
          </div>
        </div>
        <div class="row g-2 px-3 pb-3" style="margin-top: -12px">
          <div class="col-6">
            <div class="nova-mini-stat">
              <span class="d-flex align-items-center justify-content-between" style="color:#94A3B8;font-size:0.8rem">
                {{ __t('Tổng nạp') }}
                <i class="fe fe-trending-up text-success"></i>
              </span>
              <div class="fw-semibold mt-1" style="color:#F8FAFC">{{ formatCurrency(auth()->user()->total_deposit ?? 0) }}</div>
            </div>
          </div>
          <div class="col-6">
            <div class="nova-mini-stat">
              <span class="d-flex align-items-center justify-content-between" style="color:#94A3B8;font-size:0.8rem">
                {{ __t('Tổng tiêu') }}
                <i class="fe fe-trending-down text-danger"></i>
              </span>
              <div class="fw-semibold mt-1" style="color:#F8FAFC">{{ formatCurrency((auth()->user()->total_deposit ?? 0) - (auth()->user()->balance ?? 0)) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center mb-2">
        <h5 class="mb-0" style="color:#94A3B8;font-weight:600;font-size:0.9rem">{{ __t('Thông báo mới') }}</h5>
      </div>
      <div class="new-feeds">
        @forelse ($posts as $post)
          <div class="card mb-2" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px">
            <div class="card-body py-3">
              <div class="text-muted small mb-2" style="color:#64748B !important">{{ $post->created_at }}</div>
              <div class="card-content" style="color:#E2E8F0;font-size:0.9rem">
                {!! $post->content !!}
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4" style="color:#64748B">{{ __t('Chưa có thông báo') }}</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Modal --}}
  @if ($msgModal = Helper::getNotice('modal_dashboard'))
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ __t('Thông báo mới') }}</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            {!! $msgModal !!}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __t('Đóng') }}</button>
          </div>
        </div>
      </div>
    </div>
  @endif
@endsection
@section('scripts')
  @vite('resources/js/modules/services/form/index.js')

  <script>
    $(document).ready(function() {
      var lastShownTime = localStorage.getItem('lastShownTime');
      var currentTime = new Date().getTime();
      var timeDiff = currentTime - lastShownTime;

      if (lastShownTime === null || timeDiff > 30 * 60 * 1000) {
        $('#exampleModal').modal('show');
        localStorage.setItem('lastShownTime', currentTime);
      }
    });
  </script>
@endsection
