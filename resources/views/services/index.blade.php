@php use App\Helpers\Helper; @endphp
@extends('layouts.app')
@section('title', $pageTitle)
@section('css')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <style>
    #form-buy label { font-weight: 600; color: #94A3B8; }
    #form-buy .server__name:hover { color: #0EA5E9; transform: scale(1.03); }
    #form-buy .panel-descr, .nova-service-descr {
      background: rgba(245, 158, 11, 0.1);
      border: 1px solid rgba(245, 158, 11, 0.3);
      color: #1E3A5F;
      padding: 12px 14px;
      border-radius: 12px;
    }
    .price_box { border-radius: 16px; }
    .price_box--bg, .price_box {
      background: linear-gradient(145deg, rgba(37,99,235,0.10), rgba(37,99,235,0.1));
      border: 1px solid rgba(37,99,235,0.20);
      padding: 1.1rem;
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(37,99,235,0.10);
      text-align: center;
    }
    .price_box .total_price, #form-buy .total_price {
      background: linear-gradient(135deg, #2563EB, #2563EB);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-size: 2rem;
      font-weight: 800;
      padding: 0 8px;
    }
    .total_price_usd { color: #94A3B8; font-size: 0.9rem; }
    .new-feeds { max-height: 480px; overflow-y: auto; }
    .nova-order-card {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      backdrop-filter: blur(12px);
    }
    .nova-order-card .card-header {
      background: transparent;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .nova-order-card .card-title {
      font-weight: 700;
      background: linear-gradient(90deg, #1E3A5F, #2563EB);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin: 0;
    }
    .select2-container--default .select2-selection--single {
      background: rgba(255,255,255,0.9) !important;
      border: 1px solid rgba(255,255,255,0.1) !important;
      border-radius: 10px !important;
      height: 42px !important;
      color: #1E3A5F !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #1E3A5F !important; line-height: 42px !important;
    }
    .select2-dropdown {
      background: #FFFFFF !important;
      border: 1px solid rgba(37,99,235,0.20) !important;
      color: #1E3A5F !important;
    }
    .select2-results__option--highlighted { background: rgba(37,99,235,0.20) !important; }
  </style>
@endsection
@section('content')
  @php
    $userDiscount = Helper::getDiscountByRank(auth()->user()->rank ?? 'bronze');
  @endphp
  <div class="alert mb-3" style="background:rgba(37,99,235,0.10);border:1px solid rgba(37,99,235,0.20);color:#1E3A5F;border-radius:12px">
    {!! Helper::getNotice('page_new_order') !!}
  </div>
  <div class="row">
    <div class="col-12 col-lg-8 col-md-8 col-sm-12">
      <div class="card custom-card nova-order-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="card-title d-flex align-items-center gap-2"><i class="fe fe-shopping-cart" style="color:#0EA5E9"></i> {{ __t('สร้างคำสั่ง') }}</h3>
          <span class="badge" style="background:rgba(14,165,233,0.12);color:#0EA5E9;border:1px solid rgba(14,165,233,0.30)">NovaSMM</span>
        </div>
        <div class="card-body">
          <div class="new__order--form">
            <form action="/api/orders/store" method="POST" id="form-buy">
              <div class="mb-3">
                <select class="js-example-placeholder-single form-control" id="search" type="search"></select>
              </div>
              <div class="mb-3">
                <label for="category_id" class="form-label">{{ __t('หมวดหมู่') }}</label>
                <select class="form-select" id="category_id" name="category_id" type="search"></select>
              </div>
              <div class="mb-3">
                <label for="service_id" class="form-label">{{ __t('บริการ') }}</label>
                <select class="form-select" id="service_id" name="service_id" type="search"></select>
              </div>
              <div id="descr" class="mb-3" style="display: none"></div>
              <div class="mb-3">
                <label for="object_id" class="form-label">{{ __t('ลิงก์') }}</label>
                <input type="text" class="form-control" id="object_id" name="object_id" placeholder="{{ __t('ป้อนลิงก์') }}">
              </div>
              <div class="mb-3 group_quantity">
                <label for="quantity" class="form-label">{{ __t('จำนวน') }}</label>
                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="{{ __t('ป้อนจำนวน') }}">
                <small class="fw-bold mt-2">Min: <span class="text-danger" id="min_buy">0</span> - Max: <span class="text-primary" id="max_buy">0</span></small>
              </div>
              <div class="mb-3 group_reaction" style="display: none"></div>
              <div class="mb-3 group_comments" style="display: none"></div>
              <div class="mb-3 group_fb_viplike" style="display: none"></div>
              <div class="mb-3 group_fb_eyeslive" style="display: none"></div>
              <div class="mb-3">
                <label for="average_time" class="form-label">{{ __t('Average time') }} <a href="javascript:void(0)" data-bs-toggle="tooltip"
                    data-bs-title="{{ __t('เวลาเฉลี่ยจาก 10 คำสั่งที่เสร็จสมบูรณ์ซึ่งมีจำนวนมากกว่า 1000.') }}"><i class="fe fe-help-circle"></i></a></label>
                <input type="text" class="form-control" id="average_time" name="average_time" disabled>
              </div>
              <div class="mb-3">
                <div class="text-center price_box">
                  <div class="price_box--bg">
                    <span class="total_price">0</span>
                  </div>
                  @if ($userDiscount > 0)
                    <div>{!! __t('คุณได้รับส่วนลด :number%', ['number' => '<span class="text-danger">' . $userDiscount . '</span>']) !!}</div>
                  @endif
                  <div class="text-danger fw-bold mt-1">{{ __t('ยอดชำระรวม') }}</div>
                </div>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary w-100">{{ __t('สร้างคำสั่ง') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4 col-md-4 col-sm-12">
      <div class="card overflow-hidden border-0 p-0 text-nowrap">
        <div class="min-vh-25 p-4" style="background: linear-gradient(135deg, #E0F2FE, #EFF6FF);">
          <div class="mb-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center rounded-pill bg-opacity-50 bg-dark p-1 text-white fw-semibold pe-3">
              <img class="rounded-circle border-white me-2" src="{{ setting('avatar_user', '/assets/images/faces/9.jpg') }}" alt="image" style="height: 2rem; width: 2rem; object-cover;">
              {{ auth()->user()->username ?? 'ยังไม่ได้เข้าสู่ระบบ' }}
            </div>
            <a href="{{ route('account.deposits.transfer') }}" class="btn btn-dark d-flex align-items-center justify-content-center rounded-circle p-0" style="height: 2.25rem; width: 2.25rem;">
              <svg class="m-auto" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" style="height: 1.5rem; width: 1.5rem;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </a>
          </div>
          <div class="d-flex align-items-center justify-content-between ">
            <p class="h5 mb-0 text-white">{{ __t('ยอดคงเหลือ') }}</p>
            <h5 class="h4 mb-0 ms-auto text-white"><span><span class="user-balance">{{ formatCurrency(auth()->user()->balance ?? 0) }}</span></span></h5>
          </div>
        </div>
        <div class="row g-2 px-4" style="margin-top: -20px">
          <div class="col-6">
            <div class="rounded bg-white p-3 shadow-sm dark-bg">
              <span class="d-flex align-items-center justify-content-between text-dark">
                {{ __t('ยอดเติมเงินรวม') }}
                <svg class="text-success" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="height: 1rem; width: 1rem;">
                  <path d="M19 15L12 9L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </span>
              <div class="btn w-100 bg-light text-dark fw-semibold border-0 py-1 mt-2">{{ formatCurrency(auth()->user()->total_deposit ?? 0) }}</div>
            </div>
          </div>
          <div class="col-6">
            <div class="rounded bg-white p-3 shadow-sm dark-bg">
              <span class="d-flex align-items-center justify-content-between text-dark">
                {{ __t('ยอดใช้จ่ายรวม') }}
                <svg class="text-danger" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="height: 1rem; width: 1rem;">
                  <path d="M19 9L12 15L5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </span>
              <div class="btn w-100 bg-light text-dark fw-semibold border-0 py-1 mt-2">{{ formatCurrency((auth()->user()->total_deposit ?? 0) - (auth()->user()->balance ?? 0)) }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center">
        <h3>{{ __t('ประกาศใหม่') }}</h3>
      </div>
      <div class="new-feeds">
        @foreach ($posts as $post)
          <div class="card">
            <div class="card-body">
              <h5>----- {{ $post->created_at }}</h5>
              <hr />
              <div class="card-content">
                {!! $post->content !!}
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Modal -->
  @if ($msgModal = Helper::getNotice('modal_dashboard'))
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ __t('ประกาศใหม่') }}</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            {!! $msgModal !!}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __t('ปิด') }}</button>
          </div>
        </div>
      </div>
    </div>
  @endif
@endsection
@section('scripts')
  <script>
    $(document).ready(function() {
      // ตรวจสอบว่ามีการแสดงโมดอลภายใน 30 นาทีที่แล้วหรือไม่
      var lastShownTime = localStorage.getItem('lastShownTime');
      var currentTime = new Date().getTime();
      var timeDiff = currentTime - lastShownTime;

      if (lastShownTime === null || timeDiff > 30 * 60 * 1000) {
        // หากยังไม่แสดงภายใน 30 นาที ให้แสดงโมดอล
        $('#exampleModal').modal('show');

        // บันทึกเวลาที่แสดงโมดอล
        localStorage.setItem('lastShownTime', currentTime);
      }
    });
  </script>

  <script>
    window._discount = {{ $userDiscount }};
    window._services = @json($services);
    window._categories = @json($categories);
    window._api_get_form = "/api/tools/get-form";
    window._api_orders_store = "/api/orders/store";
    window.SUM_PRICE_FNC = null
  </script>
  <!-- Select2 Cdn -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    const formatNumber = (num) => {
      return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    }

    $(document).ready(function() {
      $(".js-example-placeholder-single").select2({
        placeholder: "{{ __t('ค้นหาบริการ') }}",
        allowClear: true,
        dir: "ltr",
        data: [{
          id: '',
          text: ''
        }, ...window._services.map((service) => {
          return {
            id: service.id,
            text: service.display_name
          }
        })],
        selectionCssClass: 'select2-selection--single',
      });

      $("#category_id").select2({
        placeholder: "{{ __t('เลือกหมวดหมู่') }}",
        allowClear: true,
        dir: "ltr",
        data: window._categories.map((category) => {
          return {
            id: category.id,
            text: `ID ${category.id} - ${category.name}`,
            imgSrc: category.image // สมมติว่าคุณมีเส้นทางรูปภาพในคุณสมบัติ `image` ของวัตถุ `category`
          }
        }),
        templateResult: formatCategoryOption,
        templateSelection: formatCategorySelection
      });

      function formatCategoryOption(option) {
        if (!option.id) {
          return option.text;
        }

        var imgSrc = option.imgSrc;
        if (imgSrc) {
          var $option = $(
            '<span><img src="' + imgSrc + '" style="width: 30px; margin-right: 2px; margin-bottom: 2px; border-radius: 0px;" /> ' + option.text + '</span>'
          );
          return $option;
        }

        return option.text;
      }

      function formatCategorySelection(option) {
        if (!option.id) {
          return option.text;
        }

        var imgSrc = option.imgSrc;
        if (imgSrc) {
          var $selection = $(
            '<span><img src="' + imgSrc + '" style="width: 30px; margin-right: 2px; margin-bottom: 2px; border-radius: 0px;" /> ' + option.text + '</span>'
          );
          return $selection;
        }

        return option.text;
      }

      $("#service_id").select2({
        placeholder: "{{ __t('เลือกบริการ') }}",
        allowClear: true,
        dir: "ltr",
      });

      $("#search").change(function(e) {
        const service = window._services.find((service) => service.id == e.target.value);

        if (service === undefined) {
          return;
        }

        $("#category_id").val(service.category_id).trigger('change');
      })

      $("#category_id").change(function(e) {
        const services = window._services.filter((service) => service.category_id == e.target.value);

        $("#service_id").html(services.map((service) => {
          return `<option value="${service.id}">${service.display_name}</option>`
        }));

        const searchId = $("#search").val()

        if (searchId === '') {
          $("#service_id").val(services[0]?.id || '').trigger('change');
        } else {
          $("#service_id").val(searchId).trigger('change');
        }
      })

      $("#service_id").change(function(e) {
        let service = window._services.find((service) => service.id == e.target.value);

        if (service === undefined) {
          return;
        }

        const searchId = $("#search").val()
        if (searchId !== '') {
          $("#search").val('').trigger('change')
        }

        if ($("#quantity").val() <= service.min_buy) {
          $("#quantity").val(service.min_buy).trigger('input')
        } else {
          $("#quantity").trigger('input')
        }


        $("#descr").html('').hide();
        if (service.descr) {
          $("#descr").html(`<label for="descr" class="form-label">{{ __t('คำอธิบายบริการ') }}</label><div class="panel-descr p-2" style="border-radius: 8px">${service.descr}</div>`).show();
        }
        $("#min_buy").text(formatNumber(service.min_buy));
        $("#max_buy").text(formatNumber(service.max_buy));
        $("#average_time").val(service.average_time);

        // update field custom comments
        if (service.type === 'custom_comments' || service.type === 'custom_comment') {
          axios
            .get(_api_get_form + '/comments')
            .then(({
              data: res
            }) => {
              $('.group_comments').show().html(res)
              sumPrice()
            })
            .finally(() => {
              sumPrice()
            })
        } else {
          $('.group_comments').hide().html('')

          $("#quantity").attr("readonly", false);
        }
      })

      const getService = () => {
        const serviceId = $('#service_id').val()

        return window._services.find((service) => service.id == serviceId)
      }

      const sumPrice = () => {
        const service = getService()

        if (service === undefined) {
          return 0
        }

        const quantity = parseInt($('#quantity').val()),
          price_per = parseFloat(service.price_per)

        if (isNaN(quantity) || isNaN(price_per) || quantity <= 0) {
          return 0
        }

        let total_payment = parseFloat(quantity * price_per)

        if (service.form_type === 'fb_viplike') {
          const num_post = parseInt($('#num_post').val()),
            duration = parseInt($('#duration').val())

          total_payment *= num_post * duration
        } else if (service.form_type === 'fb_eyeslive') {
          const duration = parseInt($('#duration').val())

          total_payment *= duration
        }

        if (service.type === 'custom_comments' || service.type === 'custom_comment') {
          const comment_count = parseInt($('.comment_count').text())

          total_payment = comment_count * price_per
        }

        if (window._discount > 0) {
          // total_payment = total_payment - (total_payment * window._discount / 100)
        }

        $('.total_price').html($formatCurrency(total_payment))

        return total_payment
      }

      SUM_PRICE_FNC = sumPrice

      $('#quantity').on('input', sumPrice)
      $('#num_post').on('input', sumPrice)
      $('#duration').on('input', sumPrice)


      //
      $('#form-buy').submit(async (e) => {
        e.preventDefault()

        const confirm = await Swal.fire({
          title: '{{ __t('คุณแน่ใจหรือไม่?') }}',
          text: '{{ __t('คุณได้ตรวจสอบข้อมูลเรียบร้อยแล้วหรือไม่?') }}',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '{{ __t('ตกลง') }}',
          cancelButtonText: '{{ __t('ยกเลิก') }}',
        })

        if (confirm.isConfirmed === false) return

        const payload = $formDataToPayload(new FormData(e.target)),
          button = e.target.querySelector('button[type=submit]'),
          service = getService()

        if (payload.object_id === '') {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ __t('ช่องลิงก์ต้องไม่เว้นว่าง!') }}',
          })
          return
        }
        if (service === undefined) {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ __t('ไม่พบบริการนี้!') }}',
          })
          return
        }
        // if (payload.quantity < service.min_buy || payload.quantity > service.max_buy) {
        //   Swal.fire({
        //     icon: 'error',
        //     title: 'Oops...',
        //     text: '{{ __t('จำนวนที่ซื้อต้องอยู่ระหว่าง ') }} ' + service.min_buy + ' {{ __t(' ถึง ') }}' + service.max_buy,
        //   })
        //   return
        // }

        if (service.min_buy > 0 && payload.quantity < parseInt(service.min_buy)) {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ __t('จำนวนที่ซื้อต้องน้อยกว่า ') }}' + service.min_buy,
          })
          return
        }

        if (service.max_buy > 0 && payload.quantity > parseInt(service.max_buy)) {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ __t('จำนวนที่ซื้อต้องน้อยกว่า ') }}' + service.max_buy,
          })
          return
        }


        return handleSubmit(payload, button)
      })

      const handleSubmit = async (payload, button) => {
        Swal.fire({
          icon: 'warning',
          title: '{{ __t('กำลังดำเนินการ...') }}',
          html: '{{ __t('กรุณาอย่าปิดหน้านี้เพื่อไม่ให้สูญเสียเงิน!') }}',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            Swal.showLoading()
          },
        })

        $setLoading(button)

        try {
          const {
            data: result
          } = await axios.post(_api_orders_store, payload)

          // ดูคำสั่ง, ซื้อต่อ
          const {
            isConfirmed
          } = await Swal.fire({
            title: 'สำเร็จ!',
            text: result.message,
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'ดูคำสั่ง',
            cancelButtonText: 'ซื้อต่อ',
          })

          if (isConfirmed) {
            window.location.href = '{{ route('account.orders') }}?order_id=' + result.data.order_detail.id
          } else {
            // window.location.reload()
            $("#object_id").val('')
            $("#quantity").val(0)

          }
        } catch (error) {
          if (error?.response?.status === 401) {
            return Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'คุณต้องเข้าสู่ระบบเพื่อซื้อบริการ!',
              showCancelButton: true,
              confirmButtonText: 'เข้าสู่ระบบ',
              cancelButtonText: 'ยกเลิก',
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = '/login'
              }
            })
          }
          Swal.fire('Oops...', $catchMessage(error), 'error')
        } finally {
          fetchUser()
          $removeLoading(button)

        }
      }

      setTimeout(() => {
        // trigger change
        $("#category_id").val(window._categories[0]?.id || '').trigger('change');
      }, 500)
    })
  </script>
@endsection
