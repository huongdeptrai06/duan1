<!doctype html>
<html lang="vi">
  <!--begin::Head-->
  <head>
    <meta charset="utf-8" />
    <title><?= $title ?? 'Trang chủ quản lý tour' ?></title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="<?= $title ?? 'Trang chủ quản lý tour' ?>" />
    <meta name="author" content="FPOLY HN" />
    <meta name="description" content="Website Quản Lý Tour FPOLY HN"/>
    <meta name="keywords" content="Website Quản Lý Tour FPOLY HN"/>
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="<?= asset('dist/css/adminlte.css') ?>" />
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::Custom Header Styles-->
    <style>
      /* Cải thiện căn chỉnh các nút header */
      .app-header .navbar-nav.ms-auto {
        gap: 0.5rem;
        padding-right: 0.5rem;
      }
      
      .app-header .navbar-nav.ms-auto .nav-item {
        display: flex;
        align-items: center;
      }
      
      .app-header .navbar-nav.ms-auto .nav-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
      }
      
      .app-header .navbar-nav.ms-auto .nav-link:hover {
        background-color: rgba(0, 0, 0, 0.05);
      }
      
      .app-header .navbar-nav.ms-auto .nav-link i {
        font-size: 1.1rem;
      }
      
      /* Cải thiện badge thông báo */
      .app-header .navbar-nav .nav-link.position-relative {
        padding-right: 1.5rem;
      }
      
      .app-header .navbar-badge {
        font-size: 0.65rem;
        padding: 0.2em 0.4em;
        min-width: 1.2em;
        line-height: 1.2;
        top: -0.25rem;
        right: 0.25rem;
      }
      
      /* Cải thiện user menu */
      .app-header .user-menu .nav-link {
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
      }
      
      .app-header .user-menu .user-image {
        width: 32px;
        height: 32px;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.3);
      }
      
      @media (max-width: 767.98px) {
        .app-header .navbar-nav.ms-auto {
          gap: 0.25rem;
        }
        
        .app-header .navbar-nav.ms-auto .nav-link {
          padding: 0.4rem 0.6rem;
        }
      }
      
      /* Đảm bảo các nút Quay lại được căn phải */
      .card-footer .d-flex.justify-content-end,
      .card-header .d-flex.justify-content-end,
      .d-flex.justify-content-end.gap-2 {
        width: 100%;
        justify-content: flex-end !important;
      }
      
      /* Đảm bảo container nút được căn phải hoàn toàn */
      .card-header .d-flex.justify-content-between {
        flex-wrap: nowrap;
      }
      
      .card-header .d-flex.justify-content-end {
        margin-left: auto;
      }
    </style>
    <!--end::Custom Header Styles-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <?php block('header'); ?>

      <?php block('aside'); ?>

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0"><?= $pageTitle ?? 'Trang chủ' ?></h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="<?= BASE_URL ?>home">Home</a></li>
                  <?php if (isset($breadcrumb)): ?>
                    <?php foreach ($breadcrumb as $item): ?>
                      <li class="breadcrumb-item <?= $item['active'] ?? false ? 'active' : '' ?>" <?= $item['active'] ?? false ? 'aria-current="page"' : '' ?>>
                        <?php if ($item['active'] ?? false): ?>
                          <?= $item['label'] ?>
                        <?php else: ?>
                          <a href="<?= $item['url'] ?>"><?= $item['label'] ?></a>
                        <?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <?php if (isset($content)): ?>
              <?= $content ?>
            <?php endif; ?>
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <?php block('footer'); ?>
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="<?= asset('dist/js/adminlte.js') ?>"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!--end::Script-->
    <?php if (isset($extraJs)): ?>
      <?php foreach ($extraJs as $js): ?>
        <script src="<?= asset($js) ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>
  </body>
  <!--end::Body-->
</html>

