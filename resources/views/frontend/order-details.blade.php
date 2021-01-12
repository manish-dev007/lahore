@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")

@section('content') 

   <div class="page-wrapper">
        <main class="main">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('index')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{route('user-orders')}}">Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{$invoice_no}}</li>
                    </ol>
                </div><!-- End .container -->
            </nav>
@foreach($orderData as $data)
<?php
  $odr_status = '';
  if($data->order_status == 0){
    $odr_status = 'Placed';
  }
  if($data->order_status == 1){
    $odr_status = 'Dispatched';
  }
  if($data->order_status == 2){
    $odr_status = 'Out for Delivery';
  }
  if($data->order_status == 3){
    $odr_status = 'Delivered';
  }

?> 
            <div class="container">
                <div class="row">
                    <div class="col-lg-9 order-lg-last dashboard-content order_details">
                        
                        <h3>Order Details</h3>

                        <div class="row" id="myOrder"> <div class="col-md-12">
<div class="card" > <div class="card-body"> <div class="myaccountblock"> <h4
class="text-center"> Your Order <strong>(<span class="oprice">{{$invoice_no}}
</span>) </strong>has been <?= $odr_status; ?> successfully.</h4> <div
class="order-detaildel"> <div class="orderdeldet"> <div> <h6>{{
$data->total_items }} Items </h6> @foreach($orderAddress as $adrdata)
<h5 class="odr_addr">Address:</h5> <p> <h6>{{ $adrdata->name }} | {{ $adrdata->phone }}</h6>{{
$adrdata->address_line }},{{ $adrdata->city }},{{ $adrdata->state }} - {{
$adrdata->pincode }} (Landmark: {{ $adrdata->landmark }})</p> @endforeach
</div> <div class="buttoncancle">
                       
            <?php if ($data->order_status < 2) { ?>
             
            <button class="btn btn-theme" id="cancelorder" data-orno="<?php echo $data->invoice_no; ?>">Cancel order</button>
          <?php } ?>
                  </div>
      </div>
<?php if ($data->order_status < 2) { ?>
              <p>You can cancel the order until the order is out for delivery</p>
      <?php } ?>
    </div>
    
<?php $status_list = $odr_st = $order_date = $odr_id = '';
foreach($orderStatus as $odrstatus){
  $order_date=date('d M Y',strtotime($odrstatus->updated_at));
  $odr_st = $odrstatus->order_status;
  $odr_id = $odrstatus->order_id;
}

  
  $status_array= array('0'=>'Placed','1'=>'Processed','2'=>'Out for Delivery','3'=>'Completed');
  for($i=0;$i<sizeof($status_array);$i++){
      $status_class="progress__item--completed";
     if($odr_st==$i){
        $status_class="progress__item--active";
     }
      if($i==0){
           $sub_statement="Order id <b>".$odr_id."</b> will reach you on ".$order_date;
           $statement = 'You order has been Placed successfully';
      }

      if($i==1){
          $sub_statement="Ready to Dispatch";
           $statement = 'You order is Ready to Dispatch';
      }

      if($i==2){
          $sub_statement="Out for delivery";
           $statement = 'You order is out for Delivery';
      }

      if($i==3){
          $sub_statement="Your order is delivered successfully";
           $statement = 'You order has been delivered successfully';
      }
      $status_list.='<li class="progress__item '.$status_class.'">
                      <p class="progress__title">'.$statement.'</p>
                      <p class="progress__info">'.$sub_statement.'</p>
                    </li>';
  }

?>
    <div class="tabsection1">
      <div class="order-detailmain">
        @if($data->order_status != 4)         
        <div class="order-detailleft">
            <ul class="progressorder">
              <?= $status_list; ?>    
            </ul>
          </div>

    @else
      <div class="text-center">
         <img src="{{ URL::asset('public/frontend/assets/images/cancelled.png') }}" style="height: 60px;margin: 10px auto;" alt="Lahore - Order cancelled">
         <p> Your order has been cancelled. We would like to see you ordering your favorite Watches again.</p>
      </div>
      <style type="text/css">
        .order-detailright{
          margin: 30px auto;
          display: block;
        }
      </style>
    @endif

        <div class="order-detailright">
            <?php $totalitems = count($orderItems); if($totalitems > 0){ ?>
          <ul class="lsistblock">
            @foreach($orderItems as $odritems)
            <?php
              $pro_data = DB::table('product_details')
                    ->where('id','=',$odritems->item_id)
                    ->get();

             $url = url('/').'/storage/app/watch_thumb/';
             $image =explode(',', $pro_data[0]->thumb_img);
            ?>
            <li>
              <div class="cartproduct">
                <div class="cartproductimg">
                  <?php if($image[0] == NULL){ ?>
                    <img src="{{ URL::asset('public/frontend/assets/images/images.jpeg') }}" style="width: 60px; height: 60px;">
                  <?php }else{ ?>
                  <img src="<?php  echo $url.'/'.$image[0]; ?>" style="width: 60px; height: 60px;">
                  <?php } ?>
                </div>
                <div class="cartdetails">
                  <div class="item-desc">
                    <div class="item-name"><?= $pro_data[0]->model_no; ?> <b>(<?= $odritems->item_name; ?>)</b></div>
                    <div class="qnt"> Qty:<span><?= $odritems->item_qnty; ?></span> <span class="oprice">₹<?= $odritems->item_price; ?></span></div>
                  </div>
                </div>
              </div>
            </li>
            @endforeach
          </ul>
        <?php } ?>
          <div class="billdetailblock">
            <h4>Bill Details</h4>
            <ul>
              <li><label>Subtotal</label><span>₹<?= $data->order_subtotal; ?></span></li>
              <li><label>Delivery Charge(+)</label><span>₹0</span></li>
                            <!-- <li><label>Meazzy Wallet</label><span>19</span></li> -->
              <li class="totalblock"><label>Total</label><span>₹<?= $data->total_amount; ?></span></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <a class="btn btn-theme btn_back" href="{{route('user-orders')}}"><i class="fa fa-arrow-left" aria-hidden="true"></i>
 Go to Order History</a>
  </div>
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
            @endforeach

            <div class="mb-5"></div><!-- margin -->
        </main><!-- End .main -->

        <table>
          
        </table>

    
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