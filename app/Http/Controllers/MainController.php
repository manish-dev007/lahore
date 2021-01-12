<?php

namespace App\Http\Controllers;
use Closure;
use Session; 
   
use Illuminate\Http\Request;
use Illuminate\Http\Response; 
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection as CollSupport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\UrlGenerator; 
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOrderConfirmationMail;
use Razorpay\Api\Api;
use App\Blog;
use App\BannerHeading;
use App\ProductDetail;
use App\SeoTag;
use App\Brand; 
use App\CaseMaterial;
use App\CaseShape;
use App\CaseSize;
use App\Collection;
use App\DialColour;
use App\Feature;
use App\Gender;
use App\GlassMaterial;
use App\Movement;
use App\MovementType;
use App\StrapColour;
use App\StrapMaterial;
use App\Discount;
use App\MainCategory;
use App\User;
use App\GuestUser;
use App\ShippingAdd; 

use DB;

class MainController extends Controller
{

public function userlogin()
{
        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = GuestUser::where('id', '=', $userSession)->get();

        return view('frontend.user-login',[
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'usersData' =>$userData,
            'usersSession'=>$userSession,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
        ]);
}

public function PaymentPage(Request $request)
    {      
        $amt = $request->amt;  
        $pay_option = $request->pay_option;
        $address_id = $request->address_id;
        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = DB::table('guest_users')->where('id', '=', $userSession)->get();


      return view('frontend.payWithRazorpay',[ 
        'seo' => $seo,
        'brand_cat' => $brand_cat,
        'usersSession'=>$userSession,
        'strap_material_cat' => $strap_material_cat,
        'feature_cat' => $feature_cat,
        'footer_brands' => $footer_brands,
        'collection_cat' => $collection_cat,
        'usersData' =>$userData,
        'payAmt' =>$amt,
        'pay_option' =>$pay_option,
        'address_id' =>$address_id,
        
      ]);    
    }

    public function payment()
    {
        //Input items of form
        $input = Input::all();
        //get API Configuration 
        $api = new Api(config('key.razor_key'), config('key.razor_secret'));
        //Fetch payment information by razorpay_payment_id
        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        if(count($input)  && !empty($input['razorpay_payment_id'])) {
            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount']));

            } catch (Exception $e) {
                return  $e->getMessage();
                Session::put('error',$e->getMessage());
                return view('frontend.checkout-page');
            }


            $this->SaveOrderData($input['address_id'],$input['payOption']);
            // Do something here for store payment details in database...
        }
        
        Session::put('success', 'Payment successful, your order will be despatched soon.');

        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = GuestUser::where('id', '=', $userSession)->get();
        $userShippingAdd = ShippingAdd::where('uid', '=', $userSession)->get();
        $userOrders =  DB::table('tbl_orders')->where('uid','=',$userSession)->get();

            return view('frontend.user-dashboard',
                [
                    'seo' => $seo,
                    'brand_cat' => $brand_cat,
                    'strap_material_cat' => $strap_material_cat,
                    'feature_cat' => $feature_cat,
                    'footer_brands' => $footer_brands,
                    'collection_cat' => $collection_cat,
                    'usersSession' => $userSession,
                    'usersData' => $userData,
                    'ShippingAdd'=>$userShippingAdd,
                    'userOrders'=>$userOrders
                  
                ]

        );
    }

public function userloginCheck(Request $request)
{  
        $useremail = $request->email;
        $userpassword = md5($request->password);

        $user = DB::table('guest_users')
        ->where('email', '=', $useremail)
        ->where('status', '=', '0')
        ->first(); 

if(!empty($user)){ 
if( $userpassword == $user->password){
  
    //$userData = Session::put('id',$user->id);
    $usersSession = Session::put('id',$user->id);
        $dbcart='';
        $cart_id = '';

        $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
        $cookie = stripslashes($cookie);
        $saved_cart_items = json_decode($cookie, true);

        $uidSession = Session::get('id'); 

        $dbcart=DB::table('cart')->where('user_id','=',$user->id)->get();
        $cart_id=isset($dbcart[0]->id)?$dbcart[0]->id:'';


        if(!empty($saved_cart_items)){

                $item_ids="";
                $item_qnty="";
                foreach($saved_cart_items as $key=>$value){
                    $cart_items[$key]=$value;
                    $item_ids.=$key.",";
                    $item_qnty.=$value.",";
                 }

                //echo $item_ids."<br>";
                //print_r($cart_items);
                $cart_data=array(
                    'user_id'=>$user->id,
                    'product_id'=>rtrim($item_ids,','),
                    'pro_qty'=>rtrim($item_qnty,',')
          
                );

              }else{
                $cart_data=array(
                    'user_id'=>$user->id,
                    'product_id'=>'',
                    'pro_qty'=>''
            
                );
             }

        if(empty($cart_id)){
                $cart_id_u = DB::table('cart')->insert($cart_data);
             }




         return redirect()->back();

}
 
else{
return redirect()->back()->with('Error','Error! Check Your Email Id or Password');
 }

} else{

 return redirect()->back()->with('Error','Error! Check Your Email Id or Password');

      }

}     
   
  public function userLogout(){

            Session::flush();
            Session::forget('id');

        // if(isset( $_COOKIE['cart_items_cookie'])){

        //  setcookie('cart_items_cookie', '', time() - 2592000, "/");
                   
        // }    

         return redirect('/');
    }



    public function sendverifyedMail(){

        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = GuestUser::where('id', '=', $userSession)->get();


        return view('frontend.email-varification',[
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'usersData' =>$userData,
            'usersSession'=>$userSession,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
        ]);

    } 

    public function userRegister(){

        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = GuestUser::where('id', '=', $userSession)->get();


        return view('frontend.user-register',[
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'usersData' =>$userData,
            'usersSession'=>$userSession,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
        ]);

    } 


    public function addAddressForm(Request $request)
    {
        $this->validate($request,[
            'addr_name'    => 'required',
            'addr_phone'  => 'required',
            'addr_address'  => 'required',
            'addr_city'  => 'required',
            'addr_state'  => 'required',
            'addr_pincode'  => 'required',
            'addr_is_company'  => 'required',
        ]);

        $userid = Session::get('id');


        $datas = array();
        $datas['uid']         = $userid;
        $datas['address_line']         = $request->addr_address;
        $datas['city']       = $request->addr_city;
        $datas['state']       = $request->addr_state;
        $datas['pincode']       = $request->addr_pincode;
        $datas['name']       = $request->addr_name;
        $datas['phone']       = $request->addr_phone;
        $datas['adr_type']       = $request->addr_is_company;
        $datas['landmark']       = $request->addr_landmark;
        $datas['created_at']       = date("Y-m-d H:i:s");
        $datas['updated_at']       = date("Y-m-d H:i:s");

        $insert_ad = DB::table('shipping_adds')->insert($datas);

        if(empty($insert_ad))
        {
            echo "error";
        }
        else
        {
            echo "success";
        }
    }

    public function UpdateAddressForm(Request $request)
    {
        $this->validate($request,[
            'addr_name'    => 'required',
            'addr_phone'  => 'required',
            'addr_address'  => 'required',
            'addr_city'  => 'required',
            'addr_state'  => 'required',
            'addr_pincode'  => 'required',
            'addr_is_company'  => 'required',
            'addr_id'  => 'required',
        ]);

        $userid = Session::get('id');
        $addrId = $request->addr_id;

        $datas = array();
        $datas['address_line']         = $request->addr_address;
        $datas['city']       = $request->addr_city;
        $datas['state']       = $request->addr_state;
        $datas['pincode']       = $request->addr_pincode;
        $datas['name']       = $request->addr_name;
        $datas['phone']       = $request->addr_phone;
        $datas['adr_type']       = $request->addr_is_company;
        $datas['landmark']       = $request->addr_landmark;
        $datas['created_at']       = date("Y-m-d H:i:s");
        $datas['updated_at']       = date("Y-m-d H:i:s");

        $insert_ad = DB::table('shipping_adds')->where('id', '=', $addrId)->where('uid', '=', $userid)->update($datas);

        if(empty($insert_ad))
        {
            echo "error";
        }
        else
        {
            echo "success";
        }
    }

    public function deleteAddressForm(){
     
        $id =  $_POST['addr_id'];

        $deleteAddr =  DB::table('shipping_adds')->where('id','=',$id)->delete();

        if($deleteAddr){

            echo 'success';
        }else{
            echo ' error';
        }

    }

    public function viewAddressForm(){
     
        $id =  $_POST['addr_id'];

        $deleteAddr =  DB::table('shipping_adds')->where('id','=',$id)->get();

        return $deleteAddr;

    }


public function updateProfile(Request $request)
    {
        $this->validate($request,[
            'name'    => 'required',
            'mob_no'  => 'required',
        ]);

        $userid = Session::get('id');


        $datas = array();
        $datas['name']         = $request->name;
        $datas['phone']       = $request->mob_no;

        $upd_data = DB::table('guest_users')
              ->where('id', $userid)
              ->update($datas);

        if(empty($upd_data))
        {
            return 'Error! Please Try Later.';
        }
        else
        {
            return 'profile Updated.';
        }
    }

public function changeUsrPassword(Request $request)
    {
        $this->validate($request,[
            'oldpassword'    => 'required',
            'newpassword'  => 'required',
        ]);

        $userid = Session::get('id');
        $oldpassword = $request->oldpassword;

        $user = DB::table('guest_users')
        ->where('id', '=', $userid)
        ->where('password', '=', md5($oldpassword))
        ->first(); 
        if(!empty($user)){ 
            $datas = array();
            $datas['password']       = md5($request->newpassword);

            $upd_data = DB::table('guest_users')
                  ->where('id', $userid)
                  ->update($datas);

            if(empty($upd_data))
            {
                return 'Error! Please Try Later.';
            }
            else
            {
                return 'Password Updated.';
            }
        }else{
            return "Old Password is Wrong..";
        }
    }

 public function userInsert(Request $request){

$seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
$brand_cat = Brand::where('status', '=', '1')->get();
$collection_cat = Collection::where('status', '=', true)->get();
$strap_material_cat = StrapMaterial::where('status', '=', true)->get();
$feature_cat = Feature::where('status', '=', true)->get();
$footer_brands = Brand::where('status', '=', true)->limit(5)->get();
$userSession = Session::get('id');

$userTable = new GuestUser;

$password = Hash::make($request->password);
 
$data = [ 
  'name'=> $request->name,
  'email'=>  $request->email,
  'password'=> $password,
  'password'=> $password,
  'phone' =>  $request->phone
];

$dI = DB::table('guest_users')->insertGetId($data);

if($dI){  

$lastId = DB::table('guest_users')->where('id','=',$dI)->get();


$email = '';

foreach($lastId as $email){

$email = $email->email;

}

    return view('frontend.email-varification',
        [
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'usersSession'=>$userSession,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
            'userEmail'=> $lastId
        ]
);

}else{ echo "error";}
 }


public function userDashboard(){

$seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
$brand_cat = Brand::where('status', '=', '1')->get();
$collection_cat = Collection::where('status', '=', true)->get();
$strap_material_cat = StrapMaterial::where('status', '=', true)->get();
$feature_cat = Feature::where('status', '=', true)->get();
$footer_brands = Brand::where('status', '=', true)->limit(5)->get();
$userSession = Session::get('id');
$userData = GuestUser::where('id', '=', $userSession)->get();
$userShippingAdd = ShippingAdd::where('uid', '=', $userSession)->get();
$userOrders =  DB::table('tbl_orders')->where('uid','=',$userSession)->get();

    return view('frontend.user-dashboard',
        [
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
            'usersSession' => $userSession,
            'usersData' => $userData,
            'ShippingAdd'=>$userShippingAdd,
            'userOrders'=>$userOrders
          
        ]

);
} 

public function userAddress(){

$seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
$brand_cat = Brand::where('status', '=', '1')->get();
$collection_cat = Collection::where('status', '=', true)->get();
$strap_material_cat = StrapMaterial::where('status', '=', true)->get();
$feature_cat = Feature::where('status', '=', true)->get();
$footer_brands = Brand::where('status', '=', true)->limit(5)->get();
$userSession = Session::get('id');
$userData = GuestUser::where('id', '=', $userSession)->get();
$userShippingAdd = ShippingAdd::where('uid', '=', $userSession)->get();
$userOrders =  DB::table('tbl_orders')->where('uid','=',$userSession)->get();

    return view('frontend.user-address',
        [
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
            'usersSession' => $userSession,
            'usersData' => $userData,
            'ShippingAdd'=>$userShippingAdd,
            'userOrders'=>$userOrders
          
        ]

);
} 

public function userOrders(){

$seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
$brand_cat = Brand::where('status', '=', '1')->get();
$collection_cat = Collection::where('status', '=', true)->get();
$strap_material_cat = StrapMaterial::where('status', '=', true)->get();
$feature_cat = Feature::where('status', '=', true)->get();
$footer_brands = Brand::where('status', '=', true)->limit(5)->get();
$userSession = Session::get('id');
$userData = GuestUser::where('id', '=', $userSession)->get();
$userShippingAdd = ShippingAdd::where('uid', '=', $userSession)->get();
$userOrders =  DB::table('tbl_orders')->where('uid','=',$userSession)->get();

    return view('frontend.user-orders',
        [
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
            'usersSession' => $userSession,
            'usersData' => $userData,
            'ShippingAdd'=>$userShippingAdd,
            'userOrders'=>$userOrders
          
        ]

);
} 

public function changePassword(){


$seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
$brand_cat = Brand::where('status', '=', '1')->get();
$collection_cat = Collection::where('status', '=', true)->get();
$strap_material_cat = StrapMaterial::where('status', '=', true)->get();
$feature_cat = Feature::where('status', '=', true)->get();
$footer_brands = Brand::where('status', '=', true)->limit(5)->get();
$userSession = Session::get('id');
$userData = GuestUser::where('id', '=', $userSession)->get();

      return view('frontend.change-password',
        [
            'seo' => $seo,
            'brand_cat' => $brand_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'collection_cat' => $collection_cat,
            'usersSession' => $userSession,
            'usersData' => $userData,
          
        ]

);

}



    public function frontendIndex(){

        $blogs = Blog::where('status', '=', true)->orderBy('created_at', 'asc')->limit(3)->get();
        $latest_data = ProductDetail::where('status', '=', true)->where('featured_status', '=', false)->where('main_category_id', '=', 1)->orderBy('price', 'asc')->limit(8)->get();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $featured_data = ProductDetail::where('status', '=', true)->where('featured_status', '=', true)->where('main_category_id', '=', 1)->orderBy('price', 'asc')->limit(8)->get();
        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $bannerheading = BannerHeading::where('page_name', '=', 'home')->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $discount = Discount::all();

         $userSession = Session::get('id'); 

         $userData = GuestUser::where('id', '=', $userSession)->get(); 

           //$userData = DB::table('guest_users')->get();
        
 
        if(count($featured_data)>0){
            foreach($featured_data as $item){
                $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                $discount_rate = (!empty($data)) ? $data : '' ;
                $featured[] = ['id'=>$item->id,'model_no'=>$item->model_no,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_rate,'brand_id'=>$item->brand_id];
            }
        }
        else{
            $featured = $featured_data;
        }

        if(count($latest_data)>0){
            foreach($latest_data as $item){
                $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                $discount_price = (!empty($data)) ? $data : '' ;
                $latest[] = ['id'=>$item->id,'model_no'=>$item->model_no,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price,'brand_id'=>$item->brand_id];
            }
        }
        else{
            $latest = $latest_data;
        }



        return view('frontend.index', [
            'blogs' => $blogs,
            'latest' => $latest,
            'featured' => $featured,
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'discount' => $discount,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
            'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function about(){
        $seo = SeoTag::where('page_name', '=', 'about')->firstOrFail();
        $brand_cat = Brand::where('status', '=', true)->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $bannerheading = BannerHeading::where('page_name', '=', 'about')->get();
          $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();

        return view('frontend.about', [
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function store(){
        $seo = SeoTag::where('page_name', '=', 'about')->firstOrFail();
        $brand_cat = Brand::where('status', '=', true)->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $bannerheading = BannerHeading::where('page_name', '=', 'store')->get();
          $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();

        return view('frontend.store', [
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function repairServices(){
        $seo = SeoTag::where('page_name', '=', 'about')->firstOrFail();
        $brand_cat = Brand::where('status', '=', true)->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $bannerheading = BannerHeading::where('page_name', '=', 'repair')->get();
          $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();

        return view('frontend.repair-services', [
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function accessories(Request $request){
        $brand_cat = Brand::where('status', '=', true)->get();
        $seo = SeoTag::where('page_name', '=', 'product')->firstOrFail();
        if(!empty($request->price)){
            $price = explode('-',$request->price);
            $products = ProductDetail::where('status', '=', true)->where('main_category_id', '!=', 1)->whereBetween('price', (array)$price)->orderBy('price', 'asc')->paginate(24);
        }
        else if($request->gender == 'men'){
            $brand_slug = Gender::where('gender', '=', 'Men')->firstOrFail();
            $products = ProductDetail::where('status', '=', true)->where('main_category_id', '!=', 1)->where('gender_id','=',$brand_slug->id)->orderBy('price', 'asc')->paginate(24);
        }
        else if($request->gender == 'women'){
            $brand_slug = Gender::where('gender', '=', 'Women')->firstOrFail();
            $products = ProductDetail::where('status', '=', true)->where('main_category_id', '!=', 1)->where('gender_id','=',$brand_slug->id)->orderBy('price', 'asc')->paginate(24);
        }
        else{
            $products = ProductDetail::where('status', '=', true)->where('main_category_id', '!=', 1)->orderBy('price', 'asc')->paginate(24);
        }
        $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        $gender = Gender::select('id', 'gender')->get();
        $movementtype = MovementType::where('status', '=', true)->select('id', 'name')->get();
        $maincategory = MainCategory::where('status', '=', true)->where('id', '!=', 1)->select('id', 'name')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $bannerheading = BannerHeading::where('page_name', '=', 'accessories')->get();
          $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();

        return view('frontend.accessories', [
            'brand_cat' => $brand_cat,
            'products' => $products,
            'brands' => $brands,
            'gender' => $gender,
            'movementtype' => $movementtype,
            'maincategory' => $maincategory,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]); 
    }

    public function product(Request $request){

        $brand_cat = Brand::where('status', '=', true)->get();
        $seo = SeoTag::where('page_name', '=', 'product')->firstOrFail();
          $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();
        if(!empty($request->price)){
            $gender_slug = [];
            $price = explode('-',$request->price);
            $bannerheading = BannerHeading::where('page_name', '=', 'all')->firstOrFail();
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->whereBetween('price', (array)$price)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current()."?price=".$request->price);
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all,$slug=null, $request->price);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($request->gender == 'men'){
            $gender_slug = Gender::where('gender', '=', 'Men')->firstOrFail();
            $bannerheading = BannerHeading::where('page_name', '=', 'men-watch')->firstOrFail();
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('gender_id','=',$gender_slug->id)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current()."?gender=".$request->gender);
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all,$gender_slug->slug);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($request->gender == 'women'){
            $gender_slug = Gender::where('gender', '=', 'Women')->firstOrFail();
            $bannerheading = BannerHeading::where('page_name', '=', 'women-watch')->firstOrFail();
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('gender_id','=',$gender_slug->id)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current()."?gender=".$request->gender);
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all,$gender_slug->slug);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($request->gender == 'unisex'){
            $gender_slug = Gender::where('gender', '=', 'Unisex')->firstOrFail();
            $bannerheading = BannerHeading::where('page_name', '=', 'unisex-watch')->firstOrFail();
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('gender_id','=',$gender_slug->id)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current()."?gender=".$request->gender);
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
               // if(!empty($products_all)){
                //     $products = $this->paginate($products_all,$gender_slug->slug);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($request->gender == 'couple'){
            $gender_slug = Gender::where('gender', '=', 'couple')->firstOrFail();
            $bannerheading = BannerHeading::where('page_name', '=', 'couple-watch')->firstOrFail();
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('gender_id','=',$gender_slug->id)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current()."?gender=".$request->gender);
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all,$gender_slug->slug);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else{
            $bannerheading = BannerHeading::where('page_name', '=', 'all')->firstOrFail();
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            foreach($products_data as $item){
                $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                $discount_price = (!empty($data)) ? $data : '' ;
                $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
            }
            $products = $products_all;
            $gender_slug = [];
        }

        $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        $casematerial = CaseMaterial::where('status', '=', true)->select('id', 'name')->get();
        $caseshape = CaseShape::where('status', '=', true)->select('id', 'name')->get();
        $casesize = CaseSize::where('status', '=', true)->select('id', 'case_size')->get();
        $collection = Collection::where('status', '=', true)->select('id', 'name')->get();
        $dialcolour = DialColour::where('status', '=', true)->select('id', 'name')->get();
        $feature = Feature::where('status', '=', true)->select('id', 'name')->get();
        $gender = Gender::select('id', 'gender', 'slug')->get();
        $glassmaterial = GlassMaterial::where('status', '=', true)->select('id', 'name')->get();
        $movement = Movement::where('status', '=', true)->select('id', 'name')->get();
        $movementtype = MovementType::where('status', '=', true)->select('id', 'name')->get();
        $strapcolour = StrapColour::where('status', '=', true)->select('id', 'name')->get();
        $strapmaterial = StrapMaterial::where('status', '=', true)->select('id', 'name')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $discount = Discount::all();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
          $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();

        // dd($products_data);
        // die;

        return view('frontend.product', [
            'brand_cat' => $brand_cat,
            'gender_slug' => $gender_slug,
            'products' => $products,
            'products_data' => $products_data,
            'brands' => $brands,
            'casematerial' => $casematerial,
            'caseshape' => $caseshape,
            'casesize' => $casesize,
            'collection' => $collection,
            'dialcolour' => $dialcolour,
            'feature' => $feature,
            'gender' => $gender,
            'glassmaterial' => $glassmaterial,
            'movement' => $movement,
            'movementtype' => $movementtype,
            'strapcolour' => $strapcolour,
            'strapmaterial' => $strapmaterial,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'discount' => $discount,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function watchFinder(Request $request, $name, $slug){
        $brand_cat = Brand::where('status', '=', true)->get();
        $seo = SeoTag::where('page_name', '=', 'brand')->firstOrFail();
        $casematerial = CaseMaterial::where('status', '=', true)->select('id', 'name')->get();
        $caseshape = CaseShape::where('status', '=', true)->select('id', 'name')->get();
        $casesize = CaseSize::where('status', '=', true)->select('id', 'case_size')->get();
        $collection = Collection::where('status', '=', true)->select('id', 'name')->get();
        $dialcolour = DialColour::where('status', '=', true)->select('id', 'name')->get();
        $feature = Feature::where('status', '=', true)->select('id', 'name')->get();
        $gender = Gender::select('id', 'gender')->get();
        $glassmaterial = GlassMaterial::where('status', '=', true)->select('id', 'name')->get();
        $movement = Movement::where('status', '=', true)->select('id', 'name')->get();
        $movementtype = MovementType::where('status', '=', true)->select('id', 'name')->get();
        $strapcolour = StrapColour::where('status', '=', true)->select('id', 'name')->get();
        $strapmaterial = StrapMaterial::where('status', '=', true)->select('id', 'name')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        $discount = Discount::all();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $brandHeading = [];
        $bannerheading = BannerHeading::where('page_name', '=', 'all')->get();
        $userSession = Session::get('id');
        $userData = GuestUser::where('id', '=', $userSession)->get();

        if($name=='material'){
            $material_slug = StrapMaterial::where('slug', '=', $slug)->firstOrFail();
            $brand_slug = [];
            $collection_slug = [];
            $feature_slug = [];
            $products_all = [];
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('strap_material_id','=',$material_slug->id)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($name=='collection'){
            $collection_slug = Collection::where('slug', '=', $slug)->firstOrFail();
            $brand_slug = [];
            $material_slug = [];
            $feature_slug = [];
            $products_all = [];
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('collection_id','=',$collection_slug->id)->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($name=='feature'){
            $feature_slug = Feature::where('slug', '=', $slug)->firstOrFail();
            $brand_slug = [];
            $material_slug = [];
            $collection_slug = [];
            $products_all = [];
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->whereRaw("find_in_set('".$feature_slug->id."',feature_id)")->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($name=='size'){
            $feature_slug = CaseSize::where('slug', '=', $slug)->firstOrFail();
            $brand_slug = [];
            $material_slug = [];
            $collection_slug = [];
            $products_all = [];
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->whereRaw("find_in_set('".$feature_slug->id."',case_size_id)")->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($name=='shape'){
            $feature_slug = CaseShape::where('slug', '=', $slug)->firstOrFail();
            $brand_slug = [];
            $material_slug = [];
            $collection_slug = [];
            $products_all = [];
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->whereRaw("find_in_set('".$feature_slug->id."',case_shape_id)")->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($name=='glass'){
            $feature_slug = GlassMaterial::where('slug', '=', $slug)->firstOrFail();
            $brand_slug = [];
            $material_slug = [];
            $collection_slug = [];
            $products_all = [];
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->whereRaw("find_in_set('".$feature_slug->id."',glass_material_id)")->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else if($name=='color'){
            $feature_slug = DialColour::where('slug', '=', $slug)->firstOrFail();
            $brand_slug = [];
            $material_slug = [];
            $collection_slug = [];
            $products_all = [];
            $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->whereRaw("find_in_set('".$feature_slug->id."',dial_colour_id)")->orderBy('price', 'asc')->paginate(24);
            $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                // if(!empty($products_all)){
                //     $products = $this->paginate($products_all);
                // }
                // else{
                    $products = $products_all;
                // }
            }
            else{
                $products = $products_data;
            }
        }
        else{}

        return view('frontend.brand', [
            'brand_cat' => $brand_cat,
            'brands' => $brands,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'products' => $products,
            'products_data' => $products_data,
            'feature_slug' => $feature_slug,
            'brand_slug' => $brand_slug,
            'material_slug' => $material_slug,
            'collection_slug' => $collection_slug,
            'casematerial' => $casematerial,
            'caseshape' => $caseshape,
            'casesize' => $casesize,
            'collection' => $collection,
            'dialcolour' => $dialcolour,
            'feature' => $feature,
            'gender' => $gender,
            'glassmaterial' => $glassmaterial,
            'movement' => $movement,
            'movementtype' => $movementtype,
            'strapcolour' => $strapcolour,
            'strapmaterial' => $strapmaterial,
            'discount' => $discount,
            'footer_brands' => $footer_brands,
            'brandHeading' => $brandHeading,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    } 

    public function brandSearch(Request $request){ 
        $brand_cat = Brand::where('status', '=', true)->get();
        // $bannerheading = BannerHeading::where('page_name', '=', 'brand')->get();
        $seo = SeoTag::where('page_name', '=', 'brand')->firstOrFail();
        $q = Input::get('q');
        $slug_data = Input::get('brand_cat');

        if($q != "" && $slug_data != "")
        { 
            $brandHeading = Brand::where('id', '=', $slug_data)->firstOrFail();
            $bannerheading = [];
            $products_data = ProductDetail::where('model_no', 'LIKE', '%' . $q . '%')
                                ->where('main_category_id', '=', 1)
                                ->orwhere('id', 'LIKE', '%' . $q . '%')
                                ->orwhere('price', 'LIKE', '%' . $q . '%')
                                ->orwhere('series', 'LIKE', '%' . $q . '%')
                                ->where('brand_id','=',$slug_data)
                                ->orderBy('price', 'asc')
                                ->paginate(24);
            $products_data->withPath(url()->current()."?q=".$q."&brand_cat=".$slug_data);
            if(!empty($products_data)){
                $products_all = [];
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                $products = $products_all;
            }
            else{
                $products = $products_data;
            }
        }
        else{ 
            $brandHeading = [];
            $bannerheading = BannerHeading::where('page_name', '=', 'all')->get();
            $products_data = ProductDetail::where('model_no', 'LIKE', '%' . $q . '%')
                                ->where('main_category_id', '=', 1)
                                ->orwhere('id', 'LIKE', '%' . $q . '%')
                                ->orwhere('price', 'LIKE', '%' . $q . '%')
                                ->orwhere('series', 'LIKE', '%' . $q . '%')
                                ->orderBy('price', 'asc')
                                ->paginate(24);
            $products_data->withPath(url()->current()."?q=".$q."&brand_cat=");
            if(!empty($products_data)){
                $products_all = [];
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                $products = $products_all;
            }
            else{
                $products = $products_data;
            }
        }
        $casematerial = CaseMaterial::where('status', '=', true)->select('id', 'name')->get();
        $caseshape = CaseShape::where('status', '=', true)->select('id', 'name')->get();
        $casesize = CaseSize::where('status', '=', true)->select('id', 'case_size')->get();
        $collection = Collection::where('status', '=', true)->select('id', 'name')->get();
        $dialcolour = DialColour::where('status', '=', true)->select('id', 'name')->get();
        $feature = Feature::where('status', '=', true)->select('id', 'name')->get();
        $gender = Gender::select('id', 'gender')->get();
        $glassmaterial = GlassMaterial::where('status', '=', true)->select('id', 'name')->get();
        $movement = Movement::where('status', '=', true)->select('id', 'name')->get();
        $movementtype = MovementType::where('status', '=', true)->select('id', 'name')->get();
        $strapcolour = StrapColour::where('status', '=', true)->select('id', 'name')->get();
        $strapmaterial = StrapMaterial::where('status', '=', true)->select('id', 'name')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        $discount = Discount::all();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        $userSession = Session::get('id');
        $userData = GuestUser::where('id', '=', $userSession)->get();

        return view('frontend.brand', [
            'brand_cat' => $brand_cat,
            'brands' => $brands,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'products' => $products,
            'products_data' => $products_data,
            'casematerial' => $casematerial,
            'caseshape' => $caseshape,
            'casesize' => $casesize,
            'collection' => $collection,
            'dialcolour' => $dialcolour,
            'feature' => $feature,
            'gender' => $gender,
            'glassmaterial' => $glassmaterial,
            'movement' => $movement,
            'movementtype' => $movementtype,
            'strapcolour' => $strapcolour,
            'strapmaterial' => $strapmaterial,
            'discount' => $discount,
            'footer_brands' => $footer_brands,
            'brandHeading' => $brandHeading,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function brand(Request $request, $slug){
        $brand_cat = Brand::where('status', '=', true)->get();
        $brand_slug = Brand::where('slug', '=', $slug)->firstOrFail();
        $seo = SeoTag::where('page_name', '=', 'brand')->firstOrFail();
        $casematerial = CaseMaterial::where('status', '=', true)->select('id', 'name')->get();
        $caseshape = CaseShape::where('status', '=', true)->select('id', 'name')->get();
        $casesize = CaseSize::where('status', '=', true)->select('id', 'case_size')->get();
        $collection = Collection::where('status', '=', true)->select('id', 'name')->get();
        $dialcolour = DialColour::where('status', '=', true)->select('id', 'name')->get();
        $feature = Feature::where('status', '=', true)->select('id', 'name')->get();
        $gender = Gender::select('id', 'gender')->get();
        $glassmaterial = GlassMaterial::where('status', '=', true)->select('id', 'name')->get();
        $movement = Movement::where('status', '=', true)->select('id', 'name')->get();
        $movementtype = MovementType::where('status', '=', true)->select('id', 'name')->get();
        $strapcolour = StrapColour::where('status', '=', true)->select('id', 'name')->get();
        $strapmaterial = StrapMaterial::where('status', '=', true)->select('id', 'name')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        $discount = Discount::all();
        $bannerheading = [];
        $brandHeading = Brand::where('slug', '=', $slug)->firstOrFail();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();

        $products_data = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('brand_id','=',$brand_slug->id)->orderBy('price', 'asc')->paginate(24);
        $products_data->withPath(url()->current());
            if(!empty($products_data)){
                foreach($products_data as $item){
                    $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                    $discount_price = (!empty($data)) ? $data : '' ;
                    $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
                }
                $products = $products_all;
            }
            else{
                $products = $products_data;
            }

        return view('frontend.brand', [
            'brand_cat' => $brand_cat,
            'brands' => $brands,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'products_data' => $products_data,
            'products' => $products,
            'brand_slug' => $brand_slug,
            'casematerial' => $casematerial,
            'caseshape' => $caseshape,
            'casesize' => $casesize,
            'collection' => $collection,
            'dialcolour' => $dialcolour,
            'feature' => $feature,
            'gender' => $gender,
            'glassmaterial' => $glassmaterial,
            'movement' => $movement,
            'movementtype' => $movementtype,
            'strapcolour' => $strapcolour,
            'strapmaterial' => $strapmaterial,
            'discount' => $discount,
            'footer_brands' => $footer_brands,
            'brandHeading' => $brandHeading,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function getBrand(Request $request){
        $products = ProductDetail::where('status', '=', true)->where('main_category_id', '=', 1)->where('brand_id','=',$request->id)->select('id','model_no','price','images')->get();

        return $products;
    }



    public function searchbar(){ 

        $response = array();
        $response['data']=array();

        $data = $_POST['item'];  
        if ($data == '') {
            $returnArr = array("ResponseCode"=>"401","Result"=>"true","ResponseMsg"=>"Product Not Found");
        }else{
            $products_data = ProductDetail::where('model_no', 'LIKE', '' . $data . '%')->select('model_no','id')->orderBy('model_no', 'ASC')->limit(10)->get();
            $brands = Brand::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();
            $collection = Collection::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();
            $case_shape = CaseShape::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();
            $case_size = CaseSize::where('case_size', 'LIKE', '' . $data . '%')->select('slug','case_size')->orderBy('case_size', 'ASC')->limit(10)->get();
            $case_materials = CaseMaterial::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();
            $glass_materials = GlassMaterial::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();
            $dial_colours = DialColour::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();
            $genders = Gender::where('gender', 'LIKE', '' . $data . '%')->select('slug','gender')->orderBy('gender', 'ASC')->limit(10)->get();
            $features = Feature::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();
            $strap_material = StrapMaterial::where('name', 'LIKE', '' . $data . '%')->select('slug','name')->orderBy('name', 'ASC')->limit(10)->get();

            
            if(count($products_data)>0){
                foreach ($products_data as $pr) {
                     $col=array();
                     $col['name']=$pr->model_no;
                     $col['url']='product/'.$pr->id;
                     array_push($response['data'], $col);
                }
            }
            if(count($brands)>0){
                foreach ($brands as $br) {
                     $col=array();
                     $col['name']=$br->name;
                     $col['url']='brand/'.$br->slug;
                     array_push($response['data'], $col);
                }
            }
            if(count($collection)>0){
                foreach ($collection as $cols) {
                     $col=array();
                     $col['name']=$cols->name;
                     $col['url']='watches/collection/'.$cols->slug;
                     array_push($response['data'], $col);
                }
            }

            if(count($case_shape)>0){
                foreach ($case_shape as $shap) {
                     $col=array();
                     $col['name']=$shap->name.' Shape';
                     $col['url']='watches/shape/'.$shap->slug;
                     array_push($response['data'], $col);
                }
            }

            if(count($case_size)>0){
                foreach ($case_size as $size) {
                     $col=array();
                     $col['name']=$size->case_size;
                     $col['url']='watches/size/'.$size->slug;
                     array_push($response['data'], $col);
                }
            }

            if(count($case_materials)>0){
                foreach ($case_materials as $matr) {
                     $col=array();
                     $col['name']='Case Material '.$matr->name;
                     $col['url']='watches/material/'.$matr->slug;
                     array_push($response['data'], $col);
                }
            }

            if(count($strap_material)>0){
                foreach ($strap_material as $st_mt) {
                     $col=array();
                     $col['name']='Strap Material '.$st_mt->name;
                     $col['url']='watches/material/'.$st_mt->slug;
                     array_push($response['data'], $col);
                }
            }
            if(count($glass_materials)>0){
                foreach ($glass_materials as $glas) {
                     $col=array();
                     $col['name']='Glass Material '.$glas->name;
                     $col['url']='watches/glass/'.$glas->slug;
                     array_push($response['data'], $col);
                }
            }
            if(count($dial_colours)>0){
                foreach ($dial_colours as $color) {
                     $col=array();
                     $col['name']='Watches in '.$color->name.' Color';
                     $col['url']='watches/color/'.$color->slug;
                     array_push($response['data'], $col);
                }
            }
            if(count($genders)>0){
                foreach ($genders as $gen) {
                     $col=array();
                     $col['name']='Watches for '.$gen->gender;
                     $col['url']='product?gender='.$gen->slug;
                     array_push($response['data'], $col);
                }
            }
            if(count($features)>0){
                foreach ($features as $feat) {
                     $col=array();
                     $col['name']=$feat->name;
                     $col['url']='watches/feature/'.$feat->slug;
                     array_push($response['data'], $col);
                }
            }

            if (empty($response['data'])) {
                $returnArr = array("ResponseCode"=>"401","Result"=>"true","ResponseMsg"=>"Product Not Found");
            }else{
                $returnArr = array("ResponseCode"=>"200","Result"=>"true","ResponseMsg"=>"Product List Get Successfully!!!","ResultData"=>$response);
            }


        }
            return json_encode($returnArr);


        // $data = $_POST['item'];  
        // if ($data == '') {
        //     $returnArr = '';
        //  }else{ 

        //     $products_data = ProductDetail::where('model_no', 'LIKE', '' . $data . '%')
        //                         ->where('main_category_id', '=', 1)
        //                         ->orwhere('id', 'LIKE', '' . $data . '%')
        //                         ->orwhere('price', 'LIKE', '' . $data . '%')
        //                         ->orwhere('series', 'LIKE', '' . $data . '%')
        //                         ->paginate(24);
        //         $products_all = [];
        //         foreach($products_data as $item){
        //             $products_all[] = ['id'=>$item->id,'model_no'=>$item->model_no,'brand_id'=>$item->brand_id,'price'=>$item->price];
        //         }
        //         $products = $products_all;
        //         if (empty($products)) {
        //             $returnArr = array("ResponseCode"=>"401","Result"=>"true","ResponseMsg"=>"Product Not Found");
        //         }else{
        //             $returnArr = array("ResponseCode"=>"200","Result"=>"true","ResponseMsg"=>"Product List Get Successfully!!!","ResultData"=>$products);
        //         }
        //     }
        //     echo json_encode($returnArr);
    }

    public function accfilters(Request $request){
        $userSession = Session::get('id');
        $cart_id = 0;
        $product_list = $this->accfilters_data($request);
        if(!empty($product_list)){
            foreach($product_list as $item){
                $data = $this->discount_price($item->product_id,$item->brand_id,$item->price,$item->gender_id);
                $discount_price = (!empty($data)) ? $data : '' ;

                $ar_val = []; 
                if(!empty($usersSession)){
                $cart_data = DB::table('cart')
                    ->select('cart.id as cart_id','cart.pro_qty','guest_users.name','guest_users.phone' , 'guest_users.email' , 'product_details.*')
                    ->join('guest_users','cart.user_id','=','guest_users.id')
                    ->join('product_details','cart.product_id','=','product_details.id')
                    ->where('user_id','=', $usersSession)
                    ->get();

                    foreach($cart_data as $value ){
                        $ar_val[] =  $value->id;
                    }                                             
                    
                }else{
                    if(isset($_COOKIE['cart_items_cookie'] )){
                        $cookies_d = json_decode($_COOKIE['cart_items_cookie']);
                        foreach($cookies_d as $key =>$value ){
                            $ar_val[] =  $key;
                        }                                             
                    }  
                }
                if(in_array($item->product_id,$ar_val) ){
                    $cart_id = 1;
                }else{
                    $cart_id = 0;
                }


                $products_all[] = ['product_id'=>$item->product_id,'cart_id'=>$cart_id,'name'=>$item->name,'model_no'=>$item->model_no,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
            }
            if(!empty($products_all)){
                $products = $products_all;
            }
            else{
                $products = [];
            }
        }
        else{
            $products = [];
        }
        // $discount = Discount::all();
        // $movement_type_group_by_data = ['movement_types' => $this->accfilters_group($request,'movement_type_id','movement_types.id','movement_types.name')];
        // $brand_group_by_data = ['brands' => $this->accfilters_group($request,'brand_id','brands.id','brands.name')];
        // $gender_group_by_data = ['genders' => $this->accfilters_group($request,'gender_id','genders.id','genders.gender')];

        return [
            $products,
            $product_list
            // $movement_type_group_by_data,
            // $gender_group_by_data,
            // $brand_group_by_data
        ];

    }

    public function filters(Request $request){
        $product_list = $this->filters_data($request);
            

        if(!empty($product_list)){
            foreach($product_list as $item){
                $data = $this->discount_price($item->product_id,$item->brand_id,$item->price,$item->gender_id);
                $discount_price = (!empty($data)) ? $data : '' ;

                $ar_val = []; 
                if(!empty($usersSession)){
                $cart_data = DB::table('cart')
                    ->select('cart.id as cart_id','cart.pro_qty','guest_users.name','guest_users.phone' , 'guest_users.email' , 'product_details.*')
                    ->join('guest_users','cart.user_id','=','guest_users.id')
                    ->join('product_details','cart.product_id','=','product_details.id')
                    ->where('user_id','=', $usersSession)
                    ->get();

                    foreach($cart_data as $value ){
                        $ar_val[] =  $value->id;
                    }                                             
                    
                }else{
                    if(isset($_COOKIE['cart_items_cookie'] )){
                        $cookies_d = json_decode($_COOKIE['cart_items_cookie']);
                        foreach($cookies_d as $key =>$value ){
                            $ar_val[] =  $key;
                        }                                             
                    }  
                }
                if(in_array($item->product_id,$ar_val) ){
                    $cart_id = 1;
                }else{
                    $cart_id = 0;
                }


                $products_all[] = ['product_id'=>$item->product_id,'cart_id'=>$cart_id,'model_no'=>$item->model_no,'name'=>$item->name,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
            }
            if(!empty($products_all)){
                $products = $products_all;
            }
            else{
                $products = [];
            }
        }
        else{
            $products = [];
        }
        



        // $pageList = $product_list->onEachSide(1)->links();
        $movement_group_by_data = ['movements' => $this->filters_group($request,'movement_id','movements.id','movements.name')];
        $collection_group_by_data = ['collections' => $this->filters_group($request,'collection_id','collections.id','collections.name')];
        $movement_type_group_by_data = ['movement_types' => $this->filters_group($request,'movement_type_id','movement_types.id','movement_types.name')];
        $case_size_group_by_data = ['case_sizes' => $this->filters_group($request,'case_size_id','case_sizes.id','case_sizes.case_size')];
        $case_shape_group_by_data = ['case_shapes' => $this->filters_group($request,'case_shape_id','case_shapes.id','case_shapes.name')];
        $case_material_group_by_data = ['case_materials' => $this->filters_group($request,'case_material_id','case_materials.id','case_materials.name')];
        $glass_material_group_by_data = ['glass_materials' => $this->filters_group($request,'glass_material_id','glass_materials.id','glass_materials.name')];
        $dial_colour_group_by_data = ['dial_colours' => $this->filters_group($request,'dial_colour_id','dial_colours.id','dial_colours.name')];
        $strap_material_group_by_data = ['strap_materials' => $this->filters_group($request,'strap_material_id','strap_materials.id','strap_materials.name')];
        $strap_colour_group_by_data = ['strap_colours' => $this->filters_group($request,'strap_colour_id','strap_colours.id','strap_colours.name')];
        $feature_group_by_data = ['features' => $this->filters_group($request,'feature_id','features.id','features.name')];
        
        // $brand_group_by_data = ['brands' => $this->filters_group($request,'brand_id','brands.id','brands.name')];
        // $gender_group_by_data = ['genders' => $this->filters_group($request,'gender_id','genders.id','genders.gender')];
        // $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        // $brand_cat = Brand::where('status', '=', true)->get();
        // $collection = Collection::where('status', '=', true)->select('id', 'name')->get();
        // $gender = Gender::select('id', 'gender', 'slug')->get();
        // $strapmaterial = StrapMaterial::where('status', '=', true)->select('id', 'name')->get();
        // $feature_cat = Feature::where('status', '=', true)->get();
        // $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        // $seo = SeoTag::where('page_name', '=', 'product')->firstOrFail();


        return [
            $products,
            $product_list,
            $movement_group_by_data,
            $collection_group_by_data,
            $movement_type_group_by_data,
            $case_size_group_by_data,
            $case_shape_group_by_data,
            $case_material_group_by_data,
            $glass_material_group_by_data,
            $dial_colour_group_by_data,
            $strap_material_group_by_data,
            $strap_colour_group_by_data,
            $feature_group_by_data
        ];
    }

    public function sale_filters(Request $request){
        $product_list = $this->sale_filters_data($request);

        if(!empty($product_list)){
            foreach($product_list as $item){
                $data = $this->discount_price($item->product_id,$item->brand_id,$item->price,$item->gender_id);
                $discount_price = (!empty($data)) ? $data : '' ;
                $products_all[] = ['product_id'=>$item->product_id,'model_no'=>$item->model_no,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_price];
            }
            if(!empty($products_all)){
                $products = $products_all;
            }
            else{
                $products = [];
            }
        }
        else{
            $products = [];
        }
        $movement_group_by_data = ['movements' => $this->sale_filters_group($request,'movement_id','movements.id','movements.name')];
        $collection_group_by_data = ['collections' => $this->sale_filters_group($request,'collection_id','collections.id','collections.name')];
        $movement_type_group_by_data = ['movement_types' => $this->sale_filters_group($request,'movement_type_id','movement_types.id','movement_types.name')];
        $case_size_group_by_data = ['case_sizes' => $this->sale_filters_group($request,'case_size_id','case_sizes.id','case_sizes.case_size')];
        $case_shape_group_by_data = ['case_shapes' => $this->sale_filters_group($request,'case_shape_id','case_shapes.id','case_shapes.name')];
        $case_material_group_by_data = ['case_materials' => $this->sale_filters_group($request,'case_material_id','case_materials.id','case_materials.name')];
        $glass_material_group_by_data = ['glass_materials' => $this->sale_filters_group($request,'glass_material_id','glass_materials.id','glass_materials.name')];
        $dial_colour_group_by_data = ['dial_colours' => $this->sale_filters_group($request,'dial_colour_id','dial_colours.id','dial_colours.name')];
        $strap_material_group_by_data = ['strap_materials' => $this->sale_filters_group($request,'strap_material_id','strap_materials.id','strap_materials.name')];
        $strap_colour_group_by_data = ['strap_colours' => $this->sale_filters_group($request,'strap_colour_id','strap_colours.id','strap_colours.name')];
        $feature_group_by_data = ['features' => $this->sale_filters_group($request,'feature_id','features.id','features.name')];
        // $brand_group_by_data = ['brands' => $this->sale_filters_group($request,'brand_id','brands.id','brands.name')];
        // $gender_group_by_data = ['genders' => $this->sale_filters_group($request,'gender_id','genders.id','genders.gender')];

        return [
            $products,
            $product_list,
            $movement_group_by_data,
            $collection_group_by_data,
            $movement_type_group_by_data,
            $case_size_group_by_data,
            $case_shape_group_by_data,
            $case_material_group_by_data,
            $glass_material_group_by_data,
            $dial_colour_group_by_data,
            $strap_material_group_by_data,
            $strap_colour_group_by_data,
            $feature_group_by_data
        ];

            // $gender_group_by_data,
            // $brand_group_by_data,
    } 

    public function productDetail($id){
        $brand_cat = Brand::where('status', '=', true)->get();
        $product = ProductDetail::where('id','=',$id)->firstOrFail();
        $feature_id = Feature::whereIN('id',explode(',',$product->feature_id))->get();
        $featured_data = ProductDetail::where('status', '=', true)->where('featured_status', '=', true)->where('id','!=',$id)->orderBy('price', 'asc')->limit(6)->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $discount = Discount::all();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
          $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();

        $discount_price = $this->discount_price($product->id,$product->brand_id,$product->price,$product->gender_id);

        if(count($featured_data)>0){
            foreach($featured_data as $item){
                $data = $this->discount_price($item->id,$item->brand_id,$item->price,$item->gender_id);
                $discount_rate = (!empty($data)) ? $data : '' ;
                $featured[] = ['id'=>$item->id,'model_no'=>$item->model_no,'price'=>$item->price,'thumb_img'=>$item->thumb_img,'discount_price'=>$discount_rate,'brand_id'=>$item->brand_id];
            }
        }
        else{
            $featured = $featured_data;
        }

        return view('frontend.product-detail', [
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'feature_id' => $feature_id,
            'featured' => $featured,
            'discount' => $discount,
            'footer_brands' => $footer_brands,
            'discount_price' => $discount_price,
             'usersData' =>$userData,
            'usersSession'=>$userSession,
            'product' => $product
        ]);
    }

    public function sale(){
        $brand_cat = Brand::where('status', '=', true)->get();
        $seo = SeoTag::where('page_name', '=', 'product')->firstOrFail();
        $discount = Discount::all();
        $brands = Brand::where('status', '=', true)->select('id', 'name')->get();
        $casematerial = CaseMaterial::where('status', '=', true)->select('id', 'name')->get();
        $caseshape = CaseShape::where('status', '=', true)->select('id', 'name')->get();
        $casesize = CaseSize::where('status', '=', true)->select('id', 'case_size')->get();
        $collection = Collection::where('status', '=', true)->select('id', 'name')->get();
        $dialcolour = DialColour::where('status', '=', true)->select('id', 'name')->get();
        $feature = Feature::where('status', '=', true)->select('id', 'name')->get();
        $gender = Gender::select('id', 'gender')->get();
        $glassmaterial = GlassMaterial::where('status', '=', true)->select('id', 'name')->get();
        $movement = Movement::where('status', '=', true)->select('id', 'name')->get();
        $movementtype = MovementType::where('status', '=', true)->select('id', 'name')->get();
        $strapcolour = StrapColour::where('status', '=', true)->select('id', 'name')->get();
        $strapmaterial = StrapMaterial::where('status', '=', true)->select('id', 'name')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $bannerheading = BannerHeading::where('page_name', '=', 'sale')->get();

        $products = $this->discount_find_out();
        if(!empty($products)){
            $products;
        }
        else{
            $products = [];
        }

        return view('frontend.sale', [
            'brand_cat' => $brand_cat,
            'products' => $products,
            'brands' => $brands,
            'casematerial' => $casematerial,
            'caseshape' => $caseshape,
            'casesize' => $casesize,
            'collection' => $collection,
            'dialcolour' => $dialcolour,
            'feature' => $feature,
            'gender' => $gender,
            'glassmaterial' => $glassmaterial,
            'movement' => $movement,
            'movementtype' => $movementtype,
            'strapcolour' => $strapcolour,
            'strapmaterial' => $strapmaterial,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'discount' => $discount,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
            'seo' => $seo
        ]);
    }

    public function blog(){
        $brand_cat = Brand::where('status', '=', true)->get();
        $blogs = Blog::where('status', '=', true)->latest()->paginate(5);
        $latests = Blog::where('status', '=', true)->latest()->limit(5)->get();
        $seo = SeoTag::where('page_name', '=', 'blog')->firstOrFail();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();

        return view('frontend.blog', [
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'blogs' => $blogs,
            'latests' => $latests,
            'footer_brands' => $footer_brands,
            'seo' => $seo
        ]);
    }

    public function blogDetail($slug){
        $blog = Blog::where('slug', '=', $slug)->firstOrFail();
        $latests = Blog::where('status', '=', true)->where('slug', '!=', $slug)->latest()->limit(5)->get();
        $brand_cat = Brand::where('status', '=', true)->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();

        return view('frontend.blog-view', [
            'blog' => $blog,
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'latests' => $latests
        ]);
    }

    public function contact(){
        $seo = SeoTag::where('page_name', '=', 'contact')->firstOrFail();
        $brand_cat = Brand::where('status', '=', true)->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $bannerheading = BannerHeading::where('page_name', '=', 'contact')->get();
         $userSession = Session::get('id');
         $userData = GuestUser::where('id', '=', $userSession)->get();
     


        return view('frontend.contact', [
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands,
            'bannerheading' => $bannerheading,
             'usersData' =>$userData,
             'usersSession'=>$userSession,
            'seo' => $seo
        ]);
    }

    public function filters_data(Request $request){
        $brands = $request->brands;
        $casematerial = $request->casematerial;
        $caseshape = $request->caseshape;
        $casesize = $request->casesize;
        $collection = $request->collection;
        $dialcolour = $request->dialcolour;
        $feature = $request->feature;//implode(',',$request->feature);
        $gender = $request->gender;
        $glassmaterial = $request->glassmaterial;
        $movement = $request->movement;
        $movementtype = $request->movementtype;
        $strapcolour = $request->strapcolour;
        $strapmaterial = $request->strapmaterial;
        $price = $request->price;//explode('-',$request->price);

        $respo = DB::table('product_details')
        ->select(
            'product_details.id as product_id',
            'product_details.model_no',
            'brands.name',
            'product_details.price',
            'product_details.thumb_img',
            'product_details.gender_id',
            'product_details.brand_id'
        )
        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
        // ->join('collections', 'product_details.collection_id', '=', 'collections.id')
        // ->join('movements', 'product_details.movement_id', '=', 'movements.id')
        // ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
        // ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
        // ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
        // ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
        // ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
        // ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
        // ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
        // ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
        // ->join('genders', 'product_details.gender_id', '=', 'genders.id')
        // ->join('features', 'product_details.feature_id', '=', 'features.id')
        ->where('product_details.status','=', 1)
        ->where('product_details.main_category_id', '=', 1)
        ->where(function($query) use ($brands) {
            if(is_array($brands)){
                return $query->whereIn('product_details.brand_id', (array)$brands);
            }
        })
        ->where(function($query) use ($collection) {
            if(is_array($collection)){
                return $query->whereIn('product_details.collection_id', (array)$collection);
            }
        })
        ->where(function($query) use ($movement) {
            if(is_array($movement)){
                return $query->whereIn('product_details.movement_id', (array)$movement);
            }
        })
        ->where(function($query) use ($movementtype) {
            if(is_array($movementtype)){
                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
            }
        })
        ->where(function($query) use ($casesize) {
            if(is_array($casesize)){
                return $query->whereIn('product_details.case_size_id', (array)$casesize);
            }
        })
        ->where(function($query) use ($caseshape) {
            if(is_array($caseshape)){
                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
            }
        })
        ->where(function($query) use ($casematerial) {
            if(is_array($casematerial)){
                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
            }
        })
        ->where(function($query) use ($dialcolour) {
            if(is_array($dialcolour)){
                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
            }
        })
        ->where(function($query) use ($strapmaterial) {
            if(is_array($strapmaterial)){
                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
            }
        })
        ->where(function($query) use ($strapcolour) {
            if(is_array($strapcolour)){
                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
            }
        })
        ->where(function($query) use ($glassmaterial) {
            if(is_array($glassmaterial)){
                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
            }
        })
        ->where(function($query) use ($gender) {
            if(is_array($gender)){
                return $query->whereIn('product_details.gender_id', (array)$gender);
            }
        })
        ->where(function($query) use ($feature) {
            if(is_array($feature)){
                foreach($feature as $f){
                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                }
            }
        })
        ->where(function($query) use ($price) {
            if(is_array($price)){
                return $query->whereBetween('product_details.price', (array)$price);
            }
        })
        ->orderBy('price', 'asc')
        ->paginate(24);
        // $respo->appends($request);
        // ->get();
        return $respo;
    }

    public function filters_group(Request $request,$group_by_column=NULL,$group_id,$group_name){
        $brands = $request->brands;
        $casematerial = $request->casematerial;
        $caseshape = $request->caseshape;
        $casesize = $request->casesize;
        $collection = $request->collection;
        $dialcolour = $request->dialcolour;
        $feature = $request->feature;//implode(',',$request->feature);
        $gender = $request->gender;
        $glassmaterial = $request->glassmaterial;
        $movement = $request->movement;
        $movementtype = $request->movementtype;
        $strapcolour = $request->strapcolour;
        $strapmaterial = $request->strapmaterial;
        $price = $request->price;//explode('-',$request->price);

        $respo = DB::table('product_details')
        ->select('product_details.id as product_id',$group_id,$group_name)
        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
        ->join('features', 'product_details.feature_id', '=', 'features.id')
        ->where('product_details.status','=', 1)
        ->where('product_details.main_category_id', '=', 1)
        ->where(function($query) use ($brands) {
            if(is_array($brands)){
                return $query->whereIn('product_details.brand_id', (array)$brands);
            }
        })
        ->where(function($query) use ($collection) {
            if(is_array($collection)){
                return $query->whereIn('product_details.collection_id', (array)$collection);
            }
        })
        ->where(function($query) use ($movement) {
            if(is_array($movement)){
                return $query->whereIn('product_details.movement_id', (array)$movement);
            }
        })
        ->where(function($query) use ($movementtype) {
            if(is_array($movementtype)){
                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
            }
        })
        ->where(function($query) use ($casesize) {
            if(is_array($casesize)){
                return $query->whereIn('product_details.case_size_id', (array)$casesize);
            }
        })
        ->where(function($query) use ($caseshape) {
            if(is_array($caseshape)){
                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
            }
        })
        ->where(function($query) use ($casematerial) {
            if(is_array($casematerial)){
                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
            }
        })
        ->where(function($query) use ($dialcolour) {
            if(is_array($dialcolour)){
                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
            }
        })
        ->where(function($query) use ($strapmaterial) {
            if(is_array($strapmaterial)){
                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
            }
        })
        ->where(function($query) use ($strapcolour) {
            if(is_array($strapcolour)){
                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
            }
        })
        ->where(function($query) use ($glassmaterial) {
            if(is_array($glassmaterial)){
                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
            }
        })
        ->where(function($query) use ($gender) {
            if(is_array($gender)){
                return $query->whereIn('product_details.gender_id', (array)$gender);
            }
        })
        ->where(function($query) use ($feature) {
            if(is_array($feature)){
                foreach($feature as $f){
                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                }
            }
        })
        ->where(function($query) use ($price) {
            if(is_array($price)){
                return $query->whereBetween('product_details.price', (array)$price);
            }
        })
        ->groupBy($group_by_column)
        ->get();
        return $respo;
    }

    public function accfilters_data(Request $request){
        $brands = $request->brands;
        // $casematerial = $request->casematerial;
        // $caseshape = $request->caseshape;
        // $casesize = $request->casesize;
        // $collection = $request->collection;
        // $dialcolour = $request->dialcolour;
        // $feature = $request->feature;//implode(',',$request->feature);
        $gender = $request->gender;
        // $glassmaterial = $request->glassmaterial;
        // $movement = $request->movement;
        $movementtype = $request->movementtype;
        // $strapcolour = $request->strapcolour;
        // $strapmaterial = $request->strapmaterial;
        $price = $request->price;//explode('-',$request->price);
        $category = $request->category;

        $respo = DB::table('product_details')
        ->select(
            'product_details.id as product_id',
            'product_details.model_no',
            'product_details.price',
            'brands.name',
            'product_details.thumb_img',
            'product_details.gender_id',
            'product_details.brand_id'
        )
        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
        ->where('product_details.status','=', 1)
        ->where(function($query) use ($category) {
            if(empty($category)){
                return $query->where('product_details.main_category_id', '!=', 1);
            }
            else{
                return $query->where('product_details.main_category_id', '=', $category);
            }
        })
        ->where(function($query) use ($brands) {
            if(is_array($brands)){
                return $query->whereIn('product_details.brand_id', (array)$brands);
            }
        })
        ->where(function($query) use ($movementtype) {
            if(is_array($movementtype)){
                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
            }
        })
        ->where(function($query) use ($gender) {
            if(is_array($gender)){
                return $query->whereIn('product_details.gender_id', (array)$gender);
            }
        })
        ->where(function($query) use ($price) {
            if(is_array($price)){
                return $query->whereBetween('product_details.price', (array)$price);
            }
        })
        ->orderBy('price', 'asc')
        ->paginate(24);
        // $respo->appends($request);
        // ->get();
        return $respo;
    }

    public function accfilters_group(Request $request,$group_by_column=NULL,$group_id,$group_name){
        $brands = $request->brands;
        // $casematerial = $request->casematerial;
        // $caseshape = $request->caseshape;
        // $casesize = $request->casesize;
        // $collection = $request->collection;
        // $dialcolour = $request->dialcolour;
        // $feature = $request->feature;//implode(',',$request->feature);
        $gender = $request->gender;
        // $glassmaterial = $request->glassmaterial;
        // $movement = $request->movement;
        $movementtype = $request->movementtype;
        // $strapcolour = $request->strapcolour;
        // $strapmaterial = $request->strapmaterial;
        $price = $request->price;//explode('-',$request->price);
        $category = $request->category;

        $respo = DB::table('product_details')
        ->select('product_details.id as product_id',$group_id,$group_name)
        ->where('product_details.status','=', 1)
        ->where(function($query) use ($brands) {
            if(is_array($brands)){
                return $query->whereIn('product_details.brand_id', (array)$brands);
            }
        })
        ->where(function($query) use ($category) {
            if(empty($category)){
                return $query->where('product_details.main_category_id', '!=', 1);
            }
            else{
                return $query->where('product_details.main_category_id', '=', $category);
            }
        })
        ->where(function($query) use ($movementtype) {
            if(is_array($movementtype)){
                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
            }
        })
        ->where(function($query) use ($gender) {
            if(is_array($gender)){
                return $query->whereIn('product_details.gender_id', (array)$gender);
            }
        })
        ->where(function($query) use ($price) {
            if(is_array($price)){
                return $query->whereBetween('product_details.price', (array)$price);
            }
        })
        ->groupBy($group_by_column)
        ->get();
        return $respo;
    }

    public function sale_filters_data(Request $request){
        $brands = $request->brands;
        $casematerial = $request->casematerial;
        $caseshape = $request->caseshape;
        $casesize = $request->casesize;
        $collection = $request->collection;
        $dialcolour = $request->dialcolour;
        $feature = $request->feature;//implode(',',$request->feature);
        $gender = $request->gender;
        $glassmaterial = $request->glassmaterial;
        $movement = $request->movement;
        $movementtype = $request->movementtype;
        $strapcolour = $request->strapcolour;
        $strapmaterial = $request->strapmaterial;
        $price = $request->price;//explode('-',$request->price);
        $discount = Discount::all();



        foreach ($discount as $disc){
            if ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Brand'){
                    if ($disc->product_by == 'Selected'){

                        $pro_ids = $disc->product_ids;
                        $pro_ids = explode(',',$pro_ids);

                        $brand_ids = $disc->brand_id;
                        $brand_ids = explode(',',$brand_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id as product_id', 'product_details.model_no', 'product_details.price', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
                        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
                        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
                        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
                        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
                        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
                        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
                        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
                        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
                        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
                        ->join('features', 'product_details.feature_id', '=', 'features.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($pro_ids) {
                            if(is_array($pro_ids)){
                                return $query->whereIn('product_details.id', (array)$pro_ids);
                            }
                        })
                        ->where(function($query) use ($brand_ids) {
                            if(is_array($brand_ids)){
                                return $query->whereIn('product_details.brand_id', (array)$brand_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->orderBy('price', 'asc')
                        ->paginate(24);
                        // ->get();
                    }
                    elseif ($disc->product_by == 'ALL'){

                        $brand_ids = $disc->brand_id;
                        $brand_ids = explode(',',$brand_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id as product_id', 'product_details.model_no', 'product_details.price', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
                        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
                        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
                        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
                        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
                        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
                        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
                        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
                        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
                        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
                        ->join('features', 'product_details.feature_id', '=', 'features.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($brand_ids) {
                            if(is_array($brand_ids)){
                                return $query->whereIn('product_details.brand_id', (array)$brand_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->orderBy('price', 'asc')
                        // ->get();
                        ->paginate(24);
                    }
                    else{}
            }
            elseif ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Gender'){
                    if ($disc->product_by == 'Selected'){

                        $pro_ids = $disc->product_ids;
                        $pro_ids = explode(',',$pro_ids);

                        $gender_ids = $disc->gender_id;
                        $gender_ids = explode(',',$gender_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id as product_id', 'product_details.model_no', 'product_details.price', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($pro_ids) {
                            if(is_array($pro_ids)){
                                return $query->whereIn('product_details.id', (array)$pro_ids);
                            }
                        })
                        ->where(function($query) use ($gender_ids) {
                            if(is_array($gender_ids)){
                                return $query->whereIn('product_details.gender_id', (array)$gender_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->orderBy('price', 'asc')
                        // ->get();
                        ->paginate(24);
                    }
                    elseif ($disc->product_by == 'ALL'){

                        $gender_ids = $disc->gender_id;
                        $gender_ids = explode(',',$gender_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id as product_id', 'product_details.model_no', 'product_details.price', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
                        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
                        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
                        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
                        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
                        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
                        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
                        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
                        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
                        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
                        ->join('features', 'product_details.feature_id', '=', 'features.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($gender_ids) {
                            if(is_array($gender_ids)){
                                return $query->whereIn('product_details.gender_id', (array)$gender_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->orderBy('price', 'asc')
                        // ->get();
                        ->paginate(24);
                    }
                    else{}
            }
            else{}
        }
        return $products;
    }

    public function sale_filters_group(Request $request,$group_by_column=NULL,$group_id,$group_name){
        $brands = $request->brands;
        $casematerial = $request->casematerial;
        $caseshape = $request->caseshape;
        $casesize = $request->casesize;
        $collection = $request->collection;
        $dialcolour = $request->dialcolour;
        $feature = $request->feature;//implode(',',$request->feature);
        $gender = $request->gender;
        $glassmaterial = $request->glassmaterial;
        $movement = $request->movement;
        $movementtype = $request->movementtype;
        $strapcolour = $request->strapcolour;
        $strapmaterial = $request->strapmaterial;
        $price = $request->price;//explode('-',$request->price);
        $discount = Discount::all();

        foreach ($discount as $disc){
            if ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Brand'){
                    if ($disc->product_by == 'Selected'){

                        $pro_ids = $disc->product_ids;
                        $pro_ids = explode(',',$pro_ids);

                        $brand_ids = $disc->brand_id;
                        $brand_ids = explode(',',$brand_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id',$group_id,$group_name)
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
                        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
                        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
                        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
                        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
                        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
                        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
                        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
                        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
                        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
                        ->join('features', 'product_details.feature_id', '=', 'features.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($pro_ids) {
                            if(is_array($pro_ids)){
                                return $query->whereIn('product_details.id', (array)$pro_ids);
                            }
                        })
                        ->where(function($query) use ($brand_ids) {
                            if(is_array($brand_ids)){
                                return $query->whereIn('product_details.brand_id', (array)$brand_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->groupBy($group_by_column)
                        // ->paginate(24);
                        ->get();
                    }
                    elseif ($disc->product_by == 'ALL'){

                        $brand_ids = $disc->brand_id;
                        $brand_ids = explode(',',$brand_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id',$group_id,$group_name)
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
                        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
                        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
                        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
                        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
                        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
                        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
                        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
                        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
                        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
                        ->join('features', 'product_details.feature_id', '=', 'features.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($brand_ids) {
                            if(is_array($brand_ids)){
                                return $query->whereIn('product_details.brand_id', (array)$brand_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->groupBy($group_by_column)
                        // ->paginate(24);
                        ->get();
                    }
                    else{}
            }
            elseif ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Gender'){
                    if ($disc->product_by == 'Selected'){

                        $pro_ids = $disc->product_ids;
                        $pro_ids = explode(',',$pro_ids);

                        $gender_ids = $disc->gender_id;
                        $gender_ids = explode(',',$gender_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id',$group_id,$group_name)
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
                        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
                        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
                        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
                        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
                        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
                        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
                        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
                        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
                        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
                        ->join('features', 'product_details.feature_id', '=', 'features.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($pro_ids) {
                            if(is_array($pro_ids)){
                                return $query->whereIn('product_details.id', (array)$pro_ids);
                            }
                        })
                        ->where(function($query) use ($gender_ids) {
                            if(is_array($gender_ids)){
                                return $query->whereIn('product_details.gender_id', (array)$gender_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->groupBy($group_by_column)
                        // ->paginate(24);
                        ->get();
                    }
                    elseif ($disc->product_by == 'ALL'){

                        $gender_ids = $disc->gender_id;
                        $gender_ids = explode(',',$gender_ids);

                        $products = DB::table('product_details')
                        ->select('product_details.id',$group_id,$group_name)
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->join('movements', 'product_details.movement_id', '=', 'movements.id')
                        ->join('movement_types', 'product_details.movement_type_id', '=', 'movement_types.id')
                        ->join('case_sizes', 'product_details.case_size_id', '=', 'case_sizes.id')
                        ->join('case_shapes', 'product_details.case_shape_id', '=', 'case_shapes.id')
                        ->join('case_materials', 'product_details.case_material_id', '=', 'case_materials.id')
                        ->join('glass_materials', 'product_details.glass_material_id', '=', 'glass_materials.id')
                        ->join('dial_colours', 'product_details.dial_colour_id', '=', 'dial_colours.id')
                        ->join('strap_materials', 'product_details.strap_material_id', '=', 'strap_materials.id')
                        ->join('strap_colours', 'product_details.strap_colour_id', '=', 'strap_colours.id')
                        ->join('genders', 'product_details.gender_id', '=', 'genders.id')
                        ->join('features', 'product_details.feature_id', '=', 'features.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($gender_ids) {
                            if(is_array($gender_ids)){
                                return $query->whereIn('product_details.gender_id', (array)$gender_ids);
                            }
                        })
                        ->where(function($query) use ($brands) {
                            if(is_array($brands)){
                                return $query->whereIn('product_details.brand_id', (array)$brands);
                            }
                        })
                        ->where(function($query) use ($collection) {
                            if(is_array($collection)){
                                return $query->whereIn('product_details.collection_id', (array)$collection);
                            }
                        })
                        ->where(function($query) use ($movement) {
                            if(is_array($movement)){
                                return $query->whereIn('product_details.movement_id', (array)$movement);
                            }
                        })
                        ->where(function($query) use ($movementtype) {
                            if(is_array($movementtype)){
                                return $query->whereIn('product_details.movement_type_id', (array)$movementtype);
                            }
                        })
                        ->where(function($query) use ($casesize) {
                            if(is_array($casesize)){
                                return $query->whereIn('product_details.case_size_id', (array)$casesize);
                            }
                        })
                        ->where(function($query) use ($caseshape) {
                            if(is_array($caseshape)){
                                return $query->whereIn('product_details.case_shape_id', (array)$caseshape);
                            }
                        })
                        ->where(function($query) use ($casematerial) {
                            if(is_array($casematerial)){
                                return $query->whereIn('product_details.case_material_id', (array)$casematerial);
                            }
                        })
                        ->where(function($query) use ($dialcolour) {
                            if(is_array($dialcolour)){
                                return $query->whereIn('product_details.dial_colour_id', (array)$dialcolour);
                            }
                        })
                        ->where(function($query) use ($strapmaterial) {
                            if(is_array($strapmaterial)){
                                return $query->whereIn('product_details.strap_material_id', (array)$strapmaterial);
                            }
                        })
                        ->where(function($query) use ($strapcolour) {
                            if(is_array($strapcolour)){
                                return $query->whereIn('product_details.strap_colour_id', (array)$strapcolour);
                            }
                        })
                        ->where(function($query) use ($glassmaterial) {
                            if(is_array($glassmaterial)){
                                return $query->whereIn('product_details.glass_material_id', (array)$glassmaterial);
                            }
                        })
                        ->where(function($query) use ($gender) {
                            if(is_array($gender)){
                                return $query->whereIn('product_details.gender_id', (array)$gender);
                            }
                        })
                        ->where(function($query) use ($feature) {
                            if(is_array($feature)){
                                foreach($feature as $f){
                                    return $query->whereRaw("find_in_set('".$f."',product_details.feature_id)");
                                }
                            }
                        })
                        ->where(function($query) use ($price) {
                            if(is_array($price)){
                                return $query->whereBetween('product_details.price', (array)$price);
                            }
                        })
                        ->groupBy($group_by_column)
                        // ->paginate(24);
                        ->get();
                    }
                    else{}
            }
            else{}
        }
        return $products;
    }

    public function discount_find_out(){
        $discount = Discount::all();
        foreach ($discount as $disc){
            if ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Brand'){
                    if ($disc->product_by == 'Selected'){

                        $pro_ids = $disc->product_ids;
                        $pro_ids = explode(',',$pro_ids);

                        $brand_ids = $disc->brand_id;
                        $brand_ids = explode(',',$brand_ids);

                        return $products = DB::table('product_details')
                        ->select('product_details.id', 'product_details.model_no', 'product_details.price', 'product_details.images', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($pro_ids) {
                            if(is_array($pro_ids)){
                                return $query->whereIn('product_details.id', (array)$pro_ids);
                            }
                        })
                        ->where(function($query) use ($brand_ids) {
                            if(is_array($brand_ids)){
                                return $query->whereIn('product_details.brand_id', (array)$brand_ids);
                            }
                        })
                        ->orderBy('price', 'asc')
                        ->paginate(24);
                    }
                    elseif ($disc->product_by == 'ALL'){

                        $brand_ids = $disc->brand_id;
                        $brand_ids = explode(',',$brand_ids);

                        return $products = DB::table('product_details')
                        ->select('product_details.id', 'product_details.model_no', 'product_details.price', 'product_details.images', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($brand_ids) {
                            if(is_array($brand_ids)){
                                return $query->whereIn('product_details.brand_id', (array)$brand_ids);
                            }
                        })
                        ->orderBy('price', 'asc')
                        ->paginate(24);
                    }
                    else{}
            }
            elseif ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Gender'){
                    if ($disc->product_by == 'Selected'){

                        $pro_ids = $disc->product_ids;
                        $pro_ids = explode(',',$pro_ids);

                        $gender_ids = $disc->gender_id;
                        $gender_ids = explode(',',$gender_ids);

                        return $products = DB::table('product_details')
                        ->select('product_details.id', 'product_details.model_no', 'product_details.price', 'product_details.images', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($pro_ids) {
                            if(is_array($pro_ids)){
                                return $query->whereIn('product_details.id', (array)$pro_ids);
                            }
                        })
                        ->where(function($query) use ($gender_ids) {
                            if(is_array($gender_ids)){
                                return $query->whereIn('product_details.gender_id', (array)$gender_ids);
                            }
                        })
                        ->orderBy('price', 'asc')
                        ->paginate(24);
                    }
                    elseif ($disc->product_by == 'ALL'){

                        $gender_ids = $disc->gender_id;
                        $gender_ids = explode(',',$gender_ids);

                        return $products = DB::table('product_details')
                        ->select('product_details.id', 'product_details.model_no', 'product_details.price', 'product_details.images', 'product_details.thumb_img', 'product_details.gender_id', 'product_details.brand_id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.main_category_id', '=', 1)
                        ->where(function($query) use ($gender_ids) {
                            if(is_array($gender_ids)){
                                return $query->whereIn('product_details.gender_id', (array)$gender_ids);
                            }
                        })
                        ->orderBy('price', 'asc')
                        ->paginate(24);
                    }
                    else{}
            }
            else{}
        }
    }

    public function discount_price($id,$brand,$price,$gender){
        $discount = Discount::all();
        foreach ($discount as $disc){
            if ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Brand'){
                foreach (explode(',',$disc->brand_id) as $b_id){
                    if ($disc->product_by == 'Selected'){
                        foreach (explode(',',$disc->product_ids) as $p_id){
                            if ($p_id == $id){
                                if ($b_id == $brand){
                                    if ($disc->discount_type == 'Percent'){
                                        if ($disc->discount_upto >= $price){
                                            return (int)$price - ((int)$price / 100 * (int)$disc->discount_value);
                                        }
                                        elseif ($disc->discount_upto <= $price){
                                            return ((int)$price - (int)$disc->max_value);
                                        }
                                        else{}
                                    }
                                    elseif ($disc->discount_type == 'Amount'){
                                        if ($disc->discount_upto >= $price){
                                            return (int)$price - (int)$disc->discount_value;
                                        }
                                        elseif ($disc->discount_upto <= $price){
                                            return ((int)$price - (int)$disc->max_value);
                                        }
                                        else{}
                                    }
                                    else{}
                                }
                            }
                        }
                    }
                    elseif ($disc->product_by == 'ALL'){
                        if ($b_id == $brand){
                            if ($disc->discount_type == 'Percent'){
                                if ($disc->discount_upto >= $price){
                                    return (int)$price - ((int)$price / 100 * (int)$disc->discount_value);
                                }
                                elseif ($disc->discount_upto <= $price){
                                    return ((int)$price - (int)$disc->max_value);
                                }
                                else{}
                            }
                            elseif ($disc->discount_type == 'Amount'){
                                if ($disc->discount_upto >= $price){
                                    return (int)$price - (int)$disc->discount_value;
                                }
                                elseif ($disc->discount_upto <= $price){
                                    return ((int)$price - (int)$disc->max_value);
                                }
                                else{}
                            }
                            else{}
                        }
                    }
                    else{}
                }
            }
            elseif ($disc->discount_activation_date <= date('Y-m-d') && $disc->discount_expiry_date >= date('Y-m-d') && $disc->discount_by == 'Gender'){
                foreach (explode(',',$disc->gender_id) as $g_id){
                    if ($disc->product_by == 'Selected'){
                        foreach (explode(',',$disc->product_ids) as $p_id){
                            if ($p_id == $id){
                                if ($g_id == $gender){
                                    if ($disc->discount_type == 'Percent'){
                                        if ($disc->discount_upto >= $price){
                                            return (int)$price - ((int)$price / 100 * (int)$disc->discount_value);
                                        }
                                        elseif ($disc->discount_upto <= $price){
                                            return ((int)$price - (int)$disc->max_value);
                                        }
                                        else{}
                                    }
                                    elseif ($disc->discount_type == 'Amount'){
                                        if ($disc->discount_upto >= $price){
                                            return (int)$price - (int)$disc->discount_value;
                                        }
                                        elseif ($disc->discount_upto <= $price){
                                            return ((int)$price - (int)$disc->max_value);
                                        }
                                        else{}
                                    }
                                    else{}
                                }
                            }
                        }
                    }
                    elseif ($disc->product_by == 'ALL'){
                        if ($g_id == $gender){
                            if ($disc->discount_type == 'Percent'){
                                if ($disc->discount_upto >= $price){
                                    return (int)$price - ((int)$price / 100 * (int)$disc->discount_value);
                                }
                                elseif ($disc->discount_upto <= $price){
                                    return ((int)$price - (int)$disc->max_value);
                                }
                                else{}
                            }
                            elseif ($disc->discount_type == 'Amount'){
                                if ($disc->discount_upto >= $price){
                                    return (int)$price - (int)$disc->discount_value;
                                }
                                elseif ($disc->discount_upto <= $price){
                                    return ((int)$price - (int)$disc->max_value);
                                }
                                else{}
                            }
                            else{}
                        }
                    }
                    else{}
                }
            }
            else{}
        }
    }

    public function paginate($items, $slug=null, $price=null, $perPage = 12, $page = null, $options = [])
    {
        if(!empty($slug)){
            $options = ['path'=>url()->current().'?gender='.$slug];
        }
        elseif(!empty($price)){
            $options = ['path'=>url()->current().'?price='.$price];
        }
        else{
            $options = ['path'=>url()->current()];
        }
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof CollSupport ? $items : CollSupport::make($items);
        // $options = ['path'=>'http://localhost/lahore/product?gender='.$slug];
        // $options = ['path'=>url()->current().'?gender='.$slug];
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
    public function pageNotFound(){
        $brand_cat = Brand::where('status', '=', true)->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();

        return view('frontend.404', [
            'brand_cat' => $brand_cat,
            'collection_cat' => $collection_cat,
            'strap_material_cat' => $strap_material_cat,
            'feature_cat' => $feature_cat,
            'footer_brands' => $footer_brands
        ]);
    }
 

  
public function addToCart(Request $request){

        $response =array();    
        $response["products"]=array();

        $item_id=$request->proid;
        $qnty=$request->qty;
        $userSession = $request->userSession;

        /* Check Stock code */

        // initialize empty cart items array
        $cart_items=array();
      
        // add new item on array
        $cart_items[$item_id]=$qnty;
          
        // read the cookie
         $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
         $cookie = stripslashes($cookie);
         $saved_cart_items = json_decode($cookie, true);

        // if $saved_cart_items is null, prevent null error
        if(!$saved_cart_items){
            $saved_cart_items=array();
        }

        // check if the item is in the array, if it is, do not add
        if(array_key_exists($item_id, $saved_cart_items)){
            // redirect to product list OR tell the user it was already added to the cart
            $response['status']="exist";
        }else{
            // if cart has contents
            if(count($saved_cart_items)>0){
                foreach($saved_cart_items as $key=>$value){
                    // add old item to array, it will prevent duplicate keys
                    $cart_items[$key]=$value;
                }
              }

            //Fetch Product Details

            $cart=get_detailed_cart($cart_items);

          /* 
            echo $cart['products']->item_subtotal;*/
            $response['products'] =$cart['products'];
            $response['cart_subtotal']  = $cart['actual_cart_subtotal'];
            $response['status']      = "added";
           /* $response['shipping_charges'] = $cart['shipping_charges'];*/
            $response['total_items']  = count($cart_items);
            /*$response['total_discount']  = $cart['total_discount'];*/
            $response['total_amount'] = $cart['actual_cart_subtotal'];
            $json = json_encode($cart_items, true);

            setcookie('cart_items_cookie', $json, time() + 2592000, "/");
            save_user_cart($cart_items);
        }


       echo  json_encode($response, JSON_PRETTY_PRINT);



} 

public  function RemoveItem(Request $request){

        $id = $_POST['pid'];   
        $Session = Session::get('id'); 
     $cart_items=array();
        $cookie = isset($_COOKIE['cart_items_cookie'])?$_COOKIE['cart_items_cookie']:'';
        $cookie = stripslashes($cookie);

        $saved_cart_items = json_decode($cookie, true);


        if(array_key_exists($id, $saved_cart_items)){

            unset($saved_cart_items[$id]);
        }

        if(count($saved_cart_items)>0){
            foreach($saved_cart_items as $key=>$value){
                // add old item to array, it will prevent duplicate keys
                $cart_items[$key]=$value;
            }
          }
        $cart=get_detailed_cart($cart_items);


       $json = json_encode($saved_cart_items, true);

       setcookie('cart_items_cookie', $json, time() + 2592000, "/");
      echo json_encode($cart, JSON_PRETTY_PRINT);
    } 

    public  function RemoveAllitem(Request $request){
   
        $Session = Session::get('id'); 
        $cart_items=array();
        $cookie = isset($_COOKIE['cart_items_cookie'])?$_COOKIE['cart_items_cookie']:'';
        $cookie = stripslashes($cookie);

        $saved_cart_items = json_decode($cookie, true);

        if(count($saved_cart_items)>0){
            foreach($saved_cart_items as $key=>$value){
                if(array_key_exists($key, $saved_cart_items)){
                    unset($saved_cart_items[$key]);
                }
            }
        }

        $cart=get_detailed_cart($cart_items);


        $json = json_encode($saved_cart_items, true);

        setcookie('cart_items_cookie', $json, time() + 2592000, "/");
        echo json_encode($cart, JSON_PRETTY_PRINT);
    }

    public function removeCartitem(){
     
     $id =  $_POST['pid'];

        $deleteCart =  DB::table('cart')->where('id','=',$id)->delete();

        if($deleteCart){

            echo 'success';
        }else{
            echo ' error';
        }

    }




/*checkoutPage*/


function updateQnty( Request $request){

        $response =array();    

        $pid= $request->id;
        $qnty= $request->qty;

        $total_price = 0;
        //Check Stock code

        // initialize empty cart items array
        $cart_items=array();
      
        // add new item on array
        $cart_items[$pid]=$qnty;
          
        // read the cookie
         $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
         $cookie = stripslashes($cookie);
         $saved_cart_items = json_decode($cookie, true);

         if(!$saved_cart_items){
            $saved_cart_items=array();
        }
        if(array_key_exists($pid, $saved_cart_items)){
            foreach($saved_cart_items as $key=>$value){
                if($pid == $key){
                    unset($saved_cart_items[$key]);
                }                        
            }
        }
      

            if(count($saved_cart_items)>0){
                foreach($saved_cart_items as $key=>$value){
                    // add old item to array, it will prevent duplicate keys
                    $cart_items[$key]=$value;
                }
              }

            //Fetch Product Details

            $cart=get_detailed_cart($cart_items);

          /* 
            echo $cart['products']->item_subtotal;*/
            $response['products'] =$cart['products'];
            $response['cart_subtotal']  = $cart['actual_cart_subtotal'];
            $response['status']      = "added";
           /* $response['shipping_charges'] = $cart['shipping_charges'];*/
            $response['total_items']  = count($cart_items);
            /*$response['total_discount']  = $cart['total_discount'];*/
            $response['total_amount'] = $cart['actual_cart_subtotal'];
            $json = json_encode($cart_items, true);

            setcookie('cart_items_cookie', $json, time() + 2592000, "/");
            save_user_cart($cart_items);
        


       echo  json_encode($response, JSON_PRETTY_PRINT);

     }


     function setAddress(){
         $response =array();    
         $adid=$this->input->post('adid');
        if ($this->session->has_userdata('user_id') && !empty($adid)) {
            $this->session->set_userdata('selected_address', $adid);
            $response['status']="success";
        }else{
            $response['status'] = 'haserror';
            $response['error'] = 'Invalid Request';
        }
        echo json_encode($response);
     }


 function validatepromo(Request $request){
      
        $response=array();  

        $promocode = $request->coupon_code;

        $userSession = Session::get('id'); 

if (isset($userSession)) {

  $cartItem = DB::table('cart')
    ->select('cart.id as cart_id','cart.pro_qty','guest_users.name','guest_users.phone' , 'guest_users.email' , 'product_details.*')
    ->join('guest_users','cart.user_id','=','guest_users.id')
    ->join('product_details','cart.product_id','=','product_details.id')
    ->where('user_id','=', $userSession)
    ->get();



        $cartTotal = 0; 
        foreach($cartItem as $item){
        $cart = $item->pro_qty + $item->price;
        $cartTotal +=$cart;
        }


   $promodata = DB::table('tbl_promocode')->where('promocode_name','=',$promocode)->first();   

   if($promodata != Null){

     $discountedPrice =  $cartTotal*$promodata->promocode_value/100;
     $promo_discounted_amount = $cartTotal-$discountedPrice;

            if($promodata->status=='active'){
                $response['cart_total'] = round($cartTotal);
                $response['promo_discounted_amount']=round($discountedPrice);
                $response['promo_applied_cart_total']= round($promo_discounted_amount);
                $response['status']="active";

            }else{
                $response['status'] = 'inactive';
                $response['error'] = $promodata->msg;
            }

            }else{ $response['error'] = 'promocode not valid' ;}

        }else{
            $response['status'] = 'inactive'; 
            $response['error'] = 'Invalid Request';
        }

        echo json_encode($response); 
     }

    function removepromocode(){
        $response=array();
        if ($this->session->has_userdata('user_id')) {
            $this->session->unset_userdata('promocode');
            $response['status']="success";
        }else{
            $response['status'] = 'haserror';
            $response['error'] = 'Invalid Request';
        }
        echo json_encode($response);
    }


    function test(){
        // read the cookie
         $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
         $cookie = stripslashes($cookie);
         $saved_cart_items = json_decode($cookie, true);
         save_user_cart($saved_cart_items);
    }

  public function checkoutPage(Request $request){

        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = DB::table('guest_users')->where('id', '=', $userSession)->get();

      return view('frontend.mycart',[ 
        'seo' => $seo,
        'brand_cat' => $brand_cat,
        'usersSession'=>$userSession,
        'strap_material_cat' => $strap_material_cat,
        'feature_cat' => $feature_cat,
        'footer_brands' => $footer_brands,
        'collection_cat' => $collection_cat,
        'usersData' =>$userData,
        
      ]);    
        
    }

public function checkout(Request $request){

        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = DB::table('guest_users')->where('id', '=', $userSession)->get();
        $userAddress = DB::table('shipping_adds')->where('uid', '=', $userSession)->get();

      return view('frontend.checkout',[ 
        'seo' => $seo,
        'brand_cat' => $brand_cat,
        'usersSession'=>$userSession,
        'strap_material_cat' => $strap_material_cat,
        'feature_cat' => $feature_cat,
        'footer_brands' => $footer_brands,
        'collection_cat' => $collection_cat,
        'usersData' =>$userData,
        'ShippingAdd' =>$userAddress,
        
      ]);    
        
    }

    public function CheckoutProduct(Request $request)
    {
        $this->validate($request,[
            'addr_id'    => 'required',
            'pay_option'  => 'required',
        ]);

        $userid = Session::get('id');
        $addrid = $request->addr_id;
        $payoption = $request->pay_option;
        if(empty($userid)){
            return view('frontend.index');
        }else{
            $this->SaveOrderData($addrid,$payoption);
            // $cart_items=array();
            // $cookie = isset($_COOKIE['cart_items_cookie'])?$_COOKIE['cart_items_cookie']:'';
            // $cookie = stripslashes($cookie);

            // $saved_cart_items = json_decode($cookie, true);

            // if(count($saved_cart_items)>0){
            //     foreach($saved_cart_items as $key=>$value){
            //         if(array_key_exists($key, $saved_cart_items)){
            //             unset($saved_cart_items[$key]);
            //         }
            //     }
            // }
            // $json = json_encode($saved_cart_items, true);

            // setcookie('cart_items_cookie', $json, time() + 2592000, "/");
        }
    }


    public function SaveOrderData($addrid,$payoption)
    {
        $userid = Session::get('id');
        $addr_id = $addrid;
        $payment_method = $payoption;
        if(empty($userid)){
            return view('frontend.index');
        }else{
            if (!empty($userid) && !empty($payoption)) {
                $cart_items=array();
                  
                // read the cookie
                 $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
                 $cookie = stripslashes($cookie);
                 $saved_cart_items = json_decode($cookie, true);


                    if(count($saved_cart_items)>0){
                        foreach($saved_cart_items as $key=>$value){
                            $cart_items[$key]=$value;
                        }
                      }


                    $cart=get_detailed_cart($cart_items);

                    $products =$cart['products'];
                    $cart_subtotal  = $cart['actual_cart_subtotal'];
                    $total_items  = count($cart_items);
                    $total_amount = $cart['actual_cart_subtotal'];

                $invoice_no=str_random();

                //Add order 
                $order_details=array('invoice_no'=>$invoice_no,
                    'uid'=>$userid,
                    'address_id'=>$addr_id,
                    'total_items'=>$total_items,
                    'order_subtotal'=>$cart_subtotal,
                    'total_amount'=>$total_amount,
                    'payment_method'=>$payment_method,
                    'created_at'=>date("Y-m-d H:i:s"),
                    'updated_at'=>date("Y-m-d H:i:s")
                );
                //Order Address
                $odr_tbl = DB::table('tbl_orders')->insert($order_details);
                if($odr_tbl)
                {
                    $getAddr =  DB::table('shipping_adds')->where('id','=',$addr_id)->get();
                    if (count($getAddr) > 0) {
                        foreach ($getAddr as $ad) {
                            $addressdata=array(
                                'order_id'=> $invoice_no,
                                'address_line' => $ad->address_line,
                                'uid' => $ad->uid,
                                'name' => $ad->name,
                                'city' => $ad->city,
                                'state' => $ad->state,
                                'pincode' => $ad->pincode,
                                'phone'=>$ad->phone,
                                'adr_type'=>$ad->adr_type,
                                'landmark'=>$ad->landmark,
                                'date_added' => date("Y-m-d H:i:s"),
                                'date_modified' => date("Y-m-d H:i:s")
                            );
                            DB::table('tbl_order_address')->insert($addressdata);
                        }
                    }
                }
                //Order Items
                    if (count($products) > 0) {
                        foreach ($products as $pd) {
                            $itemsdata=array(
                                'order_id'=> $invoice_no,
                                'item_id' => $pd['itemid'],
                                'item_name' => $pd['itemname'],
                                'item_price' => $pd['itemprice'],
                                'item_qnty' => $pd['itemqty'],
                                'date_added' => date("Y-m-d H:i:s")
                            );
                            DB::table('tbl_order_items')->insert($itemsdata);
                        }
                    }

                    //Order Status
                            $statusdata=array(
                                'order_id'=> $invoice_no,
                                'order_status' => 0,
                                'created_at' => date("Y-m-d H:i:s"),
                                'updated_at' => date("Y-m-d H:i:s")
                            );
                            $status_odr = DB::table('tbl_order_status')->insert($statusdata);
                        
                       if ($status_odr) {
                           echo 'success';
                           $this->sendOrdermail($invoice_no);
                       }else{
                            echo 'error';
                       }
                    
        }
    }
}

public function OrderDetails($id){        

        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userSession = Session::get('id');
        $userData = GuestUser::where('id', '=', $userSession)->get();
        $userShippingAdd = ShippingAdd::where('uid', '=', $userSession)->get();

        $orderData = DB::table('tbl_orders')->where('invoice_no', '=', $id)->where('uid','=',$userSession)->get();
        $orderAddress = DB::table('tbl_order_address')->where('order_id', '=', $id)->where('uid','=',$userSession)->get();
        $orderItems = DB::table('tbl_order_items')->where('order_id', '=', $id)->get();
        $orderStatus = DB::table('tbl_order_status')->where('order_id', '=', $id)->get();

            return view('frontend.order-details',
                [
                    'seo' => $seo,
                    'brand_cat' => $brand_cat,
                    'strap_material_cat' => $strap_material_cat,
                    'feature_cat' => $feature_cat,
                    'footer_brands' => $footer_brands,
                    'collection_cat' => $collection_cat,
                    'usersSession' => $userSession,
                    'usersData' => $userData,
                    'ShippingAdd'=>$userShippingAdd,
                    'orderData' => $orderData,
                    'orderAddress' => $orderAddress,
                    'orderItems' => $orderItems,
                    'orderStatus' => $orderStatus,    
                    'invoice_no' => $id,                  
                ]

        );

    }

    public function CancelOrder($id){

        $userSession = Session::get('id');
        $datas['order_status']       = 4;
        $datas['updated_at']       = date("Y-m-d H:i:s");

        $insert_ad = DB::table('tbl_orders')->where('invoice_no', '=', $id)->where('uid', '=', $userSession)->update($datas);

        $itemsdata['order_id']       = $id;
        $itemsdata['order_status']       = 4;
        $itemsdata['created_at']       = date("Y-m-d H:i:s");
        $itemsdata['updated_at']       = date("Y-m-d H:i:s");

        DB::table('tbl_order_status')->insert($itemsdata);


        $seo = SeoTag::where('page_name', '=', 'home')->firstOrFail();
        $brand_cat = Brand::where('status', '=', '1')->get();
        $collection_cat = Collection::where('status', '=', true)->get();
        $strap_material_cat = StrapMaterial::where('status', '=', true)->get();
        $feature_cat = Feature::where('status', '=', true)->get();
        $footer_brands = Brand::where('status', '=', true)->limit(5)->get();
        $userData = GuestUser::where('id', '=', $userSession)->get();
        $userShippingAdd = ShippingAdd::where('uid', '=', $userSession)->get();
        $userOrders =  DB::table('tbl_orders')->where('uid','=',$userSession)->get();

            return view('frontend.user-orders',
                [
                    'seo' => $seo,
                    'brand_cat' => $brand_cat,
                    'strap_material_cat' => $strap_material_cat,
                    'feature_cat' => $feature_cat,
                    'footer_brands' => $footer_brands,
                    'collection_cat' => $collection_cat,
                    'usersSession' => $userSession,
                    'usersData' => $userData,
                    'ShippingAdd'=>$userShippingAdd,
                    'userOrders'=>$userOrders
                  
                ]

            );
        } 


        public function sendOrdermail($id){
            $items = $uname = '';
            $userSession = Session::get('id');
            $userData = GuestUser::where('id', '=', $userSession)->get();
            foreach ($userData as $udata) {
                $uname = $udata->name;
                $email = $udata->email;
            }
            $userOrders =  DB::table('tbl_orders')->where('invoice_no','=',$id)->where('uid','=',$userSession)->get();
            $order_items = '';
            $paymode = $order_date = $deliv_addr = $order_update_date = '';$odrtotal = $subtotal = 0;
            foreach ($userOrders as $uorder) {
                $subtotal = $uorder->order_subtotal;
                $odrtotal = $uorder->total_amount;
                $paymode = strtoupper($uorder->payment_method);
                $order_date = date('d M Y',strtotime($uorder->created_at));
            }
            $orderAddres =  DB::table('tbl_order_address')->where('order_id','=',$id)->where('uid','=',$userSession)->get();
            foreach ($orderAddres as $orderAdr) {
                $deliv_addr = '<div style="font-weight:bold;margin-bottom:5px">'.$orderAdr->name.'</div><div style="margin-bottom:5px">'.$orderAdr->address_line.', '.$orderAdr->city.',<br> '.$orderAdr->landmark.',</div><div style="margin-bottom:5px"> '.$orderAdr->state.', '.$orderAdr->pincode.'<br><br><div style="margin-top:15px;margin-bottom:5px"><span style="font-weight:bold">Mobile:</span><span>'.$orderAdr->phone.'</span> </div>';
            }

            $order_no = $id;
            $order_status = 'Your Order has been successfully <b>placed</b> with Order ID: <strong>'.$order_no.'</strong>';
            $order_status1 = 'You order with ID: <strong>'.$order_no.'</strong> has been successfully <b>placed</b>. We will initiate the packaging and have it shipped to you the minute it is ready.';

            $userOrderItems =  DB::table('tbl_order_items')->where('order_id','=',$id)->get();
            foreach ($userOrderItems as $pros) {
                $Item_details =  DB::table('product_details')->where('id','=',$pros->item_id)->get();
                foreach ($Item_details as $item) {
                    if($item->thumb_img == null || $item->thumb_img == ''){
                        $pimg = asset('public/frontend/assets/images/images.jpeg');
                    }else{
                        $thumbImg = explode(',',$item->thumb_img);
                        $count_img = count($thumbImg);
                        for($i=0; $i<$count_img; $i++){
                            if ($i == 0){
                                $pimg = asset("storage/app/watch_thumb/".$thumbImg[0]);
                            }
                        }
                    }

                    $order_items .= '<tr style="overflow:hidden;border-bottom:solid 1px #e8e8e8"><td style="padding-left:14px;width:50%;text-align:left;vertical-align:top;border-collapse:collapse"><table style="border-spacing:0px"><tbody><tr><td><img src="'.$pimg.'" style="width: 60px;height: 60px;" alt="" class="'.$item->model_no.'"></td><td style="text-align:left;border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-size:14px;vertical-align:top;color:#686868;padding: 10px;">'.$item->model_no.'</td></tr></tbody></table></td><td style="width:17%;text-align:left;padding-bottom:20px;border-collapse:collapse"><table style="border-spacing:0px"><tbody><tr><td style="font-family:Helvetica,Arial,sans-serif;font-size:14px;color:#686868;width:30%;padding: 10px;5px;text-align: center;">'.$pros->item_qnty.'</td></tr></tbody></table></td><td style="border-bottom:solid 1px #e8e8e8;text-align:left;border-collapse:collapse;vertical-align:top;color:#686868;font-family:Helvetica,Arial,sans-serif;font-size:14px;width:15%;text-align:left;padding: 10px;">Rs. '.$pros->item_price.'</td><td style="border-bottom:solid 1px #e8e8e8;border-collapse:collapse;vertical-align:top;color:#686868;font-family:Helvetica,Arial,sans-serif;font-size:14px;width:15%;text-align:right;padding: 10px;">Rs. '.$pros->item_price*$pros->item_qnty.'</td></tr>';
                }
            }

            $logo_img = asset("public/frontend/assets/images/logo.png"); 
            $data = [ 
              'email'=>  $email,
              'subject' => 'Order No. '.$id.' - Lahore Watch Co. Order Confirmation'
            ];

            // $arrContextOptions=array(
            //     "ssl"=>array(
            //         "verify_peer"=>false,
            //         "verify_peer_name"=>false,
            //     ),
            // );  

            // $file = asset("public/frontend/assets/email.html");

            // $en_msg = file_get_contents($file, false, stream_context_create($arrContextOptions));


            $file = asset("public/frontend/assets/email.html");
            $en_msg = file_get_contents($file);

             $en_msg = str_replace('$order_no', $order_no, $en_msg);
            $en_msg = str_replace('$order_status', $order_status, $en_msg);
            $en_msg = str_replace('$order_status1', $order_status1, $en_msg);
            $en_msg = str_replace('$order_update_date', $order_update_date, $en_msg);
            $en_msg = str_replace('$order_date', $order_date, $en_msg);
            $en_msg = str_replace('$deliv_addr', $deliv_addr, $en_msg);
            $en_msg = str_replace('$order_items', $order_items, $en_msg);
            $en_msg = str_replace('$odrsubtotal', $subtotal, $en_msg);
            $en_msg = str_replace('$paymode', $paymode, $en_msg);
            $en_msg = str_replace('$odrtotal', $odrtotal, $en_msg);
            $en_msg = str_replace('$custname', $uname, $en_msg);
            $en_msg = str_replace('$logo_img', $logo_img, $en_msg);
     

            Mail::to($request->email)->send(new SendOrderConfirmationMail($data));

            if(count(Mail::failures()) > 0)
            {
                return 'Error! Please Try Later.';
            }
            else
            {
                return 'Thank you:-) we\'ll get back to you.';
            }
        }

    
}