@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")

@section('content') 
   <div class="page-wrapper">
        <main class="main">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Address</li>
                    </ol>
                </div><!-- End .container -->
            </nav>

            <div class="container">
                <div class="row">
                    <div class="col-lg-9 order-lg-last dashboard-content">
                        
                        <h3>Address</h3>

                        <div class="row" id="myOrder">
                            <div class="col-md-12">
                                <div class="card" >
                                    <div class="card-header">
                                        Address List
                                        <a href="javascript:void(0);" class="adr-link" data-toggle="modal" data-target="#addressModal" class="card-edit">Add Address</a>
                                    </div><!-- End .card-header -->

                                    <div class="card-body adr_profile">
                                        <?php $totaladr = count($ShippingAdd); if($totaladr > 0){
                                          ?>
                                        @foreach($ShippingAdd as $data)
                                       <div class="address_history_checkout_block">
                                          <label class="radio radio_adr">
                                            <a href="javascript:void(0);" onclick="viewAddress(<?=$data->id; ?>);" class="edit_addr"><i class="fas fa-edit"></i></a>
                                            <a href="javascript:void(0);" onclick="deleteAddress(<?=$data->id; ?>);" class="del_addr"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                          </label>

                                            <div class="chk-adr">
                                                <p class="adr_name a1">{{$data->name}} | {{$data->phone}}</p>
                                                <p class="adr_type">{{$data->adr_type}}</p>
                                                <p class="adr_adrs">{{$data->address_line}},{{$data->landmark}},{{$data->city}},{{$data->state}} - {{$data->pincode}}</p>
                                            </div>
                                        </div>

                                    @endforeach
                                <?php }else{ ?>
                                    <p>No Address Found</p>
                               <?php } ?>

                                    </div>

                                </div><!-- End .card -->
                            </div><!-- End .col-md-6 -->

                        
                        </div>

                    </div><!-- End .col-lg-9 -->

                    <aside class="sidebar col-lg-3">
                        <div class="widget widget-dashboard">
                            <h3 class="widget-title">My Account</h3>

                            <ul class="list">
                                <li><a href="{{route('user-profile')}}">Account Information</a></li>
                                <li class="active"><a href="{{route('user-address')}}">Address Book</a></li>
                                <li><a href="{{route('user-orders')}}">My Orders</a></li>
                                <li><a href="{{route('change-password')}}">Change Password</a></li>
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