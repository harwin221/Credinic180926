<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>CrediNica@yield('subtitulo')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{asset('favicon.ico')}}" rel="icon">
    <link href="{{asset('assets/img/apple-touch-icon.png')}}" rel="apple-touch-icon">

    <!-- Vendor CSS Files -->
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/modern-theme.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/select2bs5/select2-bootstrap-theme.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/select2bs5/select2.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/toast.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/js/calculator/jquery.calculator.css')}}" />




    <style>


        div.is-calculator,
        span.is-calculator {
            position: relative
        }

        button.calculator-trigger {
            width: 25px;
            padding: 0
        }

        img.calculator-trigger {
            margin: 2px;
            vertical-align: middle
        }

        .calculator-keyentry {
            position: absolute;
            top: 0;
            right: 3px;
            width: 0;
            border: 0;
            background: 0 0
        }

        .calculator-inline {
            position: relative;
            border: 1px solid #CCC;
            background-color: #f4f4f4
        }

        .calculator-inline .calculator-close {
            display: 0
        }

        .calculator-rtl {
            direction: rtl
        }

        .calculator-prompt {
            clear: both;
            text-align: center
        }

        .calculator-prompt.ui-widget-header {
            margin: 2px
        }

        .calculator-result {
            clear: both;
            margin: 0;
            padding: 2px;
            text-align: right;
            background-color: #fff;
            border: 1px solid #CCC;
            font-size: 110%;
            overflow: hidden
        }

        .calculator-result span {
            display: inline-block;
            width: 100%
        }

        .calculator-result .calculator-formula {
            font-size: 60%
        }

        .calculator-focussed {
            background-color: #ffc
        }

        .calculator-row {
            clear: both;
            width: 100%
        }

        .calculator-space {
            float: left;
            margin: 2px;
            width: 28px
        }

        .calculator-half-space {
            float: left;
            margin: 1px;
            width: 14px
        }

        .calculator-row button {
            position: relative;
            float: left;
            margin: 0;
            padding: 0;
            height: 40px;
            width: 25%;
            line-height: 40px;
            text-align: center;
            cursor: pointer;
            background: #FFF;
            border: 1px solid #CCC
        }

        .calculator-inline .calculator-add,
        .calculator-inline .calculator-clear,
        .calculator-inline .calculator-divide,
        .calculator-inline .calculator-multiply,
        .calculator-inline .calculator-percent,
        .calculator-inline .calculator-plus-minus,
        .calculator-inline .calculator-subtract,
        .calculator-inline .calculator-undo {
            background: #EEE
        }

        .calculator-inline .calculator-equals {
            background: #bdea74
        }


        @-moz-document url-prefix() {

            .calculator-base,
                /* // Firefox  */
            .calculator-trig {
                text-indent: -3px
            }
        }

        .calculator-keystroke {
            display: none;
            width: 16px;
            height: 14px;
            position: absolute;
            left: -1px;
            top: -1px;
            color: #000;
            background-color: #fff;
            border: 1px solid #CCC;
            font-size: 80%
        }

        .calculator-angle .calculator-keystroke,
        .calculator-base .calculator-keystroke,
        .calculator-trig .calculator-keystroke {
            top: -2px;
            font-size: 95%
        }

        .calculator-keyname {
            width: 22px;
            font-size: 70%
        }


        .required:after {
            content: "*";
            color: red;
            margin-left: 5px;
        }

        .small-box {
            border-radius: .25rem;
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
            display: block;
            margin-bottom: 20px;
            position: relative
        }

        .small-box > .inner {
            padding: 10px
        }

        .small-box > .small-box-footer {
            background-color: rgba(0, 0, 0, .1);
            color: rgba(255, 255, 255, .8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10
        }

        .small-box > .small-box-footer:hover {
            background-color: rgba(0, 0, 0, .15);
            color: #fff
        }

        .small-box h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0 0 10px;
            padding: 0;
            white-space: nowrap
        }

        @media (min-width: 992px) {
            .col-lg-2 .small-box h3, .col-md-2 .small-box h3, .col-xl-2 .small-box h3 {
                font-size: 1.6rem
            }

            .col-lg-3 .small-box h3, .col-md-3 .small-box h3, .col-xl-3 .small-box h3 {
                font-size: 1.6rem
            }
        }

        @media (min-width: 1200px) {
            .col-lg-2 .small-box h3, .col-md-2 .small-box h3, .col-xl-2 .small-box h3 {
                font-size: 2.2rem
            }

            .col-lg-3 .small-box h3, .col-md-3 .small-box h3, .col-xl-3 .small-box h3 {
                font-size: 2.2rem
            }
        }

        .small-box p {
            font-size: 1rem
        }

        .small-box p > small {
            color: #f8f9fa;
            display: block;
            font-size: .9rem;
            margin-top: 5px
        }

        .small-box h3, .small-box p {
            z-index: 5
        }

        .small-box .icon {
            color: rgba(0, 0, 0, .15);
            z-index: 0
        }

        .small-box .icon > i {
            font-size: 90px;
            position: absolute;
            right: 15px;
            top: 15px;
            transition: -webkit-transform .3s linear;
            transition: transform .3s linear;
            transition: transform .3s linear, -webkit-transform .3s linear
        }

        .small-box .icon > i.fa, .small-box .icon > i.fab, .small-box .icon > i.fad, .small-box .icon > i.fal, .small-box .icon > i.far, .small-box .icon > i.fas, .small-box .icon > i.ion {
            font-size: 70px;
            top: 20px
        }

        .small-box .icon svg {
            font-size: 70px;
            position: absolute;
            right: 15px;
            top: 15px;
            transition: -webkit-transform .3s linear;
            transition: transform .3s linear;
            transition: transform .3s linear, -webkit-transform .3s linear
        }

        .small-box:hover {
            text-decoration: none
        }

        .small-box:hover .icon > i, .small-box:hover .icon > i.fa, .small-box:hover .icon > i.fab, .small-box:hover .icon > i.fad, .small-box:hover .icon > i.fal, .small-box:hover .icon > i.far, .small-box:hover .icon > i.fas, .small-box:hover .icon > i.ion {
            -webkit-transform: scale(1.1);
            transform: scale(1.1)
        }

        .small-box:hover .icon > svg {
            -webkit-transform: scale(1.1);
            transform: scale(1.1)
        }

        @media (max-width: 767.98px) {
            .small-box {
                text-align: center
            }

            .small-box .icon {
                display: none
            }

            .small-box p {
                font-size: 12px
            }
        }

        .small-box > .loading-img, .small-box > .overlay {
            height: 100%;
            left: 0;
            position: absolute;
            top: 0;
            width: 100%
        }

        .card .overlay, .info-box .overlay, .overlay-wrapper .overlay, .small-box .overlay {
            border-radius: .25rem;
            -ms-flex-align: center;
            align-items: center;
            background-color: rgba(255, 255, 255, .7);
            display: -ms-flexbox;
            display: flex;
            -ms-flex-pack: center;
            justify-content: center;
            z-index: 50
        }

        .card .overlay > .fa, .card .overlay > .fab, .card .overlay > .fad, .card .overlay > .fal, .card .overlay > .far, .card .overlay > .fas, .card .overlay > .ion, .card .overlay > .svg-inline--fa, .info-box .overlay > .fa, .info-box .overlay > .fab, .info-box .overlay > .fad, .info-box .overlay > .fal, .info-box .overlay > .far, .info-box .overlay > .fas, .info-box .overlay > .ion, .info-box .overlay > .svg-inline--fa, .overlay-wrapper .overlay > .fa, .overlay-wrapper .overlay > .fab, .overlay-wrapper .overlay > .fad, .overlay-wrapper .overlay > .fal, .overlay-wrapper .overlay > .far, .overlay-wrapper .overlay > .fas, .overlay-wrapper .overlay > .ion, .overlay-wrapper .overlay > .svg-inline--fa, .small-box .overlay > .fa, .small-box .overlay > .fab, .small-box .overlay > .fad, .small-box .overlay > .fal, .small-box .overlay > .far, .small-box .overlay > .fas, .small-box .overlay > .ion, .small-box .overlay > .svg-inline--fa {
            color: #343a40
        }

        .card .overlay.dark, .info-box .overlay.dark, .overlay-wrapper .overlay.dark, .small-box .overlay.dark {
            background-color: rgba(0, 0, 0, .5)
        }

        .card .overlay.dark > .fa, .card .overlay.dark > .fab, .card .overlay.dark > .fad, .card .overlay.dark > .fal, .card .overlay.dark > .far, .card .overlay.dark > .fas, .card .overlay.dark > .ion, .card .overlay.dark > .svg-inline--fa, .info-box .overlay.dark > .fa, .info-box .overlay.dark > .fab, .info-box .overlay.dark > .fad, .info-box .overlay.dark > .fal, .info-box .overlay.dark > .far, .info-box .overlay.dark > .fas, .info-box .overlay.dark > .ion, .info-box .overlay.dark > .svg-inline--fa, .overlay-wrapper .overlay.dark > .fa, .overlay-wrapper .overlay.dark > .fab, .overlay-wrapper .overlay.dark > .fad, .overlay-wrapper .overlay.dark > .fal, .overlay-wrapper .overlay.dark > .far, .overlay-wrapper .overlay.dark > .fas, .overlay-wrapper .overlay.dark > .ion, .overlay-wrapper .overlay.dark > .svg-inline--fa, .small-box .overlay.dark > .fa, .small-box .overlay.dark > .fab, .small-box .overlay.dark > .fad, .small-box .overlay.dark > .fal, .small-box .overlay.dark > .far, .small-box .overlay.dark > .fas, .small-box .overlay.dark > .ion, .small-box .overlay.dark > .svg-inline--fa {
            color: #ced4da
        }
    </style>

    <link rel="stylesheet" href="{{asset('assets/css/stepper.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/icheck3.0.1.css')}}">

    @yield('css')

    <!-- =======================================================
    * Template Name: NiceAdmin
    * Updated: May 30 2023 with Bootstrap v5.3.0
    * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
    * Author: BootstrapMade.com
    * License: https://bootstrapmade.com/license/
    ======================================================== -->
</head>

<body>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    @include('layouts.agentesLayouts.headerAgente')
</header><!-- End Header -->

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    @include('layouts.agentesLayouts.sidebarAgente')

{{--    <div class="sidebar-header" style="position: absolute;bottom: 30px;padding: 10px">--}}
{{--        <a href="{{ route('home') }}"><img class="logo" style="width: 200px" src="{{asset ('assets/img/LogoCrediNica.png')}}" /></a>--}}
{{--    </div>--}}
</aside>

<main id="main" class="main">
    {{-- Pagetitle eliminado por ser repetitivo --}}

    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                @include('utlisComponents.flashMessage')
                <div class="card">
                    <div class="card-body">
                        <br>
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="calc" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <span id="inlineCalc"></span>
            </div>
        </div>
    </div>

</main><!-- End #main -->

<!-- ======= Footer ======= -->
<footer id="footer" class="footer mt-auto py-3">
    <div class="container">
        @include('layouts.footer')
    </div>
</footer><!-- End Footer -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<script src="{{asset('assets/js/jquery3-5-0.min.js')}}"></script>
<script src="{{asset('assets/js/toast.js')}}"></script>

<!-- Vendor JS Files -->
<script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/vendor/tinymce/tinymce.min.js')}}"></script>

<!-- Template Main JS File -->
<script src="{{asset('assets/js/main.js')}}"></script>
<script src="{{asset('assets/js/mobile-fixes.js')}}?v={{time()}}"></script>
<script src="{{asset('assets/vendor/fontawesome-free-6.4.0-web/js/all.min.js')}}"></script>
<script src="{{asset('assets/js/sweetalert2.js')}}"></script>
<script src="{{asset('assets/js/axios.min.js')}}"></script>
<script src="{{asset('assets/js/stepper.js')}}"></script>

<script src="{{asset('assets/js/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/utils.js')}}"></script>
<script src="{{asset('assets/js/calculator/jquery.plugin.min.js')}}"></script>
<script src="{{asset('assets/js/calculator/jquery.calculator.min.js')}}"></script>
{{--<script src="{{asset('assets/js/select2/bootstrap.bundle.js')}}"></script>--}}


<script>

    $(document).ready(function () {
        // Limpiar backdrops fantasma al cargar
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({'overflow': '', 'padding-right': ''});
        $('.select2').select2({
            theme: 'bootstrap-5'
        });
        $("#inlineCalc").calculator({
            layout: ['_%+-CABS', '_7_8_9_/', '_4_5_6_*', '_1_2_3_-', '_0_._=_+'],
            showFormula: true
        });
    });

    function cleanupModalBackdrops() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        const openModals = document.querySelectorAll('.modal.show');

        if (openModals.length === 0) {
            backdrops.forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            return;
        }

        if (backdrops.length > openModals.length) {
            backdrops.forEach((backdrop, index) => {
                if (index >= openModals.length) {
                    backdrop.remove();
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', cleanupModalBackdrops);
    document.addEventListener('hidden.bs.modal', cleanupModalBackdrops);
    document.addEventListener('show.bs.modal', () => {
        setTimeout(cleanupModalBackdrops, 1);
    });

</script>

@yield('script')

</body>

</html>
