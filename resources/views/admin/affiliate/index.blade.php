@php use App\Helpers\Helper; @endphp
@extends('admin.layouts.master')
@section('title', 'Admin: Affiliate Management')
@section('content')
  <div class="row">
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-primary rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">ยอดเงินธุรกรรมรวม</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ formatCurrency($totalAmount) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-success rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">ธุรกรรมที่สำเร็จ</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ formatCurrency($totalAmountCompleted) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-warning rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">ธุรกรรมที่รอดำเนินการ</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ formatCurrency($totalAmountPending) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3">
      <div class="card custom-card border-top-card border-top-danger rounded-0">
        <div class="card-body">
          <div class="text-center">
            <p class="fs-14 fw-semibold mb-2">ธุรกรรมที่ล้มเหลว</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap">
              <h4 class="mb-0 fw-semibold">{{ formatCurrency($totalAmountCancelled) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card custom-card nova-admin-card">
    <div class="card-header justify-content-between">
      <div class="card-title">ประวัติการถอนเงิน</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="table table-bordered table-striped text-nowrap" id="datatable">
          <thead>
            <tr>
              <th>#</th>
              <th class="text-center">ดำเนินการ</th>
              <th>รหัสธุรกรรม</th>
              <th>จำนวนเงิน</th>
              <th>ยอดคงเหลือก่อน</th>
              <th>ยอดคงเหลือหลัง</th>
              <th>ธุรกรรม</th>
              <th>สถานะ</th>
              <th>บัญชี</th>
              <th>หมายเหตุ</th>
              <th>ระบบ</th>
              <th>เวลา</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($histories as $value)
              <tr>
                <td>{{ $value->id }}</td>
                <td class="text-center">
                  <a href="javscript:void(0)" class="text-danger" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $value->id }}">รายละเอียด</a>
                </td>
                <td>{{ $value->order_id }}</td>
                <td>{{ formatCurrency($value->amount) }}</td>
                <td>{{ formatCurrency($value->balance_before) }}</td>
                <td>{{ formatCurrency($value->balance_after) }}</td>
                <td>{{ $value->prefix === '+' ? 'เพิ่มเงิน' : 'หักเงิน' }}</td>
                <td>{!! $value->status_html !!}</td>
                <td>{{ $value->username }}</td>
                <td>{{ $value->user_note }}</td>
                <td>{{ $value->sys_note }}</td>
                <td>{{ $value->created_at }}</td>
              </tr>
              @php
                $payment = $value->withdraw_detail;
              @endphp
              <div class="modal fade" id="modal-edit-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">อัปเดตข้อมูล #{{ $value->id }}</h5>
                      <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('admin.affiliates.update') }}" method="POST" enctype="multipart/form-data" class="default-form">
                        @csrf
                        <input type="hidden" name="id" value="{{ $value->id }}">

                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="bank_name" class="form-label">ธนาคาร</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ $payment['bank_name'] ?? '-' }}" disabled>
                          </div>
                          <div class="col-md-6">
                            <label for="account_number" class="form-label">หมายเลขบัญชี</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" value="{{ $payment['account_number'] ?? '-' }}" disabled>
                          </div>
                        </div>
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="account_name" class="form-label">เจ้าของบัญชี</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" value="{{ $payment['account_name'] ?? '-' }}" disabled>
                          </div>
                          <div class="col-md-6">
                            <label for="amount" class="form-label">จำนวนเงินที่ต้องถอน</label>
                            <input type="text" class="form-control" id="amount" name="amount" value="{{ formatCurrency($value->amount) }}" disabled>
                          </div>
                        </div>
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="user_note" class="form-label">หมายเหตุ</label>
                            <textarea class="form-control" id="user_note" name="user_note" rows="3">{{ $value->user_note }}</textarea>
                          </div>
                          <div class="col-md-6">
                            <label for="sys_note" class="form-label">หมายเหตุจากระบบ</label>
                            <textarea class="form-control" id="sys_note" name="sys_note" rows="3" disabled>{{ $value->sys_note }}</textarea>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label for="status" class="form-label">สถานะ</label>
                          <select class="form-control" id="status" name="status">
                            <option value="Pending" @if ($value->status === 'Pending') selected @endif>รอดำเนินการ</option>
                            <option value="Completed" @if ($value->status === 'Completed') selected @endif>เสร็จสมบูรณ์</option>
                            <option value="Cancelled" @if ($value->status === 'Cancelled') selected @endif>ยกเลิก</option>
                          </select>
                        </div>
                        <div class="text-center mb-3">
                          @if ($payment)
                            <img
                              src="https://api.vietqr.io/{{ $payment['bank_name'] }}/{{ $payment['account_number'] }}/{{ $value->amount }}/tt hoa don {{ $value->order_id }}/vietqr_net_2.jpg?accountName={{ $payment['account_name'] }}"
                              width="300" alt="">
                          @endif
                        </div>
                        <div class="mb-3">
                          <button class="btn btn-primary w-100" type="submit">อัปเดต</button>
                        </div>
                      </form>

                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">ประวัติการรับเงิน</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="table table-bordered table-striped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>จำนวนเงิน</th>
              <th>ยอดคงเหลือก่อน</th>
              <th>ยอดคงเหลือหลัง</th>
              <th>ธุรกรรม</th>
              <th>สถานะ</th>
              <th>บัญชี</th>
              <th>หมายเหตุ</th>
              <th>ระบบ</th>
              <th>เวลา</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($commissions as $value)
              <tr>
                <td>{{ $value->id }}</td>
                <td>{{ formatCurrency($value->amount) }}</td>
                <td>{{ formatCurrency($value->balance_before) }}</td>
                <td>{{ formatCurrency($value->balance_after) }}</td>
                <td>{{ $value->prefix === '+' ? 'เพิ่มเงิน' : 'หักเงิน' }}</td>
                <td>{!! $value->status_html !!}</td>
                <td>{{ $value->username }}</td>
                <td>{{ $value->user_note }}</td>
                <td>{{ $value->sys_note }}</td>
                <td>{{ $value->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">รายชื่อผู้ที่ถูกแนะนำ - ขณะนี้มี <span class="text-danger">{{ $referrals->count() }}</span> คน</div>
        </div>
        <div class="card-body">
          <div class="table-responsive theme-scrollbar">
            <table class="display table table-bordered table-stripped text-nowrap datatable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>บัญชี</th>
                  <th>แนะนำ</th>
                  <th>ยอดฝากรวม</th>
                  <th>ค่าคอมมิชชันที่ได้รับรวม</th>
                  <th>วันที่แนะนำ</th>
                  <th>วันที่อัปเดต</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($referrals as $referral)
                  <tr>
                    <td>{{ $referral['id'] }}</td>
                    <td>{{ $referral['username'] }}</td>
                    <td>{{ $referral->to_username }}</td>
                    <td>{{ formatCurrency($referral['total_deposit']) }} ₫</td>
                    <td>{{ formatCurrency($referral->total_commission) }}</td>
                    <td>{{ $referral['created_at'] }}</td>
                    <td>{{ $referral['updated_at'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">รายชื่อผู้ใช้ที่เข้าร่วมโปรแกรม - ขณะนี้มี <span class="text-danger">{{ $affiliates->count() }}</span> คน</div>
        </div>
        <div class="card-body">
          <div class="table-responsive theme-scrollbar">
            <table class="display table table-bordered table-stripped text-nowrap datatable">
              <thead>
                <tr>
                  <th>UID</th>
                  <th>บัญชี</th>
                  <th>จำนวนคลิก</th>
                  <th>จำนวนการลงทะเบียน</th>
                  <th>วันที่เข้าร่วม</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($affiliates as $referral)
                  <tr>
                    <td>{{ $referral->id }}</td>
                    <td>{{ $referral->username }}</td>
                    <td>{{ $referral->clicks }}</td>
                    <td>{{ $referral->signups }}</td>
                    <td>{{ $referral->created_at }}</td>
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
      $("#datatable").DataTable({
        responsive: true,
        columnDefs: [{
          orderable: false,
          targets: [0, 1, 2, 3, 4, 5, 6, 7, 8]
        }]
      });
    })
  </script>
@endsection
