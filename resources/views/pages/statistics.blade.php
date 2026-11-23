@php use App\Helpers\Helper; @endphp
@extends('layouts.app')
@section('title', $pageTitle)
@section('css')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

  <style>
    #form-buy .server__name:hover {
      color: #10ac84;
      transform: scale(1.05);
      margin-left: 5px;
    }

    #form-buy label {
      font-weight: bold;
    }

    #form-buy .total_price,
    .total_price_usd {
      font-weight: bold;
      font-size: 2rem;
      color: red;
    }

    #form-buy .panel-descr {
      background-color: #FFE0B5;
      color: #000;
      padding: 10px;
      border-radius: 8px;
    }
  </style>
@endsection
@section('content')

  {{-- NovaSMM Welcome Banner --}}
  <div class="nova-welcome-banner mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index:1">
      <div>
        <div class="d-flex align-items-center gap-2 mb-2">
          <img src="/assets/images/brand-logos/nova-icon.svg" alt="NovaSMM" width="36" height="36">
          <span class="badge" style="background:rgba(37,99,235,0.10);color:#0EA5E9;border:1px solid rgba(37,99,235,0.20)">NovaSMM</span>
        </div>
        <h2 class="banner-title mb-1">{{ __t('ยินดีต้อนรับสู่') }} NovaSMM</h2>
        <p class="banner-subtitle mb-0">{{ __t('บริการครบ จบในที่เดียว เร็ว ปลอดภัย มั่นใจได้') }} <strong style="color:#0EA5E9">100%</strong></p>
      </div>
      <div class="d-none d-md-flex gap-2">
        <a href="{{ route('home') }}" class="btn btn-primary btn-nova">
          <i class="fe fe-shopping-cart me-1"></i> {{ __t('สั่งซื้อบริการ') }}
        </a>
        <a href="{{ route('account.deposits.card') }}" class="btn btn-outline-primary">
          <i class="fe fe-credit-card me-1"></i> {{ __t('เติมเงิน') }}
        </a>
      </div>
    </div>
  </div>

  {{-- Stat Cards - Real Data --}}
  <div class="row g-3 mb-4 total-sales-card-section">
    <div class="col-6 col-xl-3">
      <div class="nova-stat-card h-100">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="stat-label mb-1">{{ __t('Số dư hiện tại') }}</div>
            <div class="stat-value">{{ formatCurrency(auth()->user()->balance) }}</div>
          </div>
          <div class="stat-icon">
            <i class="fe fe-dollar-sign"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="nova-stat-card h-100">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="stat-label mb-1">{{ __t('Tổng tiền nạp tháng') }}</div>
            <div class="stat-value">{{ formatCurrency($totalDepositInMonth) }}</div>
            <span class="stat-change {{ $percentDeposit < 0 ? 'down' : 'up' }}">
              {{ round($percentDeposit, 1) }}% {{ __t('vs tháng trước') }}
            </span>
          </div>
          <div class="stat-icon" style="background:rgba(16,185,129,0.15);color:#10B981">
            <i class="fe fe-trending-up"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="nova-stat-card h-100">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="stat-label mb-1">{{ __t('Tổng đơn của bạn') }}</div>
            <div class="stat-value">{{ number_format($totalOrderInMonth) }}</div>
            <span class="stat-change {{ $percentOrder < 0 ? 'down' : 'up' }}">
              {{ round($percentOrder, 1) }}% {{ __t('vs tháng trước') }}
            </span>
          </div>
          <div class="stat-icon" style="background:rgba(37,99,235,0.15);color:#3B82F6">
            <i class="fe fe-shopping-cart"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="nova-stat-card h-100">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="stat-label mb-1">{{ __t('Trạng thái hệ thống') }}</div>
            <div class="stat-value" style="font-size:1.35rem;color:#10B981">
              <i class="fas fa-circle me-1" style="font-size:0.6rem"></i> Online
            </div>
            <span class="stat-change up">99.8% Uptime</span>
          </div>
          <div class="stat-icon" style="background:rgba(14,165,233,0.12);color:#0EA5E9">
            <i class="fe fe-activity"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
      <div class="card custom-card">
        <div class="card-header">
          <div class="card-title">{{ __t('Thống kê đơn hàng') }}</div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div id="orders-chart"></div>
            </div>
            <div class="col-md-6">
              <div id="pie-basic"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12">
        <div class="card custom-card">
          <div class="card-header">
            <h3 class="card-title">{{ __t('Dữ liệu đơn hàng') }}</h3>
          </div>
          <div class="card-body">
            <div id="column-basic" width="1112" class="overflow-hidden"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12">
      <div class="card custom-card">
        <div class="card-header">
          <h3 class="card-title">{{ __t('Phân tích dòng tiền') }}</h3>
        </div>
        <div class="card-body">
          <div id="totaltransactions" width="1112" class="overflow-hidden"></div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    /* basic line chart */
    var options = {
      series: @json($chartOrders),
      chart: {
        height: 320,
        type: 'line',
        zoom: {
          enabled: false
        }
      },
      colors: @json($chartOrdersColors),
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'straight',
        width: 3,
      },
      grid: {
        borderColor: '#f2f5f7',
      },
      title: {
        text: '{{ __t('Đơn hàng gần đây') }}',
        align: 'left',
        style: {
          fontSize: '13px',
          fontWeight: 'bold',
          color: '#8c9097'
        },
      },
      xaxis: {
        categories: @json($chartCategories),
        labels: {
          show: true,
          style: {
            colors: "#8c9097",
            fontSize: '11px',
            fontWeight: 600,
            cssClass: 'apexcharts-xaxis-label',
          },
        }
      },
      yaxis: {
        labels: {
          show: true,
          style: {
            colors: "#8c9097",
            fontSize: '11px',
            fontWeight: 600,
            cssClass: 'apexcharts-yaxis-label',
          },
        }
      }
    };
    var chart = new ApexCharts(document.querySelector("#orders-chart"), options);
    chart.render();
    /* basic pie chart */
    var options1 = {
      series: @json($chartPieData),
      chart: {
        height: 300,
        type: 'pie',
      },
      colors: @json($chartPieColors),
      labels: @json($chartPieLabels),
      legend: {
        position: "bottom"
      },
      dataLabels: {
        dropShadow: {
          enabled: false
        }
      },
    };
    var chart = new ApexCharts(document.querySelector("#pie-basic"), options1);
    chart.render();

    //
    /* basic column chart */

    var options = {
      series: [{
        name: '{{ __t('Đơn hàng') }}',
        data: @json($chartCount)
      }],
      chart: {
        type: 'bar',
        height: 320
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '80%',
          endingShape: 'rounded'
        },
      },
      grid: {
        borderColor: '#f2f5f7',
      },
      dataLabels: {
        enabled: false
      },
      colors: ["#2563EB", "#fb6b25", "#f5b849"],
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      xaxis: {
        categories: @json($chartCategories),
        labels: {
          show: true,
          style: {
            colors: "#8c9097",
            fontSize: '11px',
            fontWeight: 600,
            cssClass: 'apexcharts-xaxis-label',
          },
        }
      },
      yaxis: {
        title: {
          text: '{{ __t('đơn') }}',
          style: {
            color: "#8c9097",
          }
        },
        labels: {
          show: true,
          style: {
            colors: "#8c9097",
            fontSize: '11px',
            fontWeight: 600,
            cssClass: 'apexcharts-xaxis-label',
          },
        }
      },
      fill: {
        opacity: 1
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return val + " {{ __t('đơn') }}"
          }
        }
      }
    };
    var chart = new ApexCharts(document.querySelector("#column-basic"), options);
    chart.render();
  </script>

  <script>
    var options = {
      series: [{
        name: '{{ __t('Tiền nạp') }}',
        data: @json($chartDeposit)
      }, {
        name: '{{ __t('Tiền tiêu') }}',
        data: @json($chartSpent)
      }],
      chart: {
        toolbar: {
          show: false
        },
        type: 'line',
        height: 320,
        dropShadow: {
          enabled: true,
          opacity: 0.1,
        },
      },
      grid: {
        borderColor: '#f1f1f1',
        strokeDashArray: 3
      },

      labels: @json($chartCategories),
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: "smooth",
        width: [3, 3, 0],
        dashArray: [0, 4],
        lineCap: "round"
      },
      legend: {
        show: true,
        position: 'top',
      },
      xaxis: {
        axisBorder: {
          color: '#e9e9e9',
        },
      },
      plotOptions: {
        bar: {
          columnWidth: "20%",
          borderRadius: 2
        }
      },
      grid: {
        show: true,
        padding: {
          right: 0,
          left: 0
        },
      },
      yaxis: {
        labels: {
          formatter: function(val) {
            return $formatCurrency(val)
          }
        }
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return $formatCurrency(val)
          }
        }
      },

      colors: ["rgba(37,99,235,1)", "rgba(249, 148, 51, 1)", 'rgba(119, 119, 142, 0.05)'],
    };
    document.querySelector("#totaltransactions").innerHTML = ""
    var chart2 = new ApexCharts(document.querySelector("#totaltransactions"), options);
    chart2.render();

    function totaltransactions() {
      chart2.updateOptions({
        colors: ["rgba(" + myVarVal + ", 1)", '#0EA5E9'],
      })
    }
  </script>
@endsection
