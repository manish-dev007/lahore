<?php

 

/**
 * Get cart items with details
 * 
 * @param cart_items 
 * @return array
 */
if (!function_exists('get_detailed_cart')) {
    function get_detailed_cart($cart_items,$uid=NULL,$promocode=NULL) {
         
         $total_amount=0; 
         $shipping_charges=0;       

         $return=array();
         $return['products']=array();
            $cart_subtotal=0;
            $actualcart_subtotal=0;         
            $total_discount=0;
            foreach($cart_items as $key=>$value){

                $item_subtotal=0;
                $qty_discount = 0;

                    $product =  DB::table('product_details')
                        ->select('product_details.*','brands.name as brand_name','brands.slug','collections.name')
                        ->join('brands', 'product_details.brand_id', '=', 'brands.id')
                        ->join('collections', 'product_details.collection_id', '=', 'collections.id')
                        ->where('product_details.status','=', 1)
                        ->where('product_details.id','=',$key)
                        ->first();
                    

                /*$ci->Products_model->get_product_price(array('id'=>$key,'status' => "'active'"))->row();*/

                $pid = $product->id;
                $category_id = $product->main_category_id;
               /* $netweight=$product->netweight;   */       
               $item_name = $product->name;
               $item_link = $product->slug;
                
                $pimage=!empty($product->thumb_img)?explode('*',$product->thumb_img):'';

               /* $image='';
                if(isset($pimage[0]) && $pimage[0]){
                    $image= getThumbPathByFilename('product_file_path',$pimage[0],'410x278');
                }*/

                $item_price=$product->price;
                $item_subtotal = $item_price*$value;
                $item_new_price=0;

                //Check Discount
              /*  $discount=discount($pid,$category_id,$item_price);
                if($discount['disc_available']=='yes'){
                     $item_new_price=$item_price-$discount['discount_amount'];
                     $qty_discount=$discount['discount_amount']*$value;
                     $total_discount+=$qty_discount;    
                     //$total_discount+=$discount['discount_amount'];
                     $item_subtotal = $item_new_price*$value;
                }else{
                     $item_subtotal = $item_price*$value;
                }*/

                //echo $item_new_price." ".$discount['discount_amount'];
                $img_pros =  '';
                $thumbImg = explode(',',$product->thumb_img);
                $c_pro = count($thumbImg);
                for ($i=0; $i < $c_pro; $i++) { 
                    if ($i == 0) {
                        if ($thumbImg[0] == NULL) {
                            $img_pros = URL::asset('public/frontend/assets/images/images.jpeg');
                        }else{
                            $img_pros = asset('storage/app/watch_thumb/'.$thumbImg[0]);
                        }
                        
                    }
                }

                $col= array();
                $col['itemid']    = $key;
                $col['itempid']   = $pid;                
                $col['itemname']  = $item_name;
                $col['pimage']    = $img_pros;          
                $col['itemqty']   = $value;
             
                $col['itemlink']  = $item_link;
                $col['itemprice'] = $item_price;
               /* $col['item_new_price'] = $item_new_price;                
                $col['item_discount_status']=$discount['disc_available'];*/
                $col['item_subtotal'] = $item_subtotal;                  
                
                $cart_subtotal+=$item_subtotal;
                $actualcart_subtotal += $item_price * $value;
               
                array_push($return['products'], $col);      
              }
 
            //Check if couponcode applied             
           /* $uid = $ci->session->has_userdata('uid')? $ci->session->userdata('uid'):$uid;
            $promocode = $ci->session->has_userdata('promocode')? $ci->session->userdata('promocode'):$promocode;*/
           // echo $promocode; die;

         /*   if(!empty($promocode)){
                 $coupondata=validatecoupon($promocode,$cart_items,$uid);
                if($coupondata['coupon_status']=='success'){
                    $cart_subtotal=$coupondata['cart_subtotal'];
                    $total_discount=$coupondata['total_discount'];
                    $return['products']=$coupondata['products'];
                }
            }*/ 


         /*   if ($cart_subtotal > $min_value_free_delivery) {
                $total_amount = $cart_subtotal;
                $shipping_charges = 0;
                $total_discount = $total_discount + $shipping_amount;
            } else if ($cart_subtotal == 0){
                $total_amount = $cart_subtotal;
                $shipping_charges = 0; 
            }else{
                $total_amount = $cart_subtotal + $shipping_amount;
                $shipping_charges = $shipping_amount;                            
            }*/
              
           /* $return['promocode']=$promocode;
            $return['shipping_charges']=$shipping_charges;*/


            $return['actual_cart_subtotal'] = $actualcart_subtotal;
            $return['cart_subtotal']=$cart_subtotal;
            $return['total_amount']=$cart_subtotal;
            /*$return['total_discount']=$total_discount;*/
            $return['total_items']=count($cart_items);

         return $return; 
      }
}


/**
 * Merge cart items (Cookies & Database)
 * 
 * @param cart_id 
 * @return array
 */
if (!function_exists('user_cart')) {

    function user_cart($cart_id=NULL) { 

         $uidSession = Session::get('id'); 
         // read the cookie
         $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
         $cookie = stripslashes($cookie);
         $saved_cart_items = json_decode($cookie, true);

         $uid='';
         $dbcart='';
           
         if($uidSession && !empty($cart_id)){
            $uid=$uidSession;
            $dbcart=DB::table('cart')->where('user_id','=',$uidSession)->get();
            $cart_id=isset($dbcart[0]->id)?$dbcart[0]->id:'';
         }

        // initialize empty cart items array
        $cart_items=array();
        
        // if $saved_cart_items is null, prevent null error
        if(!$saved_cart_items){
            $saved_cart_items=array();
        }
          
        #Get User cart         
        if(!empty($dbcart)){
            $db_items=!empty($dbcart[0]->item_id)?explode(',',$dbcart[0]->item_id):NULL;
            $db_qnty=!empty($dbcart[0]->qnty)?explode(',',$dbcart[0]->qnty):NULL;

            if(!empty($db_items)){
                for($i=0;$i<=sizeof($db_items);$i++){   
                    if(isset($db_items[$i])){
                        $cart_items[$db_items[$i]]=$db_qnty[$i];                         
                    }
                }
            }
        } 
 
        //Get cookies saved cart

        if(!empty($saved_cart_items)){
            foreach($saved_cart_items as $key=>$value){
                if(array_key_exists($key, $cart_items)){
                    //Skip
                }else{

              $cart_items[$key]=$value;
        
                }
            }
         }
    
        //save cart in database
   
        if($uidSession){
            $uid=$uidSession;
            if(!empty($cart_items)){

                $item_ids="";
                $item_qnty="";

                foreach($cart_items as $key=>$value){
                    $item_ids.=$key.",";
                    $item_qnty.=$value.",";
                }

                $cart_data=array(
                    'user_id'=>$uid,
                    'product_id'=>rtrim($item_ids,','),
                    'pro_qty'=>rtrim($item_qnty,','),
                );

             $cart_id = DB::table('cart')->where('id', $cart_id)->update($cart_data);

            }
        }


            $json = json_encode($cart_items, true);
            setcookie('cart_items_cookie', $json, time() + 2592000, "/");
    } 
}




/**
 * Update cart items (Cookies & Database)
 * 
 * @param saved_cart_items 
 * @return array
 */
if (!function_exists('save_user_cart')) {
    function save_user_cart($saved_cart_items) {

       /* $ci = $this;  */
 
        $uid='';
        $dbcart='';
        $uidSession = Session::get('id'); 

        if($uidSession){
            $dbcart =  DB::table('cart')->where('user_id','=',$uidSession)->get();     
            $cart_id=isset($dbcart[0]->id)?$dbcart[0]->id:'';
        }else{
            $cart_id='';
        }

        // initialize empty cart items array
        $cart_items=array();
    
        // if $saved_cart_items is null, prevent null error
        if(!$saved_cart_items){
            $saved_cart_items=array();
        }
 
        //die;
        //save cart in database
        if($uidSession){
            $uid=$uidSession;
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
                    'user_id'=>$uid,
                    'product_id'=>rtrim($item_ids,','),
                    'pro_qty'=>rtrim($item_qnty,',')
          
                );

              }else{
                $cart_data=array(
                    'user_id'=>$uid,
                    'product_id'=>'',
                    'pro_qty'=>''
            
                );
             }

             //update in cart

           /*  print_r($cart_data);
             print_r($cart_items);
             echo $cart_id;*/

             

             $cart_id_u = DB::table('cart')->where('id', $cart_id)->update($cart_data);
             $json = json_encode($cart_items, true);
             //dd($json);
             setcookie('cart_items_cookie', $json, time() + 2592000, "/");
        }    
    }
}
