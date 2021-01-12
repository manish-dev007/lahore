@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")

@section('content') 
   <div class="page-wrapper">
        <main class="main">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                    </ol>
                </div><!-- End .container -->
            </nav>

            <div class="container">
                <div class="row">
                    <div class="col-lg-9 order-lg-last dashboard-content">
                        
                        <h3>Change Password</h3>

                        <div class="row" id="myOrder">
                            <div class="col-md-8">
                                <form action="{{ route('changeUsrPassword') }}" method="POST" class="mb-1 chnge_password">
                                @csrf

                                <label for="login-oldpassword">Old Password <span class="required">*</span></label>
                                <input name="oldpassword" type="password" class="form-input form-wide mb-2" id="login-oldpassword" required="">
                                <label for="login-newpassword">New Password <span class="required">*</span></label>
                                <input name="newpassword" type="password" class="form-input form-wide mb-2" id="login-newpassword" required="">
                                <label for="login-confpassword">Confirm Password <span class="required">*</span></label>
                                <input name="confpassword" type="password" class="form-input form-wide mb-2" id="login-confpassword" required="">

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-success btn-md">Update</button>
                                    
                                </div><!-- End .form-footer -->

                                    @if(session()->has('success'))
                                                <div class=" alert alert-success">{{ session()->get('success') }}</div>
                                                @endif
                                                @if(session()->has('Error'))
                                                <div class=" alert alert-danger">{{ session()->get('Error') }}</div>
                                                @endif
                              
                            </form>
                            </div><!-- End .col-md-6 -->

                        
                        </div><!-- End .row -->

                    </div><!-- End .col-lg-9 -->

                    <aside class="sidebar col-lg-3">
                        <div class="widget widget-dashboard">
                            <h3 class="widget-title">My Account</h3>

                            <ul class="list">
                                <li><a href="{{route('user-profile')}}">Account Information</a></li>
                                <li><a href="{{route('user-address')}}">Address Book</a></li>
                                <li><a href="{{route('user-orders')}}">My Orders</a></li>
                                <li class="active"><a href="{{route('change-password')}}">Change Password</a></li>
                            </ul>
                        </div><!-- End .widget -->
                    </aside><!-- End .col-lg-3 -->
                </div><!-- End .row -->
            </div><!-- End .container -->

            <div class="mb-5"></div><!-- margin -->
        </main><!-- End .main -->

    
</div>
   <!--- Plugins JS File -->
    <script src="{{ URL::asset('public/frontend/assets/js/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('public/frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::asset('public/frontend/assets/js/plugins.min.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ URL::asset('public/frontend/assets/js/main.min.js') }}"></script>
@endsection

@section('extrascript')
@endsection