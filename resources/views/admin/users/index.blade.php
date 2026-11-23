@extends('admin.layouts.master')
@section('title', 'Admin: Users Management')
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title d-flex align-items-center gap-2">
        <i class="bx bx-user" style="color:#0EA5E9"></i> จัดการสมาชิก
        <span class="badge ms-2" style="background:rgba(37,99,235,0.10);color:#0EA5E9">NovaSMM</span>
      </div>
      <a href="javascript:void(0);" data-bs-toggle="card-fullscreen">
        <i class="ri-fullscreen-line"></i>
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="datatable" class="table table-bordered table-striped text-nowrap" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>ดำเนินการ</th>
              <th>บัญชี</th>
              <th>Email</th>
              <th>ยอดคงเหลือ</th>
              <th>ยอดฝากรวม</th>
              <th>ระดับ</th>
              <th>สถานะ</th>
              <th>ที่อยู่ IP</th>
              <th>ลงทะเบียนด้วย</th>
              <th>วันที่ลงทะเบียน</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    $(document).ready(function() {
      //DataTable
      $("#datatable").DataTable({
        order: [0, 'desc'],
        responsive: false,
        lengthMenu: [
          [10, 50, 100, 200, 500, 1000, 2000, 10000, -1],
          [10, 50, 100, 200, 500, 1000, 2000, 10000, "All"]
        ],
        language: {
          searchPlaceholder: 'ค้นหา...',
          sSearch: '',
          lengthMenu: '_MENU_',
        },
        processing: true,
        serverSide: true,
        ajax: {
          url: '/api/admin/users',
          async: true,
          type: 'GET',
          dataType: 'json',
          headers: {
            'Authorization': 'Bearer ' + userData.access_token,
            'Accept': 'application/json',
          },
          data: function(data) {
            let payload = {}
            // default params

            // set params
            payload.page = data.start / data.length + 1;
            payload.limit = data.length;
            payload.search = data.search.value;
            payload.sort_by = data.columns[data.order[0].column].data;
            payload.sort_type = data.order[0].dir;
            // return json
            return payload;
          },
          error: function(xhr) {
            Swal.fire('ล้มเหลว', $catchMessage(xhr), 'error')
          },
          dataFilter: function(data) {
            let json = JSON.parse(data);
            if (json.status) {
              json.recordsTotal = json.data.meta.total
              json.recordsFiltered = json.data.meta.total
              json.data = json.data.data
              return JSON.stringify(json); // return JSON string
            } else {
              Swal.fire('ล้มเหลว', json.message, 'error')
              return JSON.stringify({
                recordsTotal: 0,
                recordsFiltered: 0,
                data: []
              }); // return JSON string
            }
          }
        },
        columns: [{
          data: 'id',
        }, {
          render: function(data, type, row) {
            return `
            <a href="/admin/users/edit/${row.id}" class="shadow badge bg-success-gradient text-white me-1"><i class="fas fa-edit"></i></a>
            <a href="/admin/users/login-to/${row.username}" class="shadow badge bg-danger-gradient text-white me-1"><i class="fas fa-sign-in"></i></a>
            `
          }
        }, {
          data: 'username',
        }, {
          data: 'email',
        }, {
          data: 'balance',
          render: function(data) {
            return $formatCurrency(data)
          }
        }, {
          data: 'total_deposit',
          render: function(data) {
            return $formatCurrency(data)
          }
        }, {
          data: 'role',
          render: function(data) {
            return data === 'admin' ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-primary">Member</span>'
          }
        }, {
          data: 'status',
          render: function(data) {
            return data === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Locked</span>'
          }
        }, {
          data: 'ip_address',
        }, {
          data: 'register_by'
        }, {
          data: 'created_at',
          render: function(data) {
            return $formatDate(data)
          }
        }],
        columnDefs: [{
          orderable: false,
          targets: [1]
        }],
      })
    })
  </script>
@endsection
