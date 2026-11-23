@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
  <style>
    pre {
      direction: ltr;
      unicode-bidi: bidi-override;
      padding-left: 24px;
      padding-right: 24px;
      padding-top: 24px;
      padding-bottom: 24px;
      font-weight: 300;
      background: yellowgreen;
      color: black;
      border-color: yellowgreen;
      box-shadow: firebrick;
      border-style: solid;
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
      border-bottom-left-radius: 8px;
      border-bottom-right-radius: 8px;
      border-left-width: 0px;
      border-right-width: 0px;
      border-top-width: 0px;
      border-bottom-width: 0px;
    }
  </style>
  <div class="card">
    <div class="card-body">
      <h2 class="mb-3">API</h2>
      <div class="table-responsive mb-3">
        <table class="table">
          <tbody>
            <tr>
              <td class="width-40">วิธีการ HTTP</td>
              <td>POST</td>
            </tr>
            <tr>
              <td>API URL</td>
              <td>https://{{ domain() }}/api/v2</td>
            </tr>
            <tr>
              <td>คีย์ API</td>
              <td>
                รับคีย์ได้ที่หน้า <a href="{{ route('account.profile.index') }}">{{ __t('tài khoản') }}</a>
              </td>
            </tr>
            <tr>
              <td>รูปแบบการตอบกลับ</td>
              <td>JSON</td>
            </tr>
          </tbody>
        </table>
      </div>
      <h4 class="mb-3">{{ __t('Danh sách dịch vụ') }}</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>services</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
[
  {
  "service": 1,
  "name": "Followers",
  "type": "Default",
  "category": "First Category",
  "rate": "0.90",
  "min": "50",
  "max": "10000",
  "refill": true,
  "cancel": true
  },
  {
  "service": 2,
  "name": "Comments",
  "type": "Custom Comments",
  "category": "Second Category",
  "rate": "8",
  "min": "10",
  "max": "1500",
  "refill": false,
  "cancel": true
  }
]
</pre>
      <h4 class="mb-3">{{ __t('Tạo đơn hàng') }}</h4>
      <form class="">
        <div class="form-group">
          <select class="form-control input-sm" id="service_type">
            <option value="0">ปกติ</option>
            {{-- <option value="10">Package</option> --}}
            <option value="2">คอมเมนต์กำหนดเอง</option>
            {{-- <option value="4">Mentions Custom List</option> --}}
            {{-- <option value="7">Mentions User Followers</option> --}}
            {{-- <option value="14">แพ็กเกจคอมเมนต์กำหนดเอง</option> --}}
            {{-- <option value="15">Comment Likes</option> --}}
            {{-- <option value="17">Poll</option> --}}
            {{-- <option value="100">Subscriptions</option> --}}
          </select>
        </div>
      </form>
      <div id="type_0" style="">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
                <tr>
                  <td>quantity</td>
                  <td>จำนวนที่ต้องการ</td>
                </tr>
                <tr>
                  <td>runs (optional)</td>
                  <td>จำนวนรอบที่ต้องส่งมอบ</td>
                </tr>
                <tr>
                  <td>ช่วงเวลา (ไม่บังคับ)</td>
                  <td>ช่วงเวลาเป็นนาที</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_10" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_2" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
                <tr>
                  <td>comments</td>
                  <td>รายการคอมเมนต์ คั่นด้วย \r\n หรือ \n</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_4" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
                <tr>
                  <td>usernames</td>
                  <td>รายการชื่อผู้ใช้ คั่นด้วย \r\n หรือ \n</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_7" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
                <tr>
                  <td>quantity</td>
                  <td>จำนวนที่ต้องการ</td>
                </tr>
                <tr>
                  <td>username</td>
                  <td>URL ที่ใช้ดึงข้อมูลผู้ติดตาม</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_100" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>username</td>
                  <td>ชื่อผู้ใช้</td>
                </tr>
                <tr>
                  <td>min</td>
                  <td>จำนวนขั้นต่ำ</td>
                </tr>
                <tr>
                  <td>max</td>
                  <td>จำนวนสูงสุด</td>
                </tr>
                <tr>
                  <td>posts (optional)</td>
                  <td>Use this parameter if you want to limit the number of new (future) posts that will be parsed and for which orders will be created. If posts parameter is not set, the subscription will be created for
                    an unlimited number of posts.</td>
                </tr>
                <tr>
                  <td>old_posts (optional)</td>
                  <td>จำนวนโพสต์ที่มีอยู่ซึ่งจะถูกวิเคราะห์และสร้างคำสั่งซื้อให้ ใช้ได้เฉพาะบริการที่รองรับตัวเลือกนี้</td>
                </tr>
                <tr>
                  <td>delay</td>
                  <td>หน่วงเวลาเป็นนาที ค่าที่เป็นไปได้: 0, 5, 10, 15, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 360, 420, 480, 540, 600</td>
                </tr>
                <tr>
                  <td>expiry (optional)</td>
                  <td>วันหมดอายุ รูปแบบ ว/ด/ป</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_14" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
                <tr>
                  <td>comments</td>
                  <td>รายการคอมเมนต์ คั่นด้วย \r\n หรือ \n</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_15" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
                <tr>
                  <td>quantity</td>
                  <td>จำนวนที่ต้องการ</td>
                </tr>
                <tr>
                  <td>username</td>
                  <td>ชื่อผู้ใช้ของเจ้าของคอมเมนต์</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="type_17" style="display:none;">
        <div class="table-bg">
          <div class="table-wr ">
            <table class="table mb-3">
              <thead>
                <tr>
                  <th class="width-40">พารามิเตอร์</th>
                  <th>คำอธิบาย</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>key</td>
                  <td>คีย์ API ของคุณ</td>
                </tr>
                <tr>
                  <td>action</td>
                  <td>add</td>
                </tr>
                <tr>
                  <td>service</td>
                  <td>รหัสบริการ</td>
                </tr>
                <tr>
                  <td>link</td>
                  <td>ลิงก์ไปยังหน้า</td>
                </tr>
                <tr>
                  <td>quantity</td>
                  <td>จำนวนที่ต้องการ</td>
                </tr>
                <tr>
                  <td>answer_number</td>
                  <td>หมายเลขคำตอบของโพลล์</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
{
  "order": 23501
}
</pre>
      <h4 class="mb-3">{{ __t('Trạng thái đơn') }}</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>status</td>
              </tr>
              <tr>
                <td>order</td>
                <td>รหัสคำสั่งซื้อ</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
{
  "charge": "0.27819",
  "start_count": "3572",
  "status": "Partial",
  "remains": "157",
  "currency": "USD"
}
</pre>
      <h4 class="mb-3">{{ __t('Lấy nhiều đơn cùng lúc') }}</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>status</td>
              </tr>
              <tr>
                <td>orders</td>
                <td>รหัสคำสั่งซื้อ (คั่นด้วยเครื่องหมายจุลภาค สูงสุด 100 รหัส)</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
{
  "1": {
  "charge": "0.27819",
  "start_count": "3572",
  "status": "Partial",
  "remains": "157",
  "currency": "USD"
  },
  "10": {
  "error": "Incorrect order ID"
  },
  "100": {
  "charge": "1.44219",
  "start_count": "234",
  "status": "In progress",
  "remains": "10",
  "currency": "USD"
  }
}
</pre>
      <h4 class="mb-3">สร้างการเติมซ้ำ</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>refill</td>
              </tr>
              <tr>
                <td>order</td>
                <td>รหัสคำสั่งซื้อ</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
{
  "refill": "1"
}
</pre>
      <h4 class="mb-3">สร้างการเติมซ้ำหลายรายการ</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>refill</td>
              </tr>
              <tr>
                <td>orders</td>
                <td>รหัสคำสั่งซื้อ (คั่นด้วยเครื่องหมายจุลภาค สูงสุด 100 รหัส)</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
[
  {
    "order": 1,
    "refill": 1
  },
  {
    "order": 2,
    "refill": 2
  },
  {
  "order": 3,
  "refill": {
      "error": "Incorrect order ID"
    }
  }
]
</pre>
      <h4 class="mb-3">ดูสถานะการเติมซ้ำ</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>refill_status</td>
              </tr>
              <tr>
                <td>refill</td>
                <td>รหัสการเติมซ้ำ</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
{
  "status": "Completed"
}
</pre>
      <h4 class="mb-3">ดูสถานะการเติมซ้ำหลายรายการ</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>refill_status</td>
              </tr>
              <tr>
                <td>refills</td>
                <td>รหัสการเติมซ้ำ (คั่นด้วยเครื่องหมายจุลภาค สูงสุด 100 รหัส)</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
[
  {
  "refill": 1,
  "status": "Completed"
  },
  {
  "refill": 2,
  "status": "Rejected"
  },
  {
  "refill": 3,
  "status": {
      "error": "Refill not found"
    }
  }
]
</pre>
      <h4 class="mb-3">สร้างการยกเลิก</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>cancel</td>
              </tr>
              <tr>
                <td>orders</td>
                <td>รหัสคำสั่งซื้อ (คั่นด้วยเครื่องหมายจุลภาค สูงสุด 100 รหัส)</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
[
  {
  "order": 9,
  "cancel": {
      "error": "Incorrect order ID"
    }
  },
  {
    "order": 2,
    "cancel": 1
  }
]
</pre>
      <h4 class="mb-3">ยอดคงเหลือของผู้ใช้</h4>
      <div class="table-bg">
        <div class="table-wr ">
          <table class="table mb-3">
            <thead>
              <tr>
                <th class="width-40">พารามิเตอร์</th>
                <th>คำอธิบาย</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>key</td>
                <td>คีย์ API ของคุณ</td>
              </tr>
              <tr>
                <td>action</td>
                <td>balance</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h6>ตัวอย่างการตอบกลับ</h6>
      </div>
      <pre>
{
  "balance": "100.84292",
  "currency": "USD"
}
</pre>
      <a href="/example.txt" class="btn btn-big-secondary" target="_blank">ตัวอย่างโค้ด PHP</a>
    </div>
  </div>
@endsection
@section('scripts')

  <script>
    $("#service_type").change(function() {
      $("div[id^='type_']").hide();
      var e = $("#service_type").val();
      $("#type_" + e).show();
    })
    $("#service_type").trigger("change");
  </script>
@endsection
