@extends('layouts.master')

@section('title')
    Carousel
@endsection

@section('topbar-title')
    Elements
@endsection

@section('css')
    <!-- slick-carousel css -->
    <link rel="stylesheet" href="{{ URL::asset('build/libs/slick-carousel/slick/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/slick-carousel/slick/slick.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Basic</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">These carousels are powered by <a href="http://kenwheeler.github.io/slick"
                            target="_blank">slick.js</a>. The example below is a basic initialization.</p>
                    <div class="slider single-item">
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-1.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-2.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Multiple Items</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Set number of slides to show for carousel by setting <code>slidesToShow</code>
                        property.</p>
                    <div class="slider multiple-items">
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-1.jpg') }}" class="card-img" alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-2.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Responsive Display</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Set slides to show for carousel by setting responsive property.</p>
                    <div class="slider responsive">
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-1.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-2.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Variable Width</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Set <code>variableWidth: true</code> to enabled width features and set
                        <code>variableWidth</code> property for width.
                    </p>
                    <div class="slider variable-width">
                        <div class="card mb-0" style="width: 200px;">
                            <img src="{{ URL::asset('build/images/small/img-1.jpg') }}" class="w-100 object-fit-cover"
                                alt="" style="height: 180px;">
                        </div>
                        <div class="card mb-0" style="width: 175px;">
                            <img src="{{ URL::asset('build/images/small/img-2.jpg') }}" class="w-100 object-fit-cover"
                                alt="" style="height: 180px;">
                        </div>
                        <div class="card mb-0" style="width: 150px;">
                            <img src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="w-100 object-fit-cover"
                                alt="" style="height: 180px;">
                        </div>
                        <div class="card mb-0" style="width: 300px;">
                            <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="w-100 object-fit-cover"
                                alt="" style="height: 180px;">
                        </div>
                        <div class="card mb-0" style="width: 225px;">
                            <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="w-100 object-fit-cover"
                                alt="" style="height: 180px;">
                        </div>
                        <div class="card mb-0" style="width: 125px;">
                            <img src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="w-100 object-fit-cover"
                                alt="" style="height: 180px;">
                        </div>

                    </div>
                </div>
            </div>
            <!-- end card -->

            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Fade</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Set <code>fade: true</code> to enabled fade effect and set <code>fade</code>
                        property for linear speed.</p>
                    <div class="slider slider-fade">
                        <div class="card mb-0">
                            <img src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="img-fluid"
                                alt="">
                        </div>
                        <div class="card mb-0">
                            <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="img-fluid"
                                alt="">
                        </div>
                        <div class="card mb-0">
                            <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="img-fluid"
                                alt="">
                        </div>
                        <div class="card mb-0">
                            <img src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="img-fluid"
                                alt="">
                        </div>

                    </div>
                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-6">

            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Adaptive Height</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Set <code>adaptiveHeight: true</code> to enabled height features and set
                        <code>adaptiveHeight</code> property for height.
                    </p>
                    <div class="slider one-time text-center">
                        <div>
                            <div class="card mb-2">
                                <img src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-2">
                                <img src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                            <h5>Look ma!</h5>
                        </div>
                        <div>
                            <div class="card mb-2">
                                <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                            <h5>Check
                                <br />this out!
                            </h5>
                        </div>
                        <div>
                            <div class="card mb-2">
                                <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                            <h5>Woo!</h5>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Center Mode</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Centered carousel, you can enable this feature by setting <code>centerMode:
                            true</code>.</p>
                    <div class="slider slick-slider-center">
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-2.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Lazy Loading</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">To use lazy loading, set a <code>data-lazy</code> attribute on your img tags and
                        leave off the src.</p>
                    <div class="slider lazy">
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-2.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->

            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Autoplay</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Set <code>autoplay: true</code> to enabled autoplay features and set
                        <code>autoplaySpeed</code> property for autoplay speed.
                    </p>
                    <div class="slider autoplay">
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-1.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-2.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->

            <div class="card">
                <div class="card-header card-header-bordered">
                    <h3 class="card-title">Slider Syncing</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Sync your carousels and make one of them as navigation, look the example.</p>
                    <div class="slider slider-for">
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                    </div>

                    <div class="slider slider-nav mt-3">
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-7.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-6.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-5.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-4.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                        <div>
                            <div class="card mb-0">
                                <img data-lazy src="{{ URL::asset('build/images/small/img-3.jpg') }}" class="card-img"
                                    alt="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <!-- slick-carousel js -->
    <script src="{{ URL::asset('build/libs/slick-carousel/slick/slick.min.js') }}"></script>

    <!-- slick-carousel init js -->
    <script src="{{ URL::asset('build/js/pages/slick-carousel.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
