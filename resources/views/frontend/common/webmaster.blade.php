<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"> 
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Lahore Watch Co. @yield('title')</title>
<meta name="author" content="SW-THEMES">
<!-- <meta name="csrf-token" content="{{ csrf_token() }}"> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<style type="text/css">  
  .icon_whatsapp {
      background: url({{ URL::asset('public/frontend/assets/images/icon-whatsapp.svg') }})no-repeat center center;
  }
</style>

 
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ URL::asset('public/frontend/assets/images/icons/favicon.ico') }}">
<script type="text/javascript">
  var base_url = window.location.origin+'/lahore/';
</script>
<script type="text/javascript">
WebFontConfig = {
google: { families: [ 'Open+Sans:300,400,600,700,800','Poppins:300,400,500,600,700','Segoe Script:300,400,500,600,700' ] }
};
(function(d) {
var wf = d.createElement('script'), s = d.scripts[0];
wf.src = "{{ URL::asset('public/frontend/assets/js/webfont.js') }}";
wf.async = true;
s.parentNode.insertBefore(wf, s);
})(document); 
</script> 

<!-- Plugins CSS File -->
<link rel="stylesheet" href="{{ URL::asset('public/frontend/assets/css/bootstrap.min.css') }}">
<!-- Main CSS File -->
<link rel="stylesheet" href="{{ URL::asset('public/frontend/assets/css/style.min.css') }}">
<link rel="stylesheet" href="{{ URL::asset('public/frontend/assets/css/custom.css') }}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('public/frontend/assets/vendor/fontawesome-free/css/all.min.css') }}">
@section('linkfile')
@show
</head>
<body>
<div class="page-wrapper">
<header class="header"> 
    <div class="header_top text-uppercase">
                <div class="container">
            <div class="row" style="width: 100%;">
                <div class="row no-gutters d-flex justify-content-between w-100">
                    <div class="col contact_no">
                                                <ul>                        
                            <!-- <li>
                            <a class="position-relative mr-15" href="tel:+91 8725028886" title="Need help? Contact us"><span class="lahore_i_icons icon_phone"></span>+011 - 28729564</a>
                            </li> -->
                            <li class="whatsapp"><a id="wats" href="https://api.whatsapp.com/send?phone=919999998270&amp;text=Hi, I would like to speak to a luxury watch expert at Lahore Watch Co.." title="Need help? Whatsapp us" target="_blank"><span class="lahore_i_icons icon_whatsapp"></span>+91 9999998270</a></li>
                        </ul>                   </div>
                    <div class="col account_headerWrap text-right icon_hover">
                        <ul>
                            <li class="dropdown media-icons"> <a href="https://www.facebook.com/" target="_blank"><i class="icon-facebook"></i></a>
              </li>

                            <li class="dropdown media-icons"> <a href="https://www.facebook.com/" target="_blank"><i class="icon-instagram"></i></a>
              </li>
                         
                        </ul>
                    </div>
                </div>
            </div>
        </div>  
    </div>
<div class="header-middle">
<div class="container">
<div class="header-left">
<a href="{{ route('index') }}" class="logo">
<img src="{{ URL::asset('public/frontend/assets/images/logo.svg') }}" alt="Porto Logo">
</a>
</div><!-- End .header-left -->
<div class="header-center">
<div class="header-search">
<a href="#" class="search-toggle" role="button"><i class="icon-magnifier"></i></a>
<form action="{{ route('brandSearch') }}" method="get" autocomplete="off">
<div class="header-search-wrapper">
<input type="search" class="form-control searchItem" name="q" id="q" placeholder="Search...">
<input type="hidden" value="<?=isset($usersSession)?$usersSession:''?>" name="userSession">
<!--<div class="select-custom">
<select id="cat" name="brand_cat" class="text-uppercase">
<option value="">All Brand</option>
@foreach ($brand_cat as $item)
<option value="{{ $item->id }}">{{ $item->name }}</option>
@endforeach
</select>
</div>--><!-- End .select-custom -->
<button class="btn" type="submit"><i class="icon-magnifier"></i></button>
</div><!-- End .header-search-wrapper -->
<div class="search-autocomplete" style="display: none;">
    <ul>
    </ul>
</div>
</form>
</div><!-- End .header-search -->
</div><!-- End .headeer-center -->
<div class="header-right">
<button class="mobile-menu-toggler" type="button">
<i class="icon-menu"></i>
</button>

<div class="header-dropdown dropdown-expanded">
<div class="header-menu">
<ul>
<li><a href="{{ route('blog') }}">BLOG</a></li>
<li><a href="{{ route('contact') }}">Contact</a></li>
<li><a href="{{ route('about') }}">About Us</a></li>
<li class="dropdown cart-dropdown">

<?php
   if($usersSession != NULL ){

?>
    
 @foreach($usersData as $userData)
           
   <a href="{{$userData->id}}">{{$userData->name}} &nbsp; </a>

                <div class="dropdown-menu" >
                <div class="dropdownmenu-wrapper" >
                <div class="dropdown-cart-products">
                <div class="product">
                <h4 class="product-title">
                <a href="{{route('user-profile')}}"> Profile</a>
                </h4>
                </div>
                <div class="product">
                <h4 class="product-title">
                <a href="{{route('user-logout')}}">Logout</a>
                </h4>

                </div>
                </div><!-- End .cart-product -->
                </div><!-- End .dropdownmenu-wrapper -->
                </div><!-- End .dropdown-menu -->
                   
@endforeach

<?php
 }else{ 

?>
 <a href="{{ url('user-login') }}"> Login &nbsp;</a>
<?php
         }
?>

 </li>
</ul>
</div><!-- End .header-menu -->
</div><!-- End .header-dropown -->
<div class="dropdown cart-dropdown">
<a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-display="static">

<?php

$total = 0; 
$totalitem = 0;

 if(isset($_COOKIE['cart_items_cookie'])){

/*$cart_data =  json_decode($_COOKIE['cart_items_cookie']);*/

  $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
         $cookie = stripslashes($cookie);
         $saved_cart_items = json_decode($cookie, true);

foreach( $saved_cart_items as $key=>$value){

 $data = DB::table('product_details')
        ->select('product_details.*','brands.name','brands.slug','brands.logo_img')
        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
        ->where('product_details.status','=', 1)
        ->where('product_details.id','=',$key)
        ->get();

$totalitem += count($data);

} 
}

if($totalitem>0){
echo '<span class="cart-count">'.$totalitem.'</span>';
}
 else{
    echo '<span class="cart-count">0</span>';
}

?>

</a>

<div class="dropdown-menu" >
<div class="dropdownmenu-wrapper " style="    max-height: 350px;
    overflow: scroll;">

<?php
$total = 0; 
$totalitem = 0;



if(isset($_COOKIE['cart_items_cookie'])){ ?>

 <div class="dropdown_cart">
 
<?php 

$cart_data =  json_decode($_COOKIE['cart_items_cookie']);

foreach( $cart_data as $key=>$value){

 $data = DB::table('product_details')
                        ->select('product_details.*','brands.name as brand_name','brands.slug','collections.name')
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.id','=',$key)
                        ->get();

$m_data = explode(',', $data[0]->images);


?>

<div class="dropdown-cart-products prods-{{ $data[0]->id }}">
<div class="product">

<div class="product-details">
<h4 class="product-title">
<a href="#"></a>
</h4>
<!--  -->
<span class="cart-product-info">
     
<p>{{$data[0]->name}}</p> 

<!-- ///cart11// -->
<span class="cart-product-qty">{{$value}}</span>
x 
</span> <span>  {{ $data[0]->price}}</span>

</div><!-- End .product-details -->


<figure class="product-image-container">
<a href="" class="product-image">
  @if ($m_data[0] == null)
      <img src="{{ URL::asset('public/frontend/assets/images/images.jpeg') }}">
  @else
      <?php $thumbImg = explode(',',$m_data[0]);
      $c_pro = count($thumbImg); ?>
      @for($i=0; $i<$c_pro; $i++)
          @if ($i == 0)
              <img src="{{ asset('storage/app/watch_thumb/'.$thumbImg[0]) }}" alt="product">
          @endif
      @endfor
  @endif
</a>
<a href="javascript:void(0);" onclick="removeCart(<?php echo $data[0]->id; ?>);" id="btnRemove" class="btn-remove" title="Remove Product"><i class="icon-cancel"></i></a>

<input type="hidden" name="pid" value="{{ $data[0]->id }}">
</figure> 
</div><!-- End .product --> 
</div><!-- End .cart-product -->

<?php
$total += $data[0]->price * $value;
}

}else{
   ?>
   <h3>Cart Empty</h3>
   <a href="{{route('index')}}" class="btn btn-secondary">Shop Now</a>
 <?php } ?>
 </div>
<div class="totalAmount">
<?php
if($total>0){ 
?>
<div class="dropdown-cart-total">
  <span> ₹  <?=$total?></span>
  <span class="cart-total-price">Total</span>
</div>

<div class="dropdown-cart-action checkout-btn">

  <a id="checkoutBtn_liv" href="{{route('checkoutPage')}}" class="btn btn-block">Checkout</a>

</div>
<?php
}
?><!-- End .dropdown-cart-total -->
</div><!-- End .dropdown-cart-total -->
</div><!-- End .dropdownmenu-wrapper -->
</div><!-- End .dropdown-menu -->
</div><!-- End .dropdown -->
</div><!-- End .header-right -->
</div><!-- End .container -->
</div><!-- End .header-middle -->
<div class="header-bottom sticky-header">
<div class="container">
<nav class="main-nav">
<ul class="menu sf-arrows">
<li class="{{ Request::segment(1) == '' ? 'active' : '' }}"><a href="{{ route('index') }}" id="index">Home</a></li>
<li class="{{ Request::segment(1) == 'brand' ? 'active' : '' }}">
<a href="#" class="sf-with-ul">Brands</a>
<div class="megamenu megamenu-fixed-width">
<div class="row">
{{-- <div class="@if(count($brand_cat)<8) col-lg-3 @elseif(count($brand_cat)>8 && count($brand_cat)<16) col-lg-6 @elseif(count($brand_cat)>16 && count($brand_cat)<24) col-lg-9 @else col-lg-12 @endif"> --}}
@foreach ($brand_cat->chunk(8) as $item)
<div class="col-lg-6">
{{-- <div class="row"> --}}
<div class="col-lg-12">
<ul>
@foreach ($item as $itemCol)
<li><a href="{{ route('brand',['slug'=>$itemCol->slug]) }}">{{ $itemCol->name }}</a></li>
@endforeach
</ul>
</div><!-- End .col-lg-3 -->
{{-- </div><!-- End .row --> --}}
</div><!-- End .col-lg-8 -->
@endforeach
{{-- <div class="col-lg-4">
<div class="banner">
<a href="#">
<img src="{{ URL::asset('public/frontend/assets/images/menu-banner-2.jpg') }}" alt="Menu banner">
</a>
</div><!-- End .banner -->
</div><!-- End .col-lg-4 --> --}}
</div>
</div><!-- End .megamenu -->
</li>
<li class="megamenu-container ">
<a href="#" class="sf-with-ul">WATCH FINDER</a>
<div class="megamenu">
<div class="row">
<div class="col-lg-8">
<div class="row">
<div class="col-lg-4">
<div class="menu-title">
<a href="#">By Brand</a>
</div>
<ul style="overflow: auto; max-height:300px;">
@foreach ($brand_cat as $item)
<li><a href="{{ route('brand',['slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
@endforeach
</ul>
</div><!-- End .col-lg-4 -->
<div class="col-lg-4">
<div class="menu-title">
<a href="#">By Gender</a>
</div>
<ul>
<li><a href="{{ url('/product?gender=men') }}">Watches For Men</a></li>
<li><a href="{{ url('/product?gender=women') }}">Watches For Women</a></li>
<li><a href="{{ url('/product?gender=unisex') }}">Unisex Watches</a></li>
<li><a href="{{ url('/product?gender=couple') }}">Couple Watches</a></li>
</ul>
<div class="menu-title">
<a href="#">By Price</a>
</div>
<ul style="overflow: auto; max-height: 150px;">
<li><a href="{{ url('/product?price=2500-5000') }}">₹ 2,500 - ₹ 5,000</a></li>
<li><a href="{{ url('/product?price=5000-10000') }}">₹ 5,000 - ₹ 10,000</a></li>
<li><a href="{{ url('/product?price=10000-50000') }}">₹ 10,000 - ₹ 50,000</a></li>
<li><a href="{{ url('/product?price=50000-100000') }}">₹ 50,000 - ₹ 100,000</a></li>
<li><a href="{{ url('/product?price=100000-150000') }}">₹ 100,000 - ₹ 150,000</a></li>
<li><a href="{{ url('/product?price=150000-200000') }}">₹ 150,000 - ₹ 200,000</a></li>
<li><a href="{{ url('/product?price=200000-250000') }}">₹ 200,000 - ₹ 250,000</a></li>
<li><a href="{{ url('/product?price=250000-300000') }}">₹ 250,000 - ₹ 300,000</a></li>
<li><a href="{{ url('/product?price=300000-500000') }}">₹ 300,000 - ₹ 500,000</a></li>
<li><a href="{{ url('/product?price=500000-9000000') }}">₹ 500,000 and above</a></li>
</ul>
</div><!-- End .col-lg-4 -->
<div class="col-lg-4">
<div class="menu-title">
<a href="#">Top Collections</a>
</div>
<ul style="overflow: auto; max-height:300px;">
@foreach ($collection_cat as $item)
<li><a href="{{ route('watchFinder',['name'=>'collection', 'slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
@endforeach
</ul>
</div><!-- End .col-lg-4 -->
</div><!-- End .row -->
</div><!-- End .col-lg-8 -->
<div class="col-lg-4">
<div class="menu-title">
<a href="#">Material</a>
</div>
<ul style="overflow: auto; max-height:150px;">
@foreach ($strap_material_cat as $item)
<li><a href="{{ route('watchFinder',['name'=>'material', 'slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
@endforeach
</ul>
<div class="menu-title">
<a href="#">Feature</a>
</div>
<ul style="overflow: auto; max-height:150px;">
@foreach ($feature_cat as $item)
<li><a href="{{ route('watchFinder',['name'=>'feature', 'slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
@endforeach
</ul>
</div><!-- End .col-lg-4 -->
</div><!-- End .row -->
</div><!-- End .megamenu -->
</li>
<li class="{{ Request::segment(1) == 'accessories' ? 'active' : '' }}">
<a href="{{ route('accessories') }}">Accessories</a>
</li>
<li class="{{ Request::segment(1) == 'repair-and-services' ? 'active' : '' }}">
<a href="{{ route('repairServices') }}">Repair & Services</a>
</li>
<li class="{{ Request::segment(1) == 'store' ? 'active' : '' }}"><a href="{{ route('store') }}">Stores</a>
</li>
{{-- <li class="float-right buy-effect"><a href="https://1.envato.market/DdLk5" target="_blank"><span>buy Porto!</span></a></li>--}}

<li class="float-right {{ Request::segment(1) == 'sale' ? 'active' : '' }}"><a href="{{ route('sale') }}">Sale!</a></li>
</ul>
</nav>
</div><!-- End .header-bottom -->
</div><!-- End .header-bottom -->
</header><!-- End .header -->
@section('content')
@show
<footer class="footer">
<div class="footer-middle">
<div class="container">
<div class="row">
<div class="col-lg-3 col-md-6">
<div class="widget">
<h4 class="widget-title">Quick Links</h4>
<ul class="links">
@foreach ($footer_brands as $item)
<li class="text-uppercase"><a href="{{ route('brand',['slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
@endforeach
</ul>
</div><!-- End .widget -->
<div class="widget">
<h4 class="widget-title">Follow Us on:</h4>
<a href="https://www.facebook.com/lahorewatchhouse/" class="social-icon" target="_blank"><i class="icon-facebook"></i></a>
<a href="https://instagram.com/lahorewatchco?igshid=10rfaft7yzv46" class="social-icon" target="_blank"><i class="icon-instagram"></i></a>
</div><!-- End .social-icons -->
</div><!-- End .col-lg-3 -->
<div class="col-lg-3 col-md-6">
<div class="widget">
<h4 class="widget-title">Important Links</h4>
<ul class="links">
<li><a href="{{ route('about') }}">About Us</a></li>
<li><a href="{{ route('contact') }}">Contact Us</a></li>
<!-- <li><a href="{{ route('blog') }}">Blog</a></li> -->
<li><a href="{{ route('repairServices') }}">Repair & Services</a></li>
<li><a href="{{ route('sale') }}">Sale</a></li>
</ul>
</div><!-- End .widget -->
</div><!-- End .col-lg-2 -->
{{-- <div class="col-lg-5 col-md-6">
<div class="widget widget-newsletter">
<h4 class="widget-title">Subscribe newsletter</h4>
<p>Get all the latest information on Events,Sales and Offers. Sign up for newsletter today</p>
<form action="#">
<input type="email" class="form-control" placeholder="Email address" required>
<button type="submit" class="btn">Subscribe<i class="icon-angle-right"></i></button>
</form>
</div><!-- End .widget -->
</div><!-- End .col-lg-5 --> --}}
<div class="col-lg-3 col-md-6">
<div class="widget">
<ul class="contact-info">
<li>
<span class="contact-info-label">Store Address:</span>
Shop No. 112, 113, <br>
Gaffar Market, Block 23, <br>
Beadonpura, Karol Bagh, <br>
New Delhi, Delhi, Pin Code - 110005
</li>
<li>
<span class="contact-info-label">Phone:</span>Toll Free: <a href="tel:01128729564">011 - 28729564</a>
</li>
<li>
<span class="contact-info-label">Email:</span> <a href="mailto:Lahorewatchco1950@gmail.com">Lahorewatchco1950@gmail.com</a>
</li>
<li>
<span class="contact-info-label">Working Days/Hours:</span>
Closes  8:30 PM
</li>
</ul>
</div><!-- End .widget -->
</div><!-- End .col-lg-4 -->
<div class="col-lg-3 col-md-6">
<div class="widget">
<ul class="contact-info">
<li>
<span class="contact-info-label">Store Address:</span>
G - 4, South Extention, <br>
Part - I, <br>
New Delhi,<br>
Pin Code - 110049
</li>
<li>
<span class="contact-info-label">Phone:</span>Toll Free: <a href="tel:01128729564">011 - 28729564</a>
</li>
<li>
<span class="contact-info-label">Email:</span> <a href="mailto:Lahorewatchco1950@gmail.com">Lahorewatchco1950@gmail.com</a>
</li>
<li>
<span class="contact-info-label">Working Days/Hours:</span>
Closes  8:30 PM
</li>
</ul>
</div><!-- End .widget -->
</div><!-- End .col-lg-4 -->
</div><!-- End .row -->
</div><!-- End .container -->
</div><!-- End .footer-middle -->
{{-- <div class="container">
<div class="row">
<div class="col-12 pt-4 pb-3">
    <div class="footer-bottom">
        <div class="row">
            <div class="col-lg-4 col-sm-12 text-left">
                <p class="footer-copyright">Lahore Watches. &copy;  2018.  All Rights Reserved</p>
            </div>
            <div class="col-lg-4 col-sm-12 text-center">
                <div class="social-icons">
                    <a href="#" class="social-icon" target="_blank"><i class="icon-facebook"></i></a>
                    <a href="#" class="social-icon" target="_blank"><i class="icon-twitter"></i></a>
                    <a href="#" class="social-icon" target="_blank"><i class="icon-linkedin"></i></a>
                    </div><!-- End .social-icons -->
                </div>
                <div class="col-lg-4 col-sm-12 text-right">
                    <p class="footer-copyright">Lahore Watches. &copy;  2018.  All Rights Reserved</p>
                </div>
            </div>  
            </div><!-- End .footer-bottom -->
        </div>
    </div>
    </div><!-- End .containr --> --}}
    <div class="container">
        <div class="footer-bottom d-flex justify-content-between">
            <p class="footer-copyright">Lahore Watch Co. &copy;  2018.  All Rights Reserved</p>
            <p class="footer-copyright d-flex">Powered By <a href="http://www.doorsstudio.com/" style="color:#aaaaaa;" rel="nofollow" target="_blank" title="Digital Agency in India"> <img src="{{ URL::asset('public/frontend/assets/images/doors-logo.jpg') }}" class="ml-2" alt=""></a></p>
            </div><!-- End .footer-bottom -->
            </div><!-- End .containr -->
            </footer><!-- End .footer -->
            </div><!-- End .page-wrapper -->
            <div class="mobile-menu-overlay"></div><!-- End .mobil-menu-overlay -->
            <div class="mobile-menu-container">
                <div class="mobile-menu-wrapper">
                    <span class="mobile-menu-close"><i class="icon-cancel"></i></span>
                    <nav class="mobile-nav">
                        <ul class="mobile-menu">
                            <li class="{{ Request::segment(1) == '' ? 'active' : '' }}"><a href="{{ route('index') }}">Home</a></li>
                            <li class="{{ Request::segment(1) == 'brand' ? 'active' : '' }}">
                                <a href="#">Brands</a>
                                <ul>
                                    @foreach ($brand_cat as $item)
                                    <li><a href="{{ route('brand',['slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            <li class="<!- -->">
                                <a href="#">WATCH FINDER</a>
                                <ul>
                                    <li>
                                        <a href="#">By Brand</a>
                                        <ul>
                                            @foreach ($brand_cat as $item)
                                            <li><a href="{{ route('brand',['slug'=>$item->slug]) }}" class="text-uppercase">{{ $item->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#">By Gender</a>
                                        <ul>
                                            <li><a href="{{ url('/product?gender=men') }}">Watches For Men</a></li>
                                            <li><a href="{{ url('/product?gender=women') }}">Watches For Women</a></li>
                                            <li><a href="{{ url('/product?gender=unisex') }}">Unisex Watches</a></li>
                                            <li><a href="{{ url('/product?gender=couple') }}">Couple Watches</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#">By Price</a>
                                        <ul>
                                            <li><a href="{{ url('/product?price=2500-5000') }}">₹ 2,500 - ₹ 5,000</a></li>
                                            <li><a href="{{ url('/product?price=5000-10000') }}">₹ 5,000 - ₹ 10,000</a></li>
                                            <li><a href="{{ url('/product?price=10000-50000') }}">₹ 10,000 - ₹ 50,000</a></li>
                                            <li><a href="{{ url('/product?price=50000-100000') }}">₹ 50,000 - ₹ 100,000</a></li>
                                            <li><a href="{{ url('/product?price=100000-150000') }}">₹ 100,000 - ₹ 150,000</a></li>
                                            <li><a href="{{ url('/product?price=150000-200000') }}">₹ 150,000 - ₹ 200,000</a></li>
                                            <li><a href="{{ url('/product?price=200000-250000') }}">₹ 200,000 - ₹ 250,000</a></li>
                                            <li><a href="{{ url('/product?price=250000-300000') }}">₹ 250,000 - ₹ 300,000</a></li>
                                            <li><a href="{{ url('/product?price=300000-500000') }}">₹ 300,000 - ₹ 500,000</a></li>
                                            <li><a href="{{ url('/product?price=500000-9000000') }}">₹ 500,000 and above</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#">Top Collections</a>
                                        <ul>
                                            @foreach ($collection_cat as $item)
                                            <li><a href="{{ route('watchFinder',['name'=>'collection', 'slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#">Material</a>
                                        <ul>
                                            @foreach ($strap_material_cat as $item)
                                            <li><a href="{{ route('watchFinder',['name'=>'material', 'slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#">Feature</a>
                                        <ul>
                                            @foreach ($feature_cat as $item)
                                            <li><a href="{{ route('watchFinder',['name'=>'feature', 'slug'=>$item->slug]) }}">{{ $item->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li class="{{ Request::segment(1) == 'accessories' ? 'active' : '' }}"><a href="{{ route('accessories') }}">Accessories</a></li>
                            <li class="{{ Request::segment(1) == 'repair-and-services' ? 'active' : '' }}"><a href="{{ route('repairServices') }}">Repair & Services</a></li>
                            <li class="{{ Request::segment(1) == 'store' ? 'active' : '' }}"><a href="{{ route('store') }}">Stores</a></li>
                            <li class="{{ Request::segment(1) == 'blog' ? 'active' : '' }}"><a href="{{ route('blog') }}">Blog</a></li>
                            <li class="{{ Request::segment(1) == 'about-us' ? 'active' : '' }}"><a href="{{ route('about') }}">About Us</a></li>
                            <li class="{{ Request::segment(1) == 'contact-us' ? 'active' : '' }}"><a href="{{ route('contact') }}">Contact Us</a></li>
                            <li class="{{ Request::segment(1) == 'sale' ? 'active' : '' }}"><a href="{{ route('sale') }}">Sale!<span class="tip tip-hot">Hot!</span></a></li>
                        </ul>
                        </nav><!-- End .mobile-nav -->
                        <div class="social-icons">
                            <a href="https://www.facebook.com/lahorewatchhouse/" class="social-icon" target="_blank"><i class="icon-facebook"></i></a>
                            <a href="https://instagram.com/lahorewatchco?igshid=10rfaft7yzv46" class="social-icon" target="_blank"><i class="icon-instagram"></i></a>
                            </div><!-- End .social-icons -->
                            </div><!-- End .mobile-menu-wrapper -->
                            </div><!-- End .mobile-menu-container -->
                            <div class="newsletter-popup mfp-hide" id="newsletter-popup-form" style="background-image: url('public/frontend/assets/images/newsletter_popup_bg.jpg')">
                                <div class="newsletter-popup-content">
                                    <img src="{{ URL::asset('public/frontend/assets/images/logo-black.png') }}" alt="Logo" class="logo-newsletter">
                                    <h2>BE THE FIRST TO KNOW</h2>
                                    <p>Subscribe to the Porto eCommerce newsletter to receive timely updates from your favorite products.</p>
                                    <form action="#">
                                        <div class="input-group">
                                            <input type="email" class="form-control" id="newsletter-email" name="newsletter-email" placeholder="Email address" required>
                                            <input type="submit" class="btn" value="Go!">
                                            </div><!-- End .from-group -->
                                        </form>
                                        <div class="newsletter-subscribe">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="1">
                                                    Don't show this popup again
                                                </label>
                                            </div>
                                        </div>
                                        </div><!-- End .newsletter-popup-content -->
                                        </div><!-- End .newsletter-popup -->
                                        <!-- Add Cart Modal -->
                                        <div class="modal fade" id="addCartModal" tabindex="-1" role="dialog" aria-labelledby="addCartModal" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-body add-cart-box text-center">
                                                        <p>You've just added this product to the<br>cart:</p>
                                                        <h4 id="productTitle"></h4>
                                                        <img src="#" id="productImage" width="100" height="100" alt="adding cart image">
                                                        <div class="btn-actions">
                                                            <a href="#"><button class="btn-primary">Go to cart page</button></a>
                                                            <a href="#"><button class="btn-primary" data-dismiss="modal">Continue</button></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <a id="scroll-top" href="#top" title="Top" role="button"><i class="icon-angle-up"></i></a>
                                        <!-- Plugins JS File -->
                                        <script src="{{ URL::asset('public/frontend/assets/js/jquery.min.js') }}"></script>
                                        <script src="{{ URL::asset('public/frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
                                        <script src="{{ URL::asset('public/frontend/assets/js/plugins.min.js') }}"></script>
                                        <!-- Main JS File -->
                                        <script src="{{ URL::asset('public/frontend/assets/js/main.min.js') }}"></script>
                                        @section('extrascript')
                                        @show
                                    </body>
                                </html>

<div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add New Address</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="add_address" action="#" method="post" style="width: 100%;">
      @csrf
      <div class="modal-body">
          <div class="row">
              <div class="col-md-6">
                      <label for="addr-email">Name <span class="required">*</span></label>
                      <input name="addr-name" type="text" class="form-input form-wide mb-2" id="addr-name" required="">
                    </div>
                    <input type="hidden" name="hiddnAdrid" data-id="add">

                    <div class="col-md-6">
                    <label for="addr-phone">Phone <span class="required">*</span></label>
                    <input name="addr-phone" type="text" class="form-input form-wide mb-2" id="addr-phone" required="">
                  </div>
                    <input type="hidden" name="utype" value="user">
                    <input type="hidden" name="uid" value="0">

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
                    <input name="addr-landmark" type="text" class="form-input form-wide mb-2" id="addr-landmark" required="">
                  </div>
                  <div class="col-md-6">
                    <label for="addr-landmark" style="width: 100%;">Address Type</label>
                    <label class="radio">Office
                      <input type="radio" name="is_company" value="office">
                      <span class="checkround"></span>
                    </label>
                    <label class="radio">Home
                      <input type="radio" name="is_company" value="home">
                      <span class="checkround"></span>
                    </label>
                  </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
    </div>
  </div>
</div>


<script>

var result = '';
var proName = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
var a = btoa(proName); 
var charactersLength = proName.length;
for ( var i = 0; i < charactersLength; i++ ) {
    result += proName.charAt(Math.floor(Math.random() * charactersLength))+a;
}




function validatepromo() {

  var promoUrl = '{{route("validatepromo")}}';

  var coupon_code = $('input[name=coupon_code]').val();

  $.ajax({
    type: 'POST',
    url: promoUrl,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
      'coupon_code': coupon_code,
    },
    cache: false,
    success: function (data) {
      console.log(data);
      var duce = jQuery.parseJSON(data);
    

      if (duce.status == 'active') {

        alert(duce.cart_total);


        var total = duce.cart_total - duce.promo_discounted_amount;

        $('#totalAmo').html("<strong style='font-size: 20px; margin-left: 50px; color:red;'>₹ " + total + "</strong>");

      }else{
        alert('Please Login First to Continue..');
        window.location.href = 'user-login';
      }

    }
  });

}

 
var cart_url = '{{route("addToCart")}}';
/*//cart11//*/

function updateQnty(id,num_f) {
  var url = '{{route("updateQnty")}}';
  var userSession = $('input[name=userSession]').val();
  var qty = num_f;
  $.ajax({
    type: 'POST',
    url: url,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
      'id': id,
      'qty': qty,
      'userSession': userSession,
    },
    cache: false,
    success: function (data) {
      $('.prods-' + id).remove();
      var duce = jQuery.parseJSON(data);
      $('.subtotal_cart-'+id).text(duce.products[0].item_subtotal);
      $('#totalAmo strong b').text(duce.total_amount);
      $('#totalsubtRight b').text(duce.total_amount);
      $('#totalAmoRight b').text(duce.total_amount);
      var cart_items = '<div class="dropdown-cart-products prods-' + duce.products[0].itemid + '">  <div class="product">       <div class="product-details">    <h4 class="product-title">   <a href=""></a> </h4><p>' + duce.products[0].itemname + '</p>    <span class="cart-product-info">   <span class="cart-product-qty">' + duce.products[0].itemqty + '</span>    x   </span> <span>' + duce.products[0].itemprice + '</span>   </div>          <figure class="product-image-container">' +
        '<a href="" class="product-image">                   <img src="' + duce.products[0].pimage + '" alt="product">              </a>                <a href="javascript:void(0);" onclick="removeCart(' + duce.products[0].itemid + ');" id="btnRemove" class="btn-remove" title="Remove Product"><i class="icon-cancel"></i></a>              <input type="hidden" name="pid" value="' + duce.products[0].itemid + '">            </figure></div> </div>';

      $('.dropdown_cart').append(cart_items);

      $('.dropdown-cart-total').html('<span> ₹  ' + duce.total_amount + '</span><span class="cart-total-price">Total</span>');
    }
  });
}

function addToCart(proid = '', brand_name = '', qty = '') {

  var qty = $('input[name=qty]').val();
  if (typeof qty === "undefined") {
    qty = 1;
  }else{
    qty = qty;
  }
  console.log(qty);
  var userSession = $('input[name=userSession]').val();
  $.ajax({
    type: 'POST',
    url: cart_url,
    data: {
      "proid": proid,
      "qty": qty,
      "brand_name": brand_name,
      "userSession": userSession
    },
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    cache: false,
    success: function (data) {
      console.log(data);
      var duce = jQuery.parseJSON(data);
      $('.cart-count').text(duce.total_items);
      console.log(duce.products[0]);
      var cart_items = '<div class="dropdown-cart-products prods-' + duce.products[0].itemid + '">  <div class="product">       <div class="product-details">    <h4 class="product-title">   <a href=""></a> </h4><p>' + duce.products[0].itemname + '</p>    <span class="cart-product-info">   <span class="cart-product-qty">' + duce.products[0].itemqty + '</span>    x   </span> <span>' + duce.products[0].itemprice + '</span>   </div>          <figure class="product-image-container">' +
        '<a href="" class="product-image">                   <img src="' + duce.products[0].pimage + '" alt="product">              </a>                <a href="javascript:void(0);" onclick="removeCart(' + duce.products[0].itemid + ');" id="btnRemove" class="btn-remove" title="Remove Product"><i class="icon-cancel"></i></a>              <input type="hidden" name="pid" value="' + duce.products[0].itemid + '">            </figure></div> </div>';

      $('.dropdown_cart').append(cart_items);
      $('.dropdown_cart h4').remove();
      var btn_count = '<a href="javascript:void(0);" class="btn btn-success btn_cart">Added to Cart</a>';

      $('.action_btn_cart-'+proid).html(btn_count);
      $('#total_count').html(duce.total_items);
      //console.log(btn_count);
      $('.totalAmount').html('<div class="dropdown-cart-total"><span> ₹  ' + duce.total_amount + '</span><span class="cart-total-price">Total</span></div><div class="dropdown-cart-action checkout-btn"><a id="checkoutBtn_liv" href="{{url("/checkout-page")}}" class="btn btn-block">Checkout</a></div>');

    }
  })
}


function removeAllItem() {
  var userSession = $('input[name=userSession]').val();
  var url = '{{route("removeAllitem")}}';


  $.ajax({
    type: 'POST',
    url: url,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    cache: false,
    success: function (data) {
      //console.log(data);
      var duce = jQuery.parseJSON(data);
      if (duce.total_items == 0) {
        window.location.href = '';
      }
    }
  });
}

function removeItem(pid) {
  var userSession = $('input[name=userSession]').val();
  var url = '{{route("removeitem")}}';


  $.ajax({
    type: 'POST',
    url: url,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
      'pid': pid,
    },
    cache: false,
    success: function (data) {
      //console.log(data);
      var duce = jQuery.parseJSON(data);
      $('.cart-count').text(duce.total_items);
      $('.prods-' + pid).remove();
      $('.cart-item-' + pid).remove();
      $('#totalAmo b').text(duce.total_amount);

      $('#totalAmo strong b').text(duce.total_amount);
      $('#totalsubtRight b').text(duce.total_amount);
      $('#totalAmoRight b').text(duce.total_amount);
      //console.log(duce.total_amount);
      if (duce.total_amount > 0) {
        $('.totalAmount').html('<div class="dropdown-cart-total"><span> ₹  ' + duce.total_amount + '</span><span class="cart-total-price">Total</span></div>');
        $('.checkout-btn').html('<a id="checkoutBtn"  href="{{url("/add-to-cart")}}" class="btn btn-block">Checkout</a>');
      } else {
        $('.totalAmount').html('<div class="dropdown-cart-total"><span>Your cart is empty </span><a href="{{url("/")}}" class="btn btn-secondary">Shop Now</a></div>');
        $('.checkout-btn').html('');
      }
      if (duce.total_items == 0) {
        window.location.href = '';
      }
    }
  });
}

function removeCart(pid) {
  var userSession = $('input[name=userSession]').val();
  var url = '{{route("removeitem")}}';

  $.ajax({
    type: 'POST',
    url: url,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
      'pid': pid,
      'userSession': userSession
    },
    cache: false,
    success: function (data) {
      //console.log(data);
      var duce = jQuery.parseJSON(data);
      $('.cart-count').text(duce.total_items);
      $('.prods-' + pid).remove();
      //console.log(duce.total_amount);
      if (duce.total_amount > 0) {
        $('#totalAmo').text(duce.total_items);
        $('.totalAmount').html('<div class="dropdown-cart-total"><span> ₹  ' + duce.total_amount + '</span><span class="cart-total-price">Total</span></div>');
        $('.checkout-btn').html('<a id="checkoutBtn"  href="{{url("/add-to-cart")}}" class="btn btn-block">Checkout</a>');
      } else {
        $('.totalAmount').html('<div class="dropdown-cart-total"><span>Your cart is empty </span><a href="{{url("/")}}" class="btn btn-secondary">Shop Now</a></div>');
        $('.checkout-btn').html('');
      }

    },
  })
}

var num;var num_f = 0;

function prevDecr(id){
  num = parseInt($('.txt-'+id).val());
  if (num > 1) {
    $('.txt-'+id).val(num - 1);
    num_f = num - 1;
  }
  if (num == 2) {
    $('.btn-prev-'+id).prop('disabled', true);
  }
  if (num == 10) {
    $('.btn-after-'+id).prop('disabled', false);
  }
  updateQnty(id, num_f);
}

function AfterDecr(id){
  num = parseInt($('.txt-'+id).val());

  if (num < 10) {
    $('.txt-'+id).val(num + 1);
    num_f = num + 1;
  }
  if (num > 0) {
    $('.btn-prev-'+id).prop('disabled', false);
  }
  if (num == 9) {
    $('.btn-after-'+id).prop('disabled', true);
  }
  updateQnty(id, num_f);
}



$("#update-profile").submit(function(event){
      event.preventDefault();
      var uname = $("#updatename").val();
      var uphone = $("#mob_no").val();

      if(uname == ''){
        alert('Please Enter your Name..');
        $("#updatename").focus();
      }else if(uphone == ''){
        alert('Please Enter your Phone..');
        $("#mob_no").focus();
      }else{
        var form = $(this);
        var url = form.attr('action');
        
        $.ajax({
               type: "POST",
               url: url,
               data: form.serialize(),
               success: function(data)
               {
                    //console.log(data);
                    if (data == 'error') {
                        alert('Something Went Wrong..')
                    }else{
                         window.location.href = 'user-profile';
                    }
               }

           });
      }      

   });


$(".chnge_password").submit(function(event){
      event.preventDefault();
      var old_pwd = $("#login-oldpassword").val();
      var new_pwd = $("#login-newpassword").val();
      var cnew_pwd = $("#login-confpassword").val();

      if(old_pwd == ''){
        alert('Please Enter Old password..');
        $("#login-oldpassword").focus();
      }else if(new_pwd == ''){
        alert('Please Enter new password..');
        $("#login-newpassword").focus();
      }else if(cnew_pwd == ''){
        alert('Please Enter Confirm password..');
        $("#login-confpassword").focus();
      }else if(new_pwd != cnew_pwd){
        alert('Password and Confirm Password Should Match');
        $("#login-newpassword").focus();
      }else{
        var form = $(this);
        var url = form.attr('action');
        
        $.ajax({
               type: "POST",
               url: url,
               data: form.serialize(),
               success: function(data)
               {
                    console.log(data);
                    if (data == 'error') {
                        alert('Something Went Wrong..')
                    }else{
                         window.location.href = 'user-profile';
                    }
               }

           });
      }      

   });

  $(".add_address").submit(function(event){ 
      event.preventDefault();
        if (!$('input[name="is_company"]').is(":checked")) {
          alert('Please Select Address Type');
        }else{
          var fd = new FormData();
          var dataType = $('input[name=hiddnAdrid]').attr('data-id');
          if(dataType == 'add'){
              var url = '{{route("addAddressForm")}}';
          }
          if(dataType == 'update'){
              var url = '{{route("UpdateAddressForm")}}';
              fd.append('addr_id',$('input[name=hiddnAdrid]').val());
          }
          
        fd.append('addr_name',$('input[name=addr-name]').val());
        fd.append('addr_phone',$('input[name=addr-phone]').val());
        fd.append('u_type',$('input[name=utype]').val());
        fd.append('uid',$('input[name=uid]').val());
        fd.append('addr_address',$('textarea[name=addr-address]').val());
        fd.append('addr_city',$('input[name=addr-city]').val());
        fd.append('addr_state',$('input[name=addr-state]').val());
        fd.append('addr_pincode',$('input[name=addr-pincode]').val());
        fd.append('addr_landmark',$('input[name=addr-landmark]').val());
        fd.append('addr_is_company',$('input[name="is_company"]:checked').val());
        
        $.ajax({
               type: "POST", 
               url: url,
               data: fd,
               headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               contentType: false,
               processData: false,
               success: function(response)
               {

                    if (response == 'error') {
                        alert('Something Went Wrong..')
                    }else{
                         window.location.href = '';
                    }
               }

           });
      }      

   });

  $(".add_guest_address").submit(function(event){ 
      event.preventDefault();
        if (!$('input[name="is_company"]').is(":checked")) {
          alert('Please Select Address Type');
        }else{
          var fd = new FormData();
          var dataType = $('input[name=hiddnAdrid]').attr('data-id');
          var url = '{{route("addGuestAddressForm")}}';
          
        fd.append('addr_name',$('input[name=addr-name]').val());
        fd.append('addr_phone',$('input[name=addr-phone]').val());
        fd.append('addr_email',$('input[name=addr-email]').val());
        fd.append('u_type',$('input[name=utype]').val());
        fd.append('uid',$('input[name=uid]').val());
        fd.append('addr_address',$('textarea[name=addr-address]').val());
        fd.append('addr_city',$('input[name=addr-city]').val());
        fd.append('addr_state',$('input[name=addr-state]').val());
        fd.append('addr_pincode',$('input[name=addr-pincode]').val());
        fd.append('addr_landmark',$('input[name=addr-landmark]').val());
        fd.append('addr_is_company',$('input[name="is_company"]:checked').val());
        
        $.ajax({
               type: "POST", 
               url: url,
               data: fd,
               headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               contentType: false,
               processData: false,
               success: function(response)
               {

                    if (response == 'error') {
                        alert('Something Went Wrong..')
                    }else{
                         window.location.href = '';
                    }
               }

           });
      }      

   });

  function deleteAddress(id) {
    var confirm=window.confirm('Are you sure want to delete this Address ?');
    if (confirm) {
        var url = '{{route("deleteAddressForm")}}';
        var fd = new FormData();
        fd.append('addr_id',id);

        $.ajax({
               type: "POST", 
               url: url,
               data: fd,
               headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               contentType: false,
               processData: false,
               success: function(response)
               {

                    if (response == 'error') {
                        alert('Something Went Wrong..')
                    }else{
                        window.location.href = '';
                    }
               }

           });
    } else {
      window.location.href = '';
    }
  }

  function viewAddress(id) {
        var url = '{{route("viewAddressForm")}}';
        var fd = new FormData();
        fd.append('addr_id',id);

        $.ajax({
               type: "POST", 
               url: url,
               data: fd,
               headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               contentType: false,
               processData: false,
               success: function(response)
               {
                  $('#addressModal').modal('show'); 
                  $('input[name=addr-name]').val(response[0].name);
                  $('input[name=addr-phone]').val(response[0].phone);
                  $('textarea[name=addr-address]').val(response[0].address_line);
                  $('input[name=addr-city]').val(response[0].city);
                  $('input[name=addr-state]').val(response[0].state);
                  $('input[name=addr-pincode]').val(response[0].pincode);
                  $('input[name=addr-landmark]').val(response[0].landmark);
                  $("input[name=is_company][value='"+response[0].adr_type+"']").prop("checked",true);
                  $('input[name=hiddnAdrid]').val(response[0].id);
                  $('input[name=hiddnAdrid]').attr('data-id','update');
               }

           });
      }

      $('.btn_final_check').click(function(){
          if (!$('.adr_block_checkout input[name="is_addrsel"]').is(":checked")) {
            alert('*Please Select an Address..!');
            $('.addrError').text('*Please Select an Address..!');
          }else if (!$('.tab_chkout input[name="check_pay_optino"]').is(":checked")) {
            alert('*Please Select Payment Option..!');
            $('.payError').text('*Please Select Payment Option..!');
          }else if ($('.hiddenuId').val() == '') {
            alert('*Please Login First to Continue..!');
          }else{
            var address_id = $('.adr_block_checkout input[name="is_addrsel"]:checked').val();
            var pay_option = $('.tab_chkout input[name="check_pay_optino"]:checked').val();
            if (pay_option == 'cod') {
                var url = '{{route("CheckoutProduct")}}'; 
                var fd = new FormData();
                fd.append('addr_id',address_id);
                fd.append('pay_option',pay_option);

                $.ajax({
                       type: "POST", 
                       url: url,
                       data: fd,
                       headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                       contentType: false,
                       processData: false,
                       success: function(response)
                       {
                          //console.log(response);
                          if (response == 'success') {
                            removeAllItem();
                            setTimeout(function(){ window.location = 'user-profile'; }, 3000);
                          }else{
                            alert('Error Occured');
                          }
                       }

                     });
            }
            if (pay_option == 'online') {
              var amt = $('#totalsubtRight b').text();
              var tok = $('meta[name="csrf-token"]').attr('content');
              var url = '{{route("PaymentPage")}}?token='+result+'&amt='+amt+'&pay_option='+pay_option+'&address_id='+address_id+'&token='+tok;
              window.location = url;
            }
              
          }
      });

      $('#cancelorder').click(function(){
        var oid = $(this).attr('data-orno');
          if (window.confirm('Are you sure want to cancel this order ?')) {
            
            var url = '{{URL::to("/CancelOrder/")}}'+'/'+oid;
            window.location = url;
          }

      });


$('.searchItem').keyup(function(){
    var promoUrl = '{{route("searchbar")}}';
    var searchField = $(this).val();
    var item = '';
    if (searchField == '') {
      $('.searchItem').val('');
      $('.search-autocomplete').css('display','none');
    }else{
      item = searchField;
    }

    $.ajax({
        type: 'POST',
        url: promoUrl,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
          'item': item,
        },
        cache: false,
        success: function (data) {

            var res_f = JSON.parse(data);
            var resultdata = '';   
              var prnt = '';
              if (res_f.ResponseCode == '200') {
                $('.search-autocomplete').css('display','block');
                 var resp = res_f.ResultData.data;

                 $.each(resp, function(i, item) {    
                    var url = item.url;      
                    prnt += '<li title="'+item.name+'"><a href="'+base_url+url+'"><strong>'+item.name+'</strong></a></li>';
                  });
                 //console.log(prnt);
                 $('.search-autocomplete ul').html(prnt);
              }else{
                $('.search-autocomplete').css('min-height','auto');
                $('.search-autocomplete ul').html('<li title="No Product Found"><a href="javascript:void(0);"><strong>No Product Found</strong></a></li>');
              }
              
        }
    });
});
</script>