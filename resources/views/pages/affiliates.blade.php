@php use App\Helpers\Helper; @endphp
@extends('layouts.app')
@section('title', __t('การตลาดแบบพันธมิตร'))
@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
      <div class="card bg-primary img-card box-primary-shadow">
        <div class="card-body">
          <div class="d-flex">
            <div class="text-white">
              <h2 class="mb-0 number-font text-fixed-white">{{ formatCurrency(Auth::user()->balance_1 ?? 0) }}</h2>
              <p class="text-white mb-0 text-fixed-white">{{ __t('ยอดคงเหลือค่าคอมมิชชัน') }}</p>
            </div>
            <div class="ms-auto"> <i class="fas fa-dollar-sign text-fixed-white fs-30 me-2 mt-2"></i> </div>
          </div>
        </div>
      </div>
    </div><!-- COL END -->
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
      <div class="card bg-secondary img-card box-secondary-shadow">
        <div class="card-body">
          <div class="d-flex">
            <div class="text-white">
              <h2 class="mb-0 number-font text-fixed-white">{{ formatCurrency(Auth::user()->total_withdraw ?? 0) }}</h2>
              <p class="text-white mb-0 text-fixed-white">{{ __t('ค่าคอมมิชชันที่ถอนแล้ว') }}</p>
            </div>
            <div class="ms-auto"> <i class="fas fa-wallet text-fixed-white fs-30 me-2 mt-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div><!-- COL END -->
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
      <div class="card  bg-success img-card box-success-shadow">
        <div class="card-body">
          <div class="d-flex">
            <div class="text-white">
              <h2 class="mb-0 number-font text-fixed-white">{{ Auth::user()->referrals()->count() ?? 0 }}</h2>
              <p class="text-white mb-0 text-fixed-white">{{ __t('แนะนำแล้ว') }}</p>
            </div>
            <div class="ms-auto">
              <i class="fas fa-user-plus fs-30 me-2 mt-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div><!-- COL END -->
  </div>
  <div class="row">

    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
      <div class="card bg-info img-card box-info-shadow">
        <div class="card-body">
          <div class="d-flex">
            <div class="text-white">
              <h2 class="mb-0 number-font text-fixed-white">{{ number_format($affiliate->clicks) }}</h2>
              <p class="text-white mb-0 text-fixed-white">{{ __t('จำนวนคลิก') }}</p>
            </div>
            <div class="ms-auto"> <i class="fas fa-share text-fixed-white fs-30 me-2 mt-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div><!-- COL END -->
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
      <div class="card bg-danger img-card box-danger-shadow">
        <div class="card-body">
          <div class="d-flex">
            <div class="text-white">
              <h2 class="mb-0 number-font text-fixed-white">{{ number_format($affiliate->signups) }}</h2>
              <p class="text-white mb-0 text-fixed-white">{{ __t('จำนวนการลงทะเบียน') }}</p>
            </div>
            <div class="ms-auto"> <i class="fas fa-users text-fixed-white fs-30 me-2 mt-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div><!-- COL END -->
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
      <div class="card bg-warning img-card box-warning-shadow">
        <div class="card-body">
          <div class="d-flex">
            <div class="text-white">
              <h2 class="mb-0 number-font text-fixed-white">{{ formatCurrency($affiliate->users->sum('total_deposit')) }}</h2>
              <p class="text-white mb-0 text-fixed-white">{{ __t('ยอดเติมเงินรวม') }}</p>
            </div>
            <div class="ms-auto"> <i class="fas fa-credit-card text-fixed-white fs-30 me-2 mt-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div><!-- COL END -->
  </div>

  <div class="card custom-card">
    <div class="card-header">
      <h3 class="card-title">{{ __t('คู่มือการใช้งาน') }}</h3>
    </div>
    <div class="card-body">
      {!! Helper::getNotice('page_affiliate') !!}
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card custom-card">
        <div class="card-header">
          <h3 class="card-title">{{ __t('ข้อมูลการแนะนำ') }}</h3>
        </div>
        <div class="card-body">
          <div class="text-danger fw-bold mb-3">{{ __t('คุณจะได้รับค่าคอมมิชชัน :percent เมื่อผู้ใช้ที่คุณแนะนำเติมเงินเข้าบัญชี', ['percent' => setting('comm_percent', 10) . '%']) }}.</div>
          <div>
            <label for="referral_code" class="form-label">{{ __t('ลิงก์แนะนำ') }}:</label>
            <div class="input-group">
              <input type="text" id="referral_code" name="referral_code" class="form-control form-control-sm" value="{{ route('ref', ['ref' => $affiliate->code]) }}" style="border-radius: 5px 0 0 5px" readonly>
              <button class="btn btn-primary copy" data-clipboard-target="#referral_code" style="border-radius: 0 5px 5px 0" type="button"><i class="fas fa-copy"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card custom-card">
        <div class="card-header">
          <h3 class="card-title">{{ __t('ขอถอนเงิน') }}</h3>
        </div>
        <div class="card-body">
          <form action="/api/users/affiliates/withdraw" id="form-withdraw" method="POST">
            <div class="mb-5">
              <div class="text-danger fw-bold">
                <i>
                  {!! __t('จำนวนเงินที่ถอนได้: ตั้งแต่ :from ถึง :max', [
                      'from' => '<span class="text-primary">' . formatCurrency($config['min_withdraw'] ?? 0) . '</span>',
                      'max' => '<span class="text-success">' . formatCurrency($config['max_withdraw'] ?? 0) . '</span>',
                  ]) !!}
                </i>
              </div>
            </div>
            <div class="mb-4">
              <label for="amount" class="form-label">{{ __t('จำนวนเงินที่ถอน') }}</label>
              <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount', $config['min_withdraw'] ?? 0) }}" required>
            </div>
            <div class="mb-4">
              <label for="withdraw_to" class="form-label">{{ __t('ถอนไปยัง') }}</label>
              <select name="withdraw_to" id="withdraw_to" class="form-control">
                <option value="bank">{{ __t('ธนาคาร') }}</option>
                <option value="wallet" selected>{{ __t('กระเป๋าเงินในบัญชี') }}</option>
              </select>
            </div>
            <div class="mb-4 group_banking">
              <label for="bank_name" class="form-label">{{ __t('ธนาคาร') }}</label>
              <select name="bank_name" id="bank_name" class="form-control">
                <option value="">{{ __t('เลือกธนาคารสำหรับถอน') }}</option>
                @foreach (Helper::getListBank() as $bank)
                  <option value="{{ $bank['code'] }}">ธนาคาร {{ $bank['code'] }} - {{ $bank['shortName'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="row mb-4 group_banking">
              <div class="col-md-6">
                <label for="account_number" class="form-label">{{ __t('หมายเลขบัญชี') }}</label>
                <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number') }}" placeholder="{{ __t('ป้อนหมายเลขบัญชี') }}">
              </div>
              <div class="col-md-6">
                <label for="account_name" class="form-label">{{ __t('ชื่อเจ้าของบัญชี') }}</label>
                <input type="text" class="form-control" id="account_name" name="account_name" value="{{ old('account_name') }}" placeholder="{{ __t('ป้อนชื่อเจ้าของบัญชี') }}">
              </div>
            </div>
            <div class="mb-4 group_banking">
              <label for="user_note" class="form-label">{{ __t('หมายเหตุ') }}</label>
              <textarea name="user_note" id="user_note" class="form-control" rows="3" placeholder="{{ __t('ป้อนหมายเหตุสำหรับผู้ดูแลระบบหากมี') }}">{{ old('user_note') }}</textarea>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-primary " type="submit">{{ __t('ถอนเงินทันที') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header">
          <h3 class="card-title">{{ __t('ประวัติการแนะนำ') }}</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive df-example demo-table">
            <table class="table table-bordered table-stripped datatable">
              <thead class=" border-t border-slate-100 dark:border-slate-800">
                <tr>
                  <th class="table-th">#</th>
                  <th class="table-th">{{ __t('บัญชี') }}</th>
                  <th class="table-th">{{ __t('ยอดเติมเงินรวม') }}</th>
                  <th class="table-th">{{ __t('ค่าคอมมิชชัน') }}</th>
                  <th class="table-th">{{ __t('เวลาสร้าง') }}</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                @foreach ($referrals as $value)
                  <tr>
                    <td class="table-td">{{ $loop->iteration }}</td>
                    <td class="table-td">{{ Helper::hideUsername($value->to_username) }}</td>
                    <td class="table-td">{{ formatCurrency($value->total_deposit ?? -1) }}</td>
                    <td class="table-td">{{ formatCurrency($value->total_commission) }}</td>
                    <td class="table-td">{{ $value->created_at }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header">
          <h3 class="card-title">{{ __t('ประวัติการทำรายการ') }}</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive df-example demo-table overflow-auto">
            <table class="table table-bordered table-stripped text-nowrap datatable">
              <thead>
                <tr>
                  <th class="table-th">#</th>
                  <th class="table-th">{{ __t('รหัสการทำรายการ') }}</th>
                  <th class="table-th">{{ __t('จำนวนเงิน') }}</th>
                  <th class="table-th">{{ __t('ยอดคงเหลือก่อน') }}</th>
                  <th class="table-th">{{ __t('ยอดคงเหลือหลัง') }}</th>
                  <th class="table-th">{{ __t('การทำรายการ') }}</th>
                  <th class="table-th">{{ __t('สถานะ') }}</th>
                  <th class="table-th">{{ __t('บัญชี') }}</th>
                  <th class="table-th">{{ __t('หมายเหตุ') }}</th>
                  <th class="table-th">{{ __t('ระบบ') }}</th>
                  <th class="table-th">{{ __t('เวลา') }}</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                @foreach ($histories as $value)
                  <tr>
                    <td class="table-td">{{ $value->id }}</td>
                    <td class="table-td">{{ $value->order_id }}</td>
                    <td class="table-td">{{ formatCurrency($value->amount) }}</td>
                    <td class="table-td">{{ formatCurrency($value->balance_before) }}</td>
                    <td class="table-td">{{ formatCurrency($value->balance_after) }}</td>
                    <td class="table-td">{{ $value->prefix === '+' ? __t('เพิ่มเงิน') : __t('หักเงิน') }}</td>
                    <td class="table-td">{!! $value->status_html !!}</td>
                    <td class="table-td">{{ $value->username }}</td>
                    <td class="table-td">{{ $value->user_note }}</td>
                    <td class="table-td">{{ $value->sys_note }}</td>
                    <td class="table-td">{{ $value->created_at }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    $(document).ready(() => {
      $('#withdraw_to').change(function() {
        if ($(this).val() == 'bank') {
          $('.group_banking').removeClass('d-none');
        } else {
          $('.group_banking').addClass('d-none');
        }
      })
      $("#withdraw_to").trigger('change')

      $("#form-withdraw").submit(async e => {
        e.preventDefault();

        const action = $(e.target).attr('action'),
          button = $(e.target).find('button[type="submit"]')
        payload = $formDataToPayload(new FormData(e.target));

        const confirm = await Swal.fire({
          title: 'ยืนยัน',
          html: `คุณต้องการถอน <b>${$formatNumber(payload.amount)} VND</b> ไปยัง <b>${payload.withdraw_to === 'bank' ? 'ธนาคาร' : 'เว็บไซต์'}</b> หรือไม่?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'ยืนยัน',
          cancelButtonText: 'ยกเลิก',
        })

        if (!confirm.isConfirmed) return

        if (payload.amount < {{ $config['min_withdraw'] ?? 0 }}) {
          return Swal.fire('ล้มเหลว', `จำนวนเงินถอนขั้นต่ำคือ {{ number_format($config['min_withdraw'] ?? 0) }} VND`, 'error')
        }

        if (payload.amount > {{ $config['max_withdraw'] ?? 0 }}) {
          return Swal.fire('ล้มเหลว', `จำนวนเงินถอนสูงสุดคือ {{ number_format($config['max_withdraw'] ?? 0) }} VND`, 'error')
        }

        $setLoading(button)

        axios.post(action, payload).then(({
          data: result
        }) => {
          Swal.fire('สำเร็จ', result.message, 'success').then(() => location.reload())
        }).catch(e => {
          Swal.fire('ล้มเหลว', $catchMessage(e), 'error')
        }).finally(() => {
          $removeLoading(button)
        })
      })
    })
  </script>
@endsection
