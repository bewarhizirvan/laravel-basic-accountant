<!DOCTYPE html>
<html lang="en">
<head>


    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">

    <title>
        {{ env('APP_NAME') }}
    </title>

    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet"/>

    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet"/>

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet"/>

    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.0') }}" rel="stylesheet"/>
    <link href="{{ asset('css/custom.css?v=2.0.11') }}" rel="stylesheet"/>

</head>

<body class="g-sidenav-show dark-version bg-gray-100">

@yield('sidenav')

<main class="main-content border-radius-lg ">

    @yield('navbar')


    <div class="container-fluid py-4">
        <div class="row ms-md-auto pe-md-3 d-flex align-items-center">
            {!! $title ?? '' !!}
        </div>

        @yield('content')

        <footer class="footer pt-3  ">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6 mb-lg-0 mb-4">
                        <div class="copyright text-center text-sm text-muted text-lg-start">
                            ©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>
                            ,
                            by
                            <a href="https://kurdistan.page" class="font-weight-bold" target="_blank">Kurdistan Page</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </div>


</main>

<!--   Core JS Files   -->
<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>

<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>

<script src="{{ asset('assets/js/argon-dashboard.min.js?v=2.0.0') }}"></script>
<script src="{{ asset('js/jquery-3.6.0.min.js?v=2.0.0') }}"></script>
<script type="application/javascript">
    // Delete JS
    function checkDelete(url, msg='') {
        if (confirm('The record Will be Deleted, is that Okay?')) {
            $.ajax({
                type: 'post',
                data: {_method: 'DELETE', _token: '{{ csrf_token() }}'},
                url: url,
                success: function(data) {
                    // do something
                    window.location.reload(true);
                }

            });
        }
    }

    function checkDeleteForm(element, name) {
        if (confirm('{ ' + name + ' } - Will be Deleted, is that Okay?')) {
            event.preventDefault(); element.closest('form').submit();
        }
    }
    // End of Delete JS
</script>
{!! $script ?? '' !!}
</body>

</html>
