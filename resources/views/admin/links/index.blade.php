@extends('admin.layouts.master')
@section('title', 'Admin: Links Management')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">จัดการลิงก์</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>ดำเนินการ</th>
              <th>รูปภาพ / ไอคอน</th>
              <th>ชื่อ</th>
              <th>ประเภท</th>
              <th>ลิงก์</th>
              <th>วันที่สร้าง</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($links as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}" class="shadow text-white badge bg-info-gradient"><i class="fa fa-edit"></i></a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="shadow text-white badge bg-danger-gradient"><i class="fa fa-trash"></i></a>
                </td>
                <td>
                  <img src="{{ $item->image }}" width="30" alt="">
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->target === '_blank' ? 'เปิดแท็บใหม่' : 'ไม่เปิดแท็บใหม่' }}</td>
                <td>{{ $item->link }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
              <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">อัปเดตข้อมูล #{{ $item->id }}</h5>
                      <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('admin.links.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <div class="mb-3">
                          <label for="image" class="form-label">รูปภาพ / ไอคอน</label>
                          <input class="form-control" type="file" id="image" name="image">
                        </div>
                        <div class="mb-3">
                          <label for="name" class="form-label">ชื่อ</label>
                          <input class="form-control" type="text" id="name" name="name" value="{{ $item->name }}" required>
                        </div>
                        <div class="mb-3">
                          <label for="link" class="form-label">Link</label>
                          <input class="form-control" type="url" id="link" name="link" value="{{ $item->link }}" required>
                        </div>
                        <div class="mb-3">
                          <label for="target" class="form-label">ประเภท</label>
                          <select class="form-control" id="target" name="target">
                            <option value="_blank" @if ($item->target === '_blank') selected @endif>เปิดแท็บใหม่</option>
                            <option value="_self" @if ($item->target === '_self') selected @endif>ไม่เปิดแท็บใหม่</option>
                          </select>
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
    <div class="card-footer">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create">เพิ่มบัญชีใหม่</button>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">เพิ่มข้อมูลใหม่</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.links.store') }}" method="POST" enctype="multipart/form-data" class="default-form">
            @csrf
            <div class="mb-3">
              <label for="image" class="form-label">รูปภาพ / ไอคอน</label>
              <input class="form-control" type="file" id="image" name="image" required>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">ชื่อ</label>
              <input class="form-control" type="text" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="link" class="form-label">Link</label>
              <input class="form-control" type="url" id="link" name="link" required>
            </div>
            <div class="mb-3">
              <label for="target" class="form-label">ประเภท</label>
              <select class="form-control" id="target" name="target">
                <option value="_blank">เปิดแท็บใหม่</option>
                <option value="_self">ไม่เปิดแท็บใหม่</option>
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
        } = await axios.post('{{ route('admin.links.delete') }}', {
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
