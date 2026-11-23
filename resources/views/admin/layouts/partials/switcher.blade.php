<div class="offcanvas offcanvas-end" tabindex="-1" id="switcher-canvas" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title text-default" id="offcanvasRightLabel">ปรับแต่งธีม</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
  </div>
  <div class="offcanvas-body">
    <nav class="border-bottom border-block-end-dashed">
      <div class="nav nav-tabs nav-justified" id="switcher-main-tab" role="tablist">
        <button class="nav-link active" id="switcher-home-tab" data-bs-toggle="tab" data-bs-target="#switcher-home" type="button" role="tab" aria-controls="switcher-home" aria-selected="true">รูปแบบธีม</button>
        <button class="nav-link" id="switcher-profile-tab" data-bs-toggle="tab" data-bs-target="#switcher-profile" type="button" role="tab" aria-controls="switcher-profile" aria-selected="false">สีของธีม</button>
      </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
      <div class="tab-pane fade show active border-0" id="switcher-home" role="tabpanel" aria-labelledby="switcher-home-tab" tabindex="0">
        <div class="">
          <p class="switcher-style-head">โหมดสี:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-light-theme">
                  สว่าง
                </label>
                <input class="form-check-input" type="radio" name="theme-style" id="switcher-light-theme" checked>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-dark-theme">
                  มืด
                </label>
                <input class="form-check-input" type="radio" name="theme-style" id="switcher-dark-theme">
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <p class="switcher-style-head">ทิศทาง:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-ltr">
                  LTR
                </label>
                <input class="form-check-input" type="radio" name="direction" id="switcher-ltr" checked>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-rtl">
                  RTL
                </label>
                <input class="form-check-input" type="radio" name="direction" id="switcher-rtl">
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <p class="switcher-style-head">รูปแบบการนำทาง:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-vertical">
                  แนวตั้ง
                </label>
                <input class="form-check-input" type="radio" name="navigation-style" id="switcher-vertical" checked>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-horizontal">
                  แนวนอน
                </label>
                <input class="form-check-input" type="radio" name="navigation-style" id="switcher-horizontal">
              </div>
            </div>
          </div>
        </div>
        <div class="navigation-menu-styles">
          <p class="switcher-style-head">รูปแบบเมนูแนวตั้งและแนวนอน:</p>
          <div class="row switcher-style gx-0 pb-2 gy-2">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-menu-click">
                  คลิกเมนู
                </label>
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-menu-click">
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-menu-hover">
                  ชี้เมนู
                </label>
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-menu-hover">
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-icon-click">
                  คลิกไอคอน
                </label>
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-icon-click">
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-icon-hover">
                  ชี้ไอคอน
                </label>
                <input class="form-check-input" type="radio" name="navigation-menu-styles" id="switcher-icon-hover">
              </div>
            </div>
          </div>
        </div>
        <div class="sidemenu-layout-styles">
          <p class="switcher-style-head">รูปแบบเมนูด้านข้าง:</p>
          <div class="row switcher-style gx-0 pb-2 gy-2">
            <div class="col-sm-6">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-default-menu">
                  เมนูเริ่มต้น
                </label>
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-default-menu" checked>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-closed-menu">
                  เมนูปิด
                </label>
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-closed-menu">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-icontext-menu">
                  ไอคอนและข้อความ
                </label>
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-icontext-menu">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-icon-overlay">
                  ไอคอนแบบทับ
                </label>
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-icon-overlay">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-detached">
                  แยกออก
                </label>
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-detached">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-double-menu">
                  เมนูคู่
                </label>
                <input class="form-check-input" type="radio" name="sidemenu-layout-styles" id="switcher-double-menu">
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <p class="switcher-style-head">รูปแบบหน้า:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-regular">
                  ปกติ
                </label>
                <input class="form-check-input" type="radio" name="page-styles" id="switcher-regular" checked>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-classic">
                  คลาสสิก
                </label>
                <input class="form-check-input" type="radio" name="page-styles" id="switcher-classic">
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-modern">
                  โมเดิร์น
                </label>
                <input class="form-check-input" type="radio" name="page-styles" id="switcher-modern">
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <p class="switcher-style-head">ความกว้างของเลย์เอาต์:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-full-width">
                  เต็มความกว้าง
                </label>
                <input class="form-check-input" type="radio" name="layout-width" id="switcher-full-width" checked>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-boxed">
                  แบบกรอบ
                </label>
                <input class="form-check-input" type="radio" name="layout-width" id="switcher-boxed">
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <p class="switcher-style-head">ตำแหน่งเมนู:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-menu-fixed">
                  คงที่
                </label>
                <input class="form-check-input" type="radio" name="menu-positions" id="switcher-menu-fixed" checked>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-menu-scroll">
                  เลื่อนได้
                </label>
                <input class="form-check-input" type="radio" name="menu-positions" id="switcher-menu-scroll">
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <p class="switcher-style-head">ตำแหน่งส่วนหัว:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-header-fixed">
                  คงที่
                </label>
                <input class="form-check-input" type="radio" name="header-positions" id="switcher-header-fixed" checked>
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-header-scroll">
                  เลื่อนได้
                </label>
                <input class="form-check-input" type="radio" name="header-positions" id="switcher-header-scroll">
              </div>
            </div>
          </div>
        </div>
        <div class="">
          <p class="switcher-style-head">หน้าโหลด:</p>
          <div class="row switcher-style gx-0">
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-loader-enable">
                  เปิดใช้งาน
                </label>
                <input class="form-check-input" type="radio" name="page-loader" id="switcher-loader-enable">
              </div>
            </div>
            <div class="col-4">
              <div class="form-check switch-select">
                <label class="form-check-label" for="switcher-loader-disable">
                  ปิดใช้งาน
                </label>
                <input class="form-check-input" type="radio" name="page-loader" id="switcher-loader-disable" checked>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade border-0" id="switcher-profile" role="tabpanel" aria-labelledby="switcher-profile-tab" tabindex="0">
        <div>
          <div class="theme-colors">
            <p class="switcher-style-head">สีเมนู:</p>
            <div class="d-flex switcher-style pb-2">
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-white" data-bs-toggle="tooltip" data-bs-placement="top" title="เมนูสว่าง" type="radio" name="menu-colors" id="switcher-menu-light" checked>
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="เมนูมืด" type="radio" name="menu-colors" id="switcher-menu-dark">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="เมนูสีหลัก" type="radio" name="menu-colors" id="switcher-menu-primary">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-gradient" data-bs-toggle="tooltip" data-bs-placement="top" title="เมนูแบบไล่สี" type="radio" name="menu-colors" id="switcher-menu-gradient">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-transparent" data-bs-toggle="tooltip" data-bs-placement="top" title="เมนูโปร่งใส" type="radio" name="menu-colors"
                  id="switcher-menu-transparent">
              </div>
            </div>
            <div class="px-4 pb-3 text-muted fs-11">หมายเหตุ: หากต้องการเปลี่ยนสีเมนูแบบไดนามิก ให้ใช้ตัวเลือกสีหลักด้านล่าง</div>
          </div>
          <div class="theme-colors">
            <p class="switcher-style-head">สีส่วนหัว:</p>
            <div class="d-flex switcher-style pb-2">
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-white" data-bs-toggle="tooltip" data-bs-placement="top" title="ส่วนหัวสว่าง" type="radio" name="header-colors" id="switcher-header-light" checked>
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="ส่วนหัวมืด" type="radio" name="header-colors" id="switcher-header-dark">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="ส่วนหัวสีหลัก" type="radio" name="header-colors" id="switcher-header-primary">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-gradient" data-bs-toggle="tooltip" data-bs-placement="top" title="ส่วนหัวแบบไล่สี" type="radio" name="header-colors"
                  id="switcher-header-gradient">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-transparent" data-bs-toggle="tooltip" data-bs-placement="top" title="ส่วนหัวโปร่งใส" type="radio" name="header-colors"
                  id="switcher-header-transparent">
              </div>
            </div>
            <div class="px-4 pb-3 text-muted fs-11">หมายเหตุ: หากต้องการเปลี่ยนสีส่วนหัวแบบไดนามิก ให้ใช้ตัวเลือกสีหลักด้านล่าง</div>
          </div>
          <div class="theme-colors">
            <p class="switcher-style-head">สีหลักของธีม:</p>
            <div class="d-flex flex-wrap align-items-center switcher-style">
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-primary-1" type="radio" name="theme-primary" id="switcher-primary">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-primary-2" type="radio" name="theme-primary" id="switcher-primary1">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-primary-3" type="radio" name="theme-primary" id="switcher-primary2">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-primary-4" type="radio" name="theme-primary" id="switcher-primary3">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-primary-5" type="radio" name="theme-primary" id="switcher-primary4">
              </div>
              <div class="form-check switch-select ps-0 mt-1 color-primary-light">
                <div class="theme-container-primary"></div>
                <div class="pickr-container-primary"></div>
              </div>
            </div>
          </div>
          <div class="theme-colors">
            <p class="switcher-style-head">พื้นหลังของธีม:</p>
            <div class="d-flex flex-wrap align-items-center switcher-style">
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-bg-1" type="radio" name="theme-background" id="switcher-background">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-bg-2" type="radio" name="theme-background" id="switcher-background1">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-bg-3" type="radio" name="theme-background" id="switcher-background2">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-bg-4" type="radio" name="theme-background" id="switcher-background3">
              </div>
              <div class="form-check switch-select me-3">
                <input class="form-check-input color-input color-bg-5" type="radio" name="theme-background" id="switcher-background4">
              </div>
              <div class="form-check switch-select ps-0 mt-1 tooltip-static-demo color-bg-transparent">
                <div class="theme-container-background"></div>
                <div class="pickr-container-background"></div>
              </div>
            </div>
          </div>
          <div class="menu-image mb-3">
            <p class="switcher-style-head">เมนูพร้อมภาพพื้นหลัง:</p>
            <div class="d-flex flex-wrap align-items-center switcher-style">
              <div class="form-check switch-select m-2">
                <input class="form-check-input bgimage-input bg-img1" type="radio" name="theme-background" id="switcher-bg-img">
              </div>
              <div class="form-check switch-select m-2">
                <input class="form-check-input bgimage-input bg-img2" type="radio" name="theme-background" id="switcher-bg-img1">
              </div>
              <div class="form-check switch-select m-2">
                <input class="form-check-input bgimage-input bg-img3" type="radio" name="theme-background" id="switcher-bg-img2">
              </div>
              <div class="form-check switch-select m-2">
                <input class="form-check-input bgimage-input bg-img4" type="radio" name="theme-background" id="switcher-bg-img3">
              </div>
              <div class="form-check switch-select m-2">
                <input class="form-check-input bgimage-input bg-img5" type="radio" name="theme-background" id="switcher-bg-img4">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="d-grid canvas-footer">
        <a href="javascript:void(0);" id="reset-all" class="btn btn-danger m-1">รีเซ็ต</a>
      </div>
    </div>
  </div>
</div>
