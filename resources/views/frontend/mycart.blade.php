@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")
@section('content')
<div class="page-wrapper">
<main class="main">
<nav aria-label="breadcrumb" class="breadcrumb-nav mb-1">
<div class="container">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="index.html">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
</ol>
</div><!-- End .container -->
</nav>
<div class="container"> 
<div class="row"> 
<div class="col-lg-8">
<div class="cart-table-container">
<table class="table table-cart">
<thead>
<tr>
<th class="product-c">Product Image</th>
<th class="product-c">Product</th>
<th class="price-col">Price</th>
<th class="qty-col">Qty</th>
<th>Subtotal</th>
</tr>
</thead>
<tbody>

<!-- cart -->

<!-- 
/*name
email
phone
price
model_no
warranty_period
images
thumb_img
brand_id
collection_id*/ -->

<?php
  $final_total = 0;
 $userSession = Session::get('id');

$user = DB::table('guest_users') 
		->where('id', '=',  $userSession)
		->first(); 


$total = 0; 
$Subtotal = 0;

 if(isset($_COOKIE['cart_items_cookie'])){

/*$cart_data =  json_decode($_COOKIE['cart_items_cookie']);*/

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

?> 

 <tr class="cart-item-<?=$key?>">
  <?php if($image[0] == NULL){ ?>
<td><img src="{{ URL::asset('public/frontend/assets/images/images.jpeg') }}" style="width: 60px; height: 60px;"></td>
<?php }else{ ?>
  <td><img src="<?php  echo $url.'/'.$image[0]; ?>" style="width: 60px; height: 60px;"></td>
<?php } ?>
<td><?=$data[0]->name?></td>
<td><?=$data[0]->price?></td>
  <td>
 <div class="product-count ">
  <?php if ($value > 1) { ?>
    <button class="button-count no-active btn-prev-<?=$key?>" onclick="prevDecr(<?=$key?>)">-</button>
  <?php }else{ ?>
<button class="button-count no-active btn-prev-<?=$key?>" onclick="prevDecr(<?=$key?>)" disabled>-</button>
  <?php } ?>
  
  <input name="qty" type="text" readonly class="number-product txt-<?=$key?>" value="<?php echo $value ; ?>">
  <button class="button-count btn-after-<?=$key?>" onclick="AfterDecr(<?=$key?>)" >+</button> 
</div></td> 


  <td class="subtotal_cart-<?=$key?>"><?php echo $Subtotal;?></td>
  <td colspan="4" class="clearfix">    

<div class="float-right">

<a href="javascript:void(0);" title="Remove product" id="removeItem" onclick="removeItem(<?=$key?>)"  class="btn-remove"><span class="sr-only">Remove</span></a>

</div> <!-- End .float-right  -->
</td>
</tr> 
<?php 
?>

<tr class="product-action-row">
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
<?php
} 
}


/* $data = DB::table('product_details')
        ->select('product_details.*','brands.name','brands.slug','brands.logo_img')
        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
        ->where('product_details.status','=', 1)
        ->where('id','=',$key)
        ->get();

    echo '<pre>';
  print_r($data);*/



/*$totalitem += count($data);*/

/* $url = url('/').'/storage/app/watch_thumb/';*/




/*


      $cookie = $_COOKIE['cart_items_cookie'];

       $cart_prod =  json_decode($cookie);


     foreach($cart_prod as $key=>$value){

                        $data = [

                            'product_id'=>$key,
                            'user_id'=>$user->id

                        ];

              $cart_insert =  DB::table('cart')->where('product_id','=',$key)->update($data); 


              } 

  }
 

 $user_cart_item = DB::table('cart')
 ->select('cart.id as cart_id','cart.pro_qty','guest_users.name','guest_users.phone' , 'guest_users.email' , 'product_details.*')
 ->join('guest_users','cart.user_id','=','guest_users.id')
 ->join('product_details','cart.product_id','=','product_details.id')
 ->where('user_id','=', $userSession)
 ->get();

$total = 0;
foreach($user_cart_item as $cart_items){

$subdomain = $cart_items->pro_qty*$cart_items->price;
$image=explode(',', $cart_items->thumb_img);
 $brand_item = DB::table('brands')
 ->where('id','=', $cart_items->brand_id)
 ->get();

  $collection_item = DB::table('collections')
 ->where('id','=', $cart_items->collection_id)
 ->get();

 $url = url('/').'/storage/app/watch_thumb/';

 $total += $subdomain;*/
?> 

<?php

?>
<tr class="product-action-row">
<td></td>
<td></td>
<td></td>
<td></td>

</tr>


<?php



/*else{

if(isset( $_COOKIE['cart_items_cookie'])){
$total = 0;

$cookie = json_decode($_COOKIE['cart_items_cookie']);

 $url = url('/').'/storage/app/watch_thumb/';
  foreach($cookie as $key => $cart_items){

$user_cart_item  = DB::table('product_details')
    ->select('product_details.*','brands.name as brand_name','brands.slug','collections.name')
    ->join('brands', 'product_details.brand_id', '=', 'brands.id')
    ->join('collections', 'product_details.collection_id', '=', 'collections.id')
    ->where('product_details.status','=', 1)
    ->where('product_details.id','=',$key)
    ->get();

 $total += $user_cart_item[0]->price;
 $image=explode(',', $user_cart_item[0]->thumb_img);

?>
<tr class="cart-item-<?=$key?> cookie_cart">
<td> <img src="<?=$url.'/'.$image[0]?>" style="width: 60px; height: 60px;"></td>

<td><?=$user_cart_item[0]->brand_name?></td>
<td><?=$user_cart_item[0]->name?></td>
<td><?=$user_cart_item[0]->price?></td>

<td><div class="product-count">
  <button  class="button-count no-active" disabled>-</button>
  <input name="qty" type="text" readonly class="number-product" value="1">
  <button class="button-count"  >+</button>
</div></td>

	<td colspan="4" class="clearfix">  

<div class="float-right"> 
<a href="#" onclick="updateQnty(<?php echo $user_cart_item[0]->id;?>)" title="Edit product" class="btn-edit"><span class="sr-only">Edit</span><i class="icon-pencil"></i></a>
<a href="#" title="Remove product" onclick="removeItem('<?=$key?>')"   class="btn-remove"><span class="sr-only">Remove</span></a>  
</div><!-- End .float-right -->
 
</td> 
  
<?php
  }
}
 ?>
</tr>
  <?php
}*/



?>
</tbody>
<tfoot>
<tr>
<td colspan="6" class="clearfix">
<div class="float-left">
<a href="{{url('/')}}" class="btn btn-outline-secondary">Continue Shopping</a>
</div><!-- End .float-left -->
<div class="float-right">
  <ul class="check-ul">
    <li><a href="javascript:void(0);" onclick="removeAllItem()" class="btn btn-outline-secondary btn-clear-cart">Clear Shopping Cart</a></li>
    <li style="    width: 220px;"><span style="font-size: 18px; margin-left: 15px;" class="btn-update-cart">Total</span>

     <span id="totalAmo"><strong style="font-size: 20px; margin-left: 50px;">₹ <b><?php echo $final_total; ?></b></strong></span>

   </li>
  </ul> 
</div><!-- End .float-right -->
</td>
</tr>
</tfoot> 
</table> 

<!-- <div class="promo-code">
 <form action="{{route('validatepromo')}}" method="post">
 	@csrf
<div class="input-group mb-3">

  <input name="coupon_code" type="text" class="form-control" placeholder="Coupon code" aria-label="Coupon code" aria-describedby="basic-addon2">
  <div class="input-group-append">
    <button type="submit" class="input-group-text btn btn-primary">Redeem coupon </button>
  </div>

</div>
</form>
</div> -->
</div><!-- End .cart-table-container -->



</div><!-- End .col-lg-8 -->
<div class="col-lg-4">
<div class="cart-summary">
<h3>Summary</h3>

<table class="table table-totals">
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
</tfoot>
</table>

<div class="checkout-methods">
<a href="{{ route('checkout') }}" class="btn btn-block btn-sm btn-primary">Go to Checkout</a>

</div><!-- End .checkout-methods -->
<br><br>
<div class="cart-discount">
<h3 style="font-size: 16px;">Apply Discount Code</h3>
<form href="javascript:void(0);" method="post">
    @csrf
<div class="input-group">
<input name="coupon_code" type="text" class="form-control form-control-sm" placeholder="Enter discount code"  required>
<div class="input-group-append">
<span class="btn btn-sm btn-primary" onclick="validatepromo()">Apply Discount</span>
</div>
</div>
</form>
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