@extends('admin.layouts.master')
@section('title', 'Admin: Vouchers Management')
@section('content')
  <div class="card custom-card nova-admin-card">
    <div class="card-header justify-content-between">
      <div class="card-title">
        จัดการโค้ดโปรโมชัน
      </div>
      <a href="javascript:void(0);" data-bs-toggle="card-fullscreen">
        <i class="ri-fullscreen-line"></i>
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered text-nowrap datatable" style="width:100%">
          <thead>
            <tr>
              <th style="max-width: 25px">ลำดับ</th>
              <th style="max-width: 55px">ดำเนินการ</th>
              <th>โค้ดโปรโมชัน</th>
              <th>มูลค่า</th>
              <th>บัญชี</th>
              <th>เริ่มต้น</th>
              <th>หมดอายุ</th>
              <th>วันที่เพิ่ม</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($vouchers as $voucher)
              <tr>
                <td>{{ $voucher->id }}</td>
                <td>
                  <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $voucher->id }}" class="badge bg-success-gradient text-white shadow"><i class="fas fa-edit"></i></a>
                  <a href="javascript:deleteRow({{ $voucher->id }})" class="badge bg-danger-gradient text-white shadow"><i class="fas fa-trash"></i></a>
                </td>
                <td>{{ $voucher->code }}</td>
                <td>-{{ $voucher->value }}%</td>
                <td>{{ $voucher->username }}</td>
                <td>{{ $voucher->start_date->format('Y/m/d') }}</td>
                <td>{{ $voucher->expire_date->format('Y/m/d') }}</td>
                <td>{{ $voucher->created_at }}</td>
              </tr>

              <div class="modal fade" id="modal-edit-{{ $voucher->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">อัปเดตโค้ดโปรโมชัน #{{ $voucher->id }}</h5>
                      <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('admin.vouchers.update', ['id' => $voucher->id]) }}" method="POST" class="default-form">
                        @csrf
                        <div class="mb-3">
                          <label for="code" class="form-label">โค้ดโปรโมชัน</label>
                          <input type="text" class="form-control" id="code" name="code" placeholder="ป้อนโค้ดโปรโมชัน" value="{{ $voucher->code }}" required>
                        </div>
                        <div class="mb-3">
                          <label for="value" class="form-label">มูลค่า</label>
                          <input type="number" class="form-control" id="value" name="value" placeholder="ป้อนมูลค่าโปรโมชัน" value="{{ $voucher->value }}" required>
                        </div>
                        <div class="mb-3">
                          <label for="type" class="form-label">บริการ</label>
                          <select class="form-select" id="type" name="type">
                            <option value="all" @if ($voucher->type === 'all') selected @endif>ทั้งหมด</option>
                          </select>
                        </div>
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="start_date" class="form-label">วันเริ่มต้น</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $voucher->start_date->format('Y-m-d') }}" required>
                          </div>
                          <div class="col-md-6">
                            <label for="expire_date" class="form-label">วันหมดอายุ</label>
                            <input type="date" class="form-control" id="expire_date" name="expire_date" value="{{ $voucher->expire_date->format('Y-m-d') }}" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label for="username" class="form-label">บัญชี</label>
                          <select class="form-select" id="username" name="username">
                            <option value="">เลือกบัญชี</option>
                            @foreach ($users as $user)
                              <option value="{{ $user->username }}">{{ $user->username }} - {{ $user->email }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="mb-3">
                          <button class="btn btn-primary w-100" type="submit">อัปเดตเลย</button>
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
    <div class="card-footer">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create"><i class="fa fa-edit"></i> เพิ่มโค้ดโปรโมชัน</button>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">เพิ่มโค้ดโปรโมชัน</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.vouchers.store') }}" method="POST" class="default-form">
            @csrf
            <div class="mb-3">
              <label for="code" class="form-label">โค้ดโปรโมชัน</label>
              <input type="text" class="form-control" id="code" name="code" placeholder="ป้อนโค้ดโปรโมชัน" required>
            </div>
            <div class="mb-3">
              <label for="value" class="form-label">มูลค่า</label>
              <input type="number" class="form-control" id="value" name="value" placeholder="ป้อนมูลค่าโปรโมชัน" value="10" required>
            </div>
            <div class="mb-3">
              <label for="type" class="form-label">บริการ</label>
              <select class="form-select" id="type" name="type">
                <option value="all">ทั้งหมด</option>
              </select>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="start_date" class="form-label">วันเริ่มต้น</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
              </div>
              <div class="col-md-6">
                <label for="expire_date" class="form-label">วันหมดอายุ</label>
                <input type="date" class="form-control" id="expire_date" name="expire_date" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="username" class="form-label">บัญชี</label>
              <select class="form-select" id="username" name="username">
                <option value="">เลือกบัญชี</option>
                @foreach ($users as $user)
                  <option value="{{ $user->username }}">{{ $user->username }} - {{ $user->email }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary w-100" type="submit">เพิ่มใหม่</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    const deleteRow = async (id) => {
      const confirmDelete = await Swal.fire({
        title: 'คุณแน่ใจหรือต้องการลบ?',
        text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
      });

      if (!confirmDelete.isConfirmed) return;

      $showLoading();

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.vouchers.delete') }}', {
          id
        })

        Swal.fire('สำเร็จ', result.message, 'success').then(() => {
          window.location.reload();
        })
      } catch (error) {
        Swal.fire('ล้มเหลว', $catchMessage(error), 'error')
      }
    }
  </script>
@endsection
