@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")

@section('content') 
   <div class="page-wrapper">
        <main class="main">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('index')}}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Orders</li>
                    </ol>
                </div><!-- End .container -->
            </nav>

            <div class="container">
                <div class="row">
                    <div class="col-lg-9 order-lg-last dashboard-content">
                        
                        <h3>Orders</h3>

                        <div class="row" id="myOrder">
                            <div class="col-md-12">
                                <div class="card" >
                                    <div class="card-header">
                                        Order History
                                    </div><!-- End .card-header -->

                                <div class="card-body">
                               
                               <?php $i=1; $totalodrs = count($userOrders); if($totalodrs > 0){
                                          ?>
                                          <table class="table">
                                              <thead>
                                                <tr>
                                                  <th scope="col">#</th>
                                                  <th scope="col">Invoice No.</th>
                                                  <th scope="col">Items</th>
                                                  <th scope="col">Amount</th>
                                                  <th scope="col">Staus</th>
                                                  <th scope="col">Payment Mode</th>
                                                  <th scope="col">Order Date</th>
                                                  <th scope="col">#</th>
                                                </tr>
                                              </thead>
                                              <tbody>
                                        @foreach($userOrders as $data)
                                            <?php $order_date=date('d M Y',strtotime($data->updated_at)); ?>
                                            
                                                <tr>
                                                  <th scope="row"><?= $i; ?></th>
                                                  <td>{{$data->invoice_no}}</td>
                                                  <td>{{$data->total_items}}</td>
                                                  <td>{{$data->total_amount}}</td>
                                                    @if($data->order_status == 0)         
                                                        <td>Placed</td>
                                                    @elseif($data->order_status == 1)   
                                                       <td>Ready to Dispatch</td>  
                                                    @elseif($data->order_status == 2)   
                                                       <td>Out for Delivery</td>  
                                                    @elseif($data->order_status == 3)   
                                                       <td>Delivered</td>    
                                                    @else
                                                       <td>Cancelled</td>   
                                                    @endif
                                                  <td>{{$data->payment_method}}</td>
                                                  <td><?= $order_date; ?></td>
                                                  <td>
                                                    <div class="dropdown-cart-action">
                                                    <a href="{{ route('OrderDetails', ['id'=>$data->invoice_no]) }} " class="btn btn-block btn-view">View</a>
                                                  </div>
                                                  </td>
                                                </tr>
                                            <?php $i++; ?>
                                        @endforeach
                                              </tbody>
                                            </table>
                                    <?php  }else{ ?>
                                        <h5>No Orders</h5>
                                  <?php  } ?>
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
                                <li><a href="{{route('user-address')}}">Address Book</a></li>
                                <li class="active"><a href="{{route('user-orders')}}">My Orders</a></li>
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