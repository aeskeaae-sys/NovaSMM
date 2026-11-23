@if ($group->description)
  <div class="alert alert-info mb-3" role="alert">{!! $group->description !!}</div>
@endif

<div class="gap-3" style="margin-left: 5px">
  <label for="server_id">{{ __t('เลือกเซิร์ฟเวอร์') }}</label>
  @foreach ($servers as $server)
    @php
      $disabled = false;
      if ($server['status'] !== true) {
          // continue;
          $disabled = true;
      }
    @endphp

    <div class="form-check form-check-success">
      <input class="form-check-input" type="radio" name="server_id" id="dsv{{ $server['id'] }}" value="{{ $server['id'] }}" data-sid="{{ $server['sid'] }}" data-pid="{{ $server['pid'] }}"
        @if ($disabled) disabled @endif>
      <label class="form-check-label" for="dsv{{ $server['id'] }}" style="font-weight: bold">
        <span class="text-primary fw-bold">{{ __t('MC') }} {{ $server['id'] }}</span>
        - <span class="fw-bold">{{ __t($server['name']) }}</span>
        - <span class="text-danger fw-bold me-1">{{ $server['price'] }}₫</span>
        @if ($server['status'])
          <span class="text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="ใช้งาน"><i class="fa-solid fa-circle-check"></i></span>
        @else
          <span class="badge badge-xl bg-danger">{{ __t('กำลังบำรุงรักษา') }}</span>
        @endif
      </label>
    </div>
  @endforeach
  @if (count($servers) === 0)
    <div class="alert alert-primary text-center" role="alert" data-key="c-no-server">
      {{ __t('ไม่มีเซิร์ฟเวอร์ที่ใช้งานอยู่!') }}
    </div>
  @endif
</div>
<div class="server_note"></div>

<script>
  var LIST_SERVERS = {!! json_encode($servers) !!};
  var SUM_PRICE_FNC = null;

  $(document).ready(function() {
    'use strict'

    // tooltip actie
    $('[data-bs-toggle="tooltip"]').tooltip()

    $('[name=server_id]').change(() => {
      const server = getServer()

      if (server === undefined) {
        return Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'ไม่พบเซิร์ฟเวอร์นี้!',
        })
      }

      const options = server.options

      if (server.info) {
        $('.server_note').html(
          `<label class="form-label">ข้อมูล : </label>
              <div class="alert text-white" role="alert" style="background-color: #864AF9">${server.info}</div>`
        )
      } else {
        $('.server_note').html('')
      }

      const reactionLoaded = $('#form_reaction_loaded').val()
      if (options.reaction && !reactionLoaded) {
        axios
          .get(API_GET_FORM + '/reaction')
          .then(({
            data: res
          }) => {
            $('.group_reaction').show().html(res)
          })
          .finally(() => {
            sumPrice()
          })
      } else if (!options.reaction) {
        $('.group_reaction').hide().html('')
      }

      const commentLoaded = $('#form_comment_loaded').val()
      if (options.comments && !commentLoaded) {
        axios
          .get(API_GET_FORM + '/comments')
          .then(({
            data: res
          }) => {
            $('.group_comments').show().html(res)
            sumPrice()
          })
          .finally(() => {
            sumPrice()
          })
      } else if (!options.comments) {
        $('.group_comments').hide().html('')
      }

      const vipLikeLoaded = $('#form_viplike_loaded').val()
      if (options.form_type === 'fb_viplike' && !vipLikeLoaded) {
        axios
          .get(API_GET_FORM + '/fb_viplike')
          .then(({
            data: res
          }) => {
            $('.group_fb_viplike').show().html(res)
          })
          .finally(() => {
            sumPrice()
          })
      } else if (options.form_type !== 'fb_viplike') {
        $('.group_fb_viplike').hide().html('')
      }

      // fb_eyeslive
      const eyesLiveLoaded = $('#form_eyeslive_loaded').val()
      if (options.form_type === 'fb_eyeslive' && !eyesLiveLoaded) {
        axios
          .get(API_GET_FORM + '/fb_eyeslive')
          .then(({
            data: res
          }) => {
            $('.group_fb_eyeslive').show().html(res)
          })
          .finally(() => {
            sumPrice()
          })
      } else if (options.form_type !== 'fb_eyeslive') {
        $('.group_fb_eyeslive').hide().html('')
      }

      sumPrice()
    })

    const getServer = () => {
      const server_id = $('[name=server_id]:checked').val()
      // const server_id = $('[name=server_id]').val()

      return LIST_SERVERS.find((item) => item.id == server_id)
    }

    const getFormJson = () => {
      const form = $('#form-buy')[0]

      const payload = $formDataToPayload(new FormData(form))

      return payload
    }

    // watch change quantity
    $('#quantity').keyup(() => {
      sumPrice()
    })

    const sumPrice = () => {
      const server = getServer()

      if (server === undefined) {
        return 0
      }
      const quantity = parseInt($('#quantity').val()),
        price_per = parseFloat(server.price)

      if (isNaN(quantity) || isNaN(price_per) || quantity <= 0) {
        return 0
      }

      let total_payment = quantity * price_per

      if (server.options.form_type === 'fb_viplike') {
        const num_post = parseInt($('#num_post').val()),
          duration = parseInt($('#duration').val())

        total_payment *= num_post * duration
      } else if (server.options.form_type === 'fb_eyeslive') {
        const duration = parseInt($('#duration').val())

        total_payment *= duration
      }

      if (server.options.charge_by === 'comment_count') {
        const comment_count = parseInt($('.comment_count').text())

        total_payment = comment_count * price_per
      }

      $('.total_price').html($formatCurrency(total_payment))
      $('.total_price_usd').html(
        '~ ' + $formatCurrency(total_payment / 24000, 'USD')
      )

      return total_payment
    }

    // init to global
    SUM_PRICE_FNC = sumPrice

    //

    $('#form-buy').submit(async (e) => {
      e.preventDefault()

      const confirm = await Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: 'คุณได้ตรวจสอบข้อมูลเรียบร้อยแล้วหรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก',
      })

      if (confirm.isConfirmed === false) return

      Swal.fire({
        icon: 'warning',
        title: 'กำลังดำเนินการ...',
        html: 'กรุณาอย่าปิดหน้านี้เพื่อไม่ให้สูญเสียเงิน!',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading()
        },
      })

      return handleSubmit(e)
    })

    const handleSubmit = async (e) => {
      const payload = $formDataToPayload(new FormData(e.target)),
        button = e.target.querySelector('button[type=submit]'),
        server = getServer()

      if (payload.object_id === '') {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'กรุณาป้อน ID หรือลิงก์เพื่อดำเนินการต่อ!',
        })
        return
      }
      if (server === undefined) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'ไม่พบเซิร์ฟเวอร์นี้!',
        })
        return
      }
      if (payload.quantity <= 0) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'จำนวนที่ซื้อต้องมากกว่า 0!',
        })
        return
      }

      $setLoading(button)

      try {
        const {
          data: result
        } = await axios.post(API_ORDERS, payload)

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
          window.location.href = '{{ route('account.orders.index') }}'
        } else {
          // window.location.reload()

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
        $removeLoading(button)
      }
    }
  })
</script>
