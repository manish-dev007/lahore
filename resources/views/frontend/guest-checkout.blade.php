@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")
@section('content')
<div class="page-wrapper">
<main class="main">
<nav aria-label="breadcrumb" class="breadcrumb-nav mb-1">
   <div class="container">
      <ol class="breadcrumb">
         <li class="breadcrumb-item"><a href="index.html">Home</a></li>
         <li class="breadcrumb-item active" aria-current="page">Guest Checkout</li>
      </ol> 
   </div>
   <!-- End .container -->
</nav>

<?php
$final_total = 0;
$userSession = Session::get('id');
$adrSession = Session::get('guest_adr');
if (empty($adrSession)) {
  $adrSession = '0';
}
$uid = 0;
$user = DB::table('guest_users') 
    ->where('id', '=',  $userSession)
    ->first(); 

$total = 0; 
$Subtotal = 0;

if(isset($_COOKIE['cart_items_cookie'])){

  $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
         $cookie = stripslashes($cookie);
         $saved_cart_items = json_decode($cookie, true);

foreach($saved_cart_items as $key=>$value){

 $data = DB::table('product_details')
        ->select('product_details.*','brands.name','brands.slug','brands.logo_img')
        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
        ->where('product_details.status','=', 1)
        ->where('product_details.id','=',$key)
        ->get();

 $url = url('/').'/storage/app/watch_thumb/';


 $Subtotal = $data[0]->price * $value;
 $final_total += $data[0]->price * $value;

 $image =explode(',', $data[0]->thumb_img);

} 
}


?> 
<br>
<div class="container">
<div class="row">
<div class="col-lg-8">
 <div class="container">
    <div class="row row-sparse">
<input type="hidden" name="hiddenuId" value="<?= $userSession; ?>">

<div class="row">
                <h2 class="title mb-2 adr-title">Enter Delivery Address</h2>
                 <?php 
                   $guest_data = DB::table('shipping_adds')->where('uniq_id','=',$adrSession)->get();
                   //dd($data);
                 $totaladr = count($guest_data); if($totaladr > 0){ ?>
                    <div class="checkout_addr adr_block_checkout" style="width: 100%;">
                      <div class="row">
                        @foreach($guest_data as $data)
                        <div class="col-md-12">
                          <div class="col-md-6">
                              <div class="address_history_checkout_block">
                                  <label class="radio radio_adr">
                                    <input type="radio" name="is_addrsel" value="{{$data->id}}">
                                    <span class="checkround"></span>
                                  </label>

                                    <div class="chk-adr">
                                        <p class="adr_name a1">{{$data->name}} | {{$data->phone}}</p>
                                        <p class="adr_type">{{$data->adr_type}}</p>
                                        <p class="adr_adrs">{{$data->address_line}},{{$data->landmark}},{{$data->city}},{{$data->state}} - {{$data->pincode}}</p>
                                    </div>
                                </div>
                          </div>
                        </div>
                          @endforeach
                      </div>
                      
                      
                  </div>
                  <?php }else{ ?>
                <form class="add_guest_address" action="#" method="post" style="width: 100%;">
                    @csrf
                    <div class="col-md-4">
                      <label for="addr-email">Name <span class="required">*</span></label>
                      <input name="addr-name" type="text" class="form-input form-wide mb-2" id="addr-name" required="">
                    </div>
                    <input type="hidden" name="utype" value="guest">
                    <input type="hidden" name="uid" value="<?php echo $uid; ?>">

                    <div class="col-md-4">
                    <label for="addr-phone">Phone <span class="required">*</span></label>
                    <input name="addr-phone" type="text" class="form-input form-wide mb-2" id="addr-phone" required="">
                  </div>

                  <div class="col-md-4">
                    <label for="addr-email">Email <span class="required">*</span></label>
                    <input name="addr-email" type="email" class="form-input form-wide mb-2" id="addr-email" required="">
                  </div>

                    <div class="col-md-12">
                     <label for="addr-address">Full Address <span class="required">*</span></label>
                    <textarea name="addr-address" class="form-input form-wide mb-2" id="addr-address" required="">   </textarea>
                  </div> 

                    <div class="col-md-4">
                    <label for="addr-city">City <span class="required">*</span></label>
                    <input name="addr-city" type="text" class="form-input form-wide mb-2" id="addr-city" required="">
                  </div>

                    <div class="col-md-4">
                    <label for="addr-state">State <span class="required">*</span></label>
                    <input name="addr-state" type="text" class="form-input form-wide mb-2" id="addr-state" required="">
                  </div>
                  <div class="col-md-4">
                    <label for="addr-pincode">Pincode <span class="required">*</span></label>
                    <input name="addr-pincode" type="text" class="form-input form-wide mb-2" id="addr-pincode" required="">
                  </div>
                  <div class="col-md-6">
                    <label for="addr-landmark">Landmark (Optional)</label>
                    <input name="addr-landmark" type="text" class="form-input form-wide mb-2" id="addr-landmark">
                  </div>
                  <div class="col-md-6">
                    <label for="addr-landmark" style="width: 100%;">Address Type</label>
                    <label class="radio">Office
                      <input type="radio" checked="checked" name="is_company" value="office">
                      <span class="checkround"></span>
                    </label>
                    <label class="radio">Home
                      <input type="radio" name="is_company" value="home">
                      <span class="checkround"></span>
                    </label>
                  </div>

                    
                  <div class="col-md-6">
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary btn-md">Add Address</button>


                            @if(session()->has('success'))
                                    <div class=" alert alert-success">{{ session()->get('success') }}</div>
                                    @endif
                                    @if(session()->has('Error'))
                                    <div class=" alert alert-danger">{{ session()->get('Error') }}</div>
                                    @endif
                    </div>
                  </div>
                </form>
              <?php } ?>
              </div>
                  <p class="addrError"></p>
            </div>
        </div><!-- End .row -->
    </div>


<div class="col-lg-4">
<div class="cart-summary">
<h3>Summary</h3>
<table class="table table-totals tab_chkout">
   <tbody>
      <tr>
         <td>Subtotal</td>
         <td id="totalsubtRight">₹<b><?php echo $final_total; ?></b></td>
      </tr>
      <tr>
         <td>Tax</td>
         <td>₹0.00</td>
      </tr>
   </tbody>
   <tfoot>
      <tr>
         <td>Order Total</td>
         <td id="totalAmoRight">₹<b><?php echo $final_total; ?></b></td>
      </tr>

      <tr>
         <td><label class="radio">Cash on Delivery
            <input type="radio" name="check_pay_optino" value="cod">
              <span class="checkround"></span>
            </label>
            <label class="radio">Online Payment
            <input type="radio" name="check_pay_optino" value="online">
              <span class="checkround"></span>
            </label>
            <p class="payError"></p>
          </td>
         <td>&nbsp;</td>
      </tr>
   </tfoot>
</table>
<div class="checkout-methods">
   <a href="javascript:void(0);" class="btn btn-block btn-sm btn-primary btn_final_check">Proceed</a>
</div>




</div><!-- End .cart-summary -->
</div><!-- End .col-lg-4 -->
</div><!-- End .row -->
</div><!-- End .container -->
<div class="mb-6"></div><!-- margin -->
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