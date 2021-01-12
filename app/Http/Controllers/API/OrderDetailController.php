<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storage;
use Illuminate\Support\Facades\Input;
use App\ProductDetail;
use App\TblOrder;
use App\TblOrderstatus;
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
use App\MainCategory;
use DB;
use Image;
use Illuminate\Support\Str;

class OrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TblOrder::latest()->paginate(env('PAGE_NO', '10'));
        //return TblOrder::select('id', 'invoice_no')->get();  
        //return DB::table('tbl_order')->select(array('id', 'invoice_no'))->paginate(env('PAGE_NO', '10'))->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $order_data = TblOrder::where('id', '=', $id)->get();
        $orderAdr_data = DB::table('tbl_order_address')->where('order_id', '=', $order_data[0]->invoice_no)->get();
        $odr_detail = '';
        $orderStatus_data = DB::table('tbl_order_status')->where('order_id', '=', $order_data[0]->invoice_no)->orderBy('id','desc')->limit(1)->get();
        $orderitems_data = DB::table('tbl_order_items')->where('order_id', '=', $order_data[0]->invoice_no)->get();


        $odr_detail .= '<div class="order-detaildel"> <div class="orderdeldet"> <div> <h6>'.$order_data[0]->total_items.' Items </h6> <h5 class="odr_addr">Address:</h5> <p> <h6>'.$orderAdr_data[0]->name.'|'.$orderAdr_data[0]->phone.'</h6>'.$orderAdr_data[0]->address_line.','.$orderAdr_data[0]->city.','.$orderAdr_data[0]->state.','.$orderAdr_data[0]->pincode.' (Landmark:'.$orderAdr_data[0]->landmark.')</p></div><div class="buttoncancle">
            <button class="btn btn-theme bt_dwnload">Download Invoice</button></div></div>';
                       
        if ($order_data[0]->order_status < 2) {
            $odr_detail .= '
            <p>You can cancel the order until the order is out for delivery</p>';
          }
                  
      $odr_detail .= '</div>';
    $status_list = $order_date = $odr_st = $odr_id = '';
foreach ($orderStatus_data as $odrstatus) {
    $order_date=date('d M Y',strtotime($odrstatus->updated_at));
    $odr_st = $odrstatus->order_status;
    $odr_id = $odrstatus->order_id;
}   $status_list = '';
    $status_array= array('0'=>'Placed','1'=>'Processed','2'=>'Out for Delivery','3'=>'Completed');
    $odr_detail .= '<div class="tabsection1">
      <div class="order-detailmain">';   
        $odr_detail .= '<div class="order-detailleft">
            <ul class="progressorder">';

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
              $sub_statement="order is delivered successfully";
               $statement = 'You order has been delivered successfully';
          }
          $odr_detail.='<li class="progress__item '.$status_class.'">
                          <p class="progress__title">'.$statement.'</p>
                          <p class="progress__info">'.$sub_statement.'</p>
                        </li>';
        }
        

            $odr_detail .= '</ul>
          </div>';

    if($order_data[0]->order_status == 4){     
        $cancl = asset('public/frontend/assets/images/cancelled.png');
      $odr_detail .= '<div class="text-center">
         <img src="'.$cancl.'" style="height: 60px;margin: 10px auto;" alt="Lahore - Order cancelled">
         <p> order has been cancelled.</p>
      </div>
      <style type="text/css">
        .order-detailright{
          margin: 30px auto;
          display: block;
        }
      </style>';
  }

        $odr_detail .= '<div class="order-detailright">
          <ul class="lsistblock">';
          foreach ($orderitems_data as $odritems) {

              $pro_data = DB::table('product_details')
                    ->where('id','=',$odritems->item_id)
                    ->get();

             $url = url('/').'/storage/app/watch_thumb/';
             $image =explode(',', $pro_data[0]->thumb_img);
            
            $odr_detail .= '<li>
              <div class="cartproduct">
                <div class="cartproductimg">';
                  if($image[0] == NULL){
                    $odr_detail .= '<img src="'.asset('public/frontend/assets/images/images.jpeg').'" style="width: 60px; height: 60px;">';
                  }else{ 
                  $odr_detail .= '<img src="'.$url.'/'.$image[0].'" style="width: 60px; height: 60px;">';
                  }
                $odr_detail .= '</div>
                <div class="cartdetails">
                  <div class="item-desc">
                    <div class="item-name">'.$pro_data[0]->model_no.'<b>('.$odritems->item_name.')</b></div>
                    <div class="qnt"> Qty:<span>'.$odritems->item_qnty.'</span> <span class="oprice">₹'.$odritems->item_price.'</span></div>
                  </div>
                </div>
              </div>
            </li>';
        }
          $odr_detail .= '</ul>

          <div class="billdetailblock">
            <h4>Bill Details</h4>
            <ul>
              <li><label>Subtotal</label><span>₹'.$order_data[0]->order_subtotal.'</span></li>
              <li><label>Delivery Charge(+)</label><span>₹0</span></li>
              <li class="totalblock"><label>Total</label><span>₹'.$order_data[0]->total_amount.'</span></li>
            </ul>
          </div>
        </div>
      </div>
    </div>';



        echo $odr_detail;
    }

     
    public function idsShow()
    {
        //echo 'string';die;
        return TblOrderstatus::select('id', 'name')->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $itemsdata=array();
        $data = TblOrder::where('id', '=', $id)->firstOrFail();
        $data->order_status = $request->order_status;
        $order_data = TblOrder::where('id', '=', $id)->get();
        $datastatus['order_id'] = $order_data[0]->invoice_no;
        $datastatus['order_status'] = $data->order_status;
        $datastatus['created_at']       = date("Y-m-d H:i:s");
        $datastatus['updated_at']       = date("Y-m-d H:i:s");
        $st = DB::table('tbl_order_status')->insert($datastatus);
        
        
        $data->save();

        if ($data->save())
        {
            return ('Status Updated Successfully.');
        }
        else
        {
            return ('Error! Gender Not Updated.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Order = TblOrder::where('id', '=', $id)->firstOrFail();
        $Order->delete();
        return ['message' => 'Order Deleted.'];
    }



    public function search($query){
        echo "string";
    }
    
    public function updateStatus(Request $request, $id){
    }
    
    public function updateFeaturedStatus(Request $request, $id){
    }

    function import(Request $request)
    {
        echo "string";
    }
}
