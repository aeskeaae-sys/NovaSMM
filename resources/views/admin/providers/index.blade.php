@php use App\Helpers\Helper; @endphp
@extends('admin.layouts.master')
@section('title', $pageTitle)
@section('content')
  <div class="mb-3 text-end">
    <button data-bs-toggle="modal" data-bs-target="#modal-create" class="btn btn-outline-primary"><i class="fas fa-plus"></i> {{ __t('เพิ่มใหม่') }}</button>
  </div>
  <div class="mb-3 text-bold">
    <div class="alert alert-danger">อัปเดต % การขึ้นราคาที่ <a href="{{ route('admin.settings.currencies') }}">ที่นี่</a> (หมวด: ใช้สำหรับซิงก์และเพิ่มบริการจำนวนมาก).</div>
    <div class="alert alert-danger">Cron ซิงก์ราคาบริการอัตโนมัติ: <a href="{{ url('/schedule/services/sync') }}" target="_blank">{{ url('/schedule/services/sync') }}</a> [อย่าลืมเปิด "ซิงก์ราคา" เป็น ON]</div>
  </div>
  <div class="card custom-card nova-admin-card">
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable" id="basic-1">
          <thead>
            <tr>
              <th>#</th>
              <th>ชื่อ</th>
              <th>ยอดคงเหลือ</th>
              <th>อัตราแลกเปลี่ยน</th>
              <th>+% การขึ้นราคา</th>
              <th>แปลง</th>
              <th>หมายเหตุ</th>
              <th data-sortable="false" width="30" class="text-center">สถานะ</th>
              <th data-sortable="false" width="30" class="text-center">ซิงก์ราคา</th>
              <th data-sortable="false" width="370">ดำเนินการ</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($records as $value)
              <tr>
                <td>{{ $value->id }}</td>
                <td>{{ $value->name }}</td>
                <td>{{ number_format($value->balance, 2) }} $</td>
                <td>1 = {{ $value->exchange_rate }}</td>
                <td>{{ $value->price_percentage_increase ?? cur_setting('default_price_percentage_increase', 25) }}%</td>
                <td>{{ formatCurrency($value->balance * $value->exchange_rate) }}</td>
                <td>{{ $value->description }}</td>
                <td class="text-center">
                  @if ($value->status)
                    <span class="badge bg-success">{{ __t('ใช้งาน') }}</span>
                  @else
                    <span class="badge bg-danger">{{ __t('ไม่ใช้งาน') }}</span>
                  @endif
                </td>
                <td>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault{{ $value->id }}" value="{{ $value->id }}" @if ($value->auto_sync) checked @endif
                      onchange="updateAutoSync(this)">
                    <label class="form-check-label" for="flexSwitchCheckDefault{{ $value->id }}"></label>
                  </div>
                </td>
                <td>
                  <button data-bs-toggle="modal" data-bs-target="#modal-update-{{ $value->id }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i> แก้ไข</button>
                  <form action="{{ route('admin.providers.price-update', ['id' => $value->id]) }}" method="POST" class="d-inline axios-form" data-confirm="1" data-reload="1">
                    @csrf
                    <button class="btn btn-outline-warning btn-sm" type="submit"><i class="fas fa-dollar-sign me-1"></i> {{ __t('ซิงก์ราคา') }}</button>
                  </form>
                  <form action="{{ route('admin.providers.balance-update', ['id' => $value->id]) }}" method="POST" class="d-inline axios-form" data-confirm="1" data-reload="1">
                    @csrf
                    <button class="btn btn-outline-success btn-sm" type="submit"><i class="fas fa-wallet me-1"></i> {{ __t('อัปเดตยอดคงเหลือ') }}</button>
                  </form>

                  <form action="{{ route('admin.providers.delete', ['id' => $value->id]) }}" method="POST" class="d-inline axios-form" data-confirm="1" data-reload="1">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm" type="submit"><i class="fas fa-trash me-1"></i> {{ __t('ลบ') }}</button>
                  </form>
                </td>
              </tr>

              <div class="modal fade" id="modal-update-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">อัปเดตข้อมูล #{{ $value->id }}</h5>
                      <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="alert alert-danger">
                        Note: This script supports most of all API Providers (API v2) like: vinasmm.com, hqsmartpanel.com etc. So it doesn't support another API provider which have different API Parameters
                      </div>
                      <form action="{{ route('admin.providers.update', ['id' => $value->id]) }}" method="POST" class="axios-form" data-reload="1">
                        @csrf
                        <div class="mb-3">
                          <label for="name" class="form-label">ชื่อ</label>
                          <input class="form-control" type="text" id="name" name="name" value="{{ $value->name }}" required>
                        </div>
                        <div class="mb-3">
                          <label for="url" class="form-label">URL</label>
                          <input class="form-control" type="text" id="url" name="url" value="{{ $value->url }}" required>
                        </div>
                        <div class="mb-3">
                          <label for="key" class="form-label">Key</label>
                          <input class="form-control" type="text" id="key" name="key" value="{{ $value->key }}" required>
                        </div>
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="exchange_rate" class="form-label">อัตราแลกเปลี่ยน</label>
                            <input class="form-control" type="number" id="exchange_rate" name="exchange_rate" value="{{ $value->exchange_rate }}" required>
                          </div>
                          <div class="col-md-6">
                            <label for="price_percentage_increase" class="form-label">% การขึ้นราคา</label>
                            <input class="form-control" type="number" id="price_percentage_increase" name="price_percentage_increase" value="{{ $value->price_percentage_increase }}" required>
                            <small>% การขึ้นราคาอัตโนมัติจากไซต์ต้นทาง</small>
                          </div>
                        </div>
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="rate_per_1k" class="form-label">รูปแบบราคาจาก API</label>
                            <select name="rate_per_1k" id="rate_per_1k" class="form-control">
                              <option value="0" @if (!$value->rate_per_1k) selected @endif>ราคาต่อ 1 ครั้ง</option>
                              <option value="1" @if ($value->rate_per_1k) selected @endif>ราคาต่อ 1,000 ครั้ง</option>
                            </select>
                            <small>ตรวจสอบว่า API ต้นทางส่งอัตราเป็นราคาต่อ 1,000 ครั้งหรือ 1 ครั้ง</small>
                          </div>
                          <div class="col-md-6">
                            <label for="status" class="form-label">สถานะ</label>
                            <select class="form-control" id="status" name="status">
                              <option value="1" @if ($value->status === true) selected @endif>ใช้งาน</option>
                              <option value="0" @if ($value->status === false) selected @endif>ไม่ใช้งาน</option>
                            </select>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label for="description" class="form-label">หมายเหตุ</label>
                          <textarea class="form-control" id="description" name="description" rows="3">{{ $value->description }}</textarea>
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

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">เพิ่มข้อมูลใหม่</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger">
            หมายเหตุ: ซอร์สโค้ดนี้รองรับผู้ให้บริการ API ส่วนใหญ่ (API v2) เช่น: app.x999.vn ฯลฯ ดังนั้น ซอร์สโค้ดนี้จึงไม่รองรับผู้ให้บริการ API อื่นที่มีพารามิเตอร์ API แตกต่างกัน
          </div>
          <form action="{{ route('admin.providers.store') }}" method="POST" class="axios-form" data-reload="1">
            @csrf
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="name" class="form-label">ชื่อ</label>
                <input class="form-control" type="text" id="name" name="name" required>
              </div>
              <div class="col-md-6">
                <label for="type" class="form-label">ประเภท</label>
                <select class="form-control" id="type" name="type">
                  <option value="standard">Standard</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label for="url" class="form-label">URL</label>
              <input class="form-control" type="text" id="url" name="url" placeholder="https://app.x999.vn/api/v2" required>
            </div>
            <div class="mb-3">
              <label for="key" class="form-label">Key</label>
              <input class="form-control" type="text" id="key" name="key" required>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="exchange_rate" class="form-label">อัตราแลกเปลี่ยน</label>
                <input class="form-control" type="number" id="exchange_rate" name="exchange_rate" required>
              </div>
              <div class="col-md-6">
                <label for="price_percentage_increase" class="form-label">% การขึ้นราคา</label>
                <input class="form-control" type="number" id="price_percentage_increase" name="price_percentage_increase" required>
                <small>ตรวจสอบว่า API ต้นทางส่งอัตราเป็นราคาต่อ 1,000 ครั้งหรือ 1 ครั้ง</small>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="rate_per_1k" class="form-label">รูปแบบราคาจาก API</label>
                <select name="rate_per_1k" id="rate_per_1k" class="form-control">
                  <option value="0">ราคาต่อ 1 ครั้ง</option>
                  <option value="1" selected>ราคาต่อ 1,000 ครั้ง</option>
                </select>
                <small>ตรวจสอบว่า API ต้นทางส่งอัตราเป็นราคาต่อ 1,000 ครั้งหรือ 1 ครั้ง</small>
              </div>
              <div class="col-md-6">
                <label for="status" class="form-label">สถานะ</label>
                <select class="form-control" id="status" name="status">
                  <option value="1">ใช้งาน</option>
                  <option value="0">ไม่ใช้งาน</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">หมายเหตุ</label>
              <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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
    const updateAutoSync = (element) => {
      let id = element.value;
      let status = element.checked ? 1 : 0;

      axios.post('{{ route('admin.providers.auto-sync') }}', {
        id: id,
        status: !!status
      }).then((response) => {
        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ',
          text: response.data.message
        })
      }).catch((error) => {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: $catchMessage(error)
        })
      });
    }
  </script>
@endsection
