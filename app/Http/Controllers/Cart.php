<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Cart extends Public_Controller {
   function __construct(){
        parent::__construct();
		$this->load->model('product_model');
		$this->load->model('users_model');  
		$this->load->model('promocode_model');
		$this->load->model('wallet_model'); 
		$this->load->model('customers_model'); 
		     	
    }

   public function index(){
  
	    $this->data['page_title'] = 'View Cart';
		$this->data['extracss']='';
		$this->data['extrajs']= '';

		// $cart=getCart();
		// pr($cart); die;

		$meta=getMeta(6);
 		$this->data['meta_title']=(isset($meta[0]->pagesmeta_title) && !empty($meta[0]->pagesmeta_title))?$meta[0]->pagesmeta_title:'';
 		$this->data['meta_desc']=(isset($meta[0]->pagesmeta_desc) && !empty($meta[0]->pagesmeta_desc))?$meta[0]->pagesmeta_desc:'';
 		$this->data['h1_text']=(isset($meta[0]->h1_text) && !empty($meta[0]->h1_text))?$meta[0]->h1_text:'';
 		$this->data['additional_tag'] = (isset($meta[0]->additional_tag)&& !empty($meta[0]->additional_tag))?$meta[0]->additional_tag:'';
 
  		$this->render('public/cart/viewcart');
	}

	public function checkout(){
		
		//$promodata=applyPromocode('XYZPOP');
		//pr($promodata);die;

		//$this->session->unset_userdata('promocode'); die;

	    $this->data['page_title'] = 'Checkout';
		$this->data['extracss']='';
		$this->data['extrajs']= '';

		// $cart=getCart();
		// pr($cart); die;
		$this->data['selected_address']='';
		$this->data['address']=array();
		if ($this->session->has_userdata('user_id')) {
			$user_id = $this->session->userdata('user_id');
			$customer_id = !empty($user_id) ? $user_id : '';
			$customers = $this->customers_model->selectdata("*", array('id' => $customer_id), "ORDER BY id DESC LIMIT 1");

			$this->data['billing_name']=$customers[0]->name;
			$this->data['phone']=$customers[0]->mobile;
			$this->data['email']=$customers[0]->email;
 			 
			$this->data['address'] = $this->users_model->selectaddress("*", array('uid' => $user_id), "ORDER BY id DESC");
			//var_dump($this->db->last_query());
			$this->data['selected_address']=$this->session->has_userdata('selected_address')? $this->session->userdata('selected_address'):'';
			
			$meta=getMeta(0);
			$this->data['meta_title']=(isset($meta[0]->pagesmeta_title) && !empty($meta[0]->pagesmeta_title))?$meta[0]->pagesmeta_title:'';
			$this->data['meta_desc']=(isset($meta[0]->pagesmeta_desc) && !empty($meta[0]->pagesmeta_desc))?$meta[0]->pagesmeta_desc:'';
			$this->data['h1_text']=(isset($meta[0]->h1_text) && !empty($meta[0]->h1_text))?$meta[0]->h1_text:'';
			$this->data['additional_tag'] = (isset($meta[0]->additional_tag)&& !empty($meta[0]->additional_tag))?$meta[0]->additional_tag:'';
	
			$this->load->view('public/cart/checkout',$this->data);
 		}else{
			 redirect('login');
		 }

		
	}

	function extractCookie()
    {
        $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
        $data   = json_decode($cookie);
        foreach ($data as $key => $value) {
            //echo "Item ID: ".$key;
            pr($value);
            echo $value->qty . " " . $value->itemtype;
        }
	}
	

	function addItem(){
		$response =array();    
		$response["products"]=array();
        $response['data']  = array();

		$pid=$this->input->post('id', true);   
		$qnty=$this->input->post('qty', true);

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

		// if $saved_cart_items is null, prevent null error
		if(!$saved_cart_items){
			$saved_cart_items=array();
		}

		// check if the item is in the array, if it is, do not add
		if(array_key_exists($pid, $saved_cart_items)){
			// redirect to product list and tell the user it was already added to the cart
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
			$cart_subtotal=0;
			$actualcart_subtotal=0;			
			$total_discount=0;
			foreach($cart_items as $key=>$value){
				$product= $this->product_model->selectdata("*", array('pid'=> $key,'status'=>'active'), "ORDER BY pid DESC");
				$pid = $product[0]->pid;
				$cat_id=$product[0]->cat_id;
				$item_name = $product[0]->product_name;
 				$item_link = site_url('product/') . $product[0]->url_slug;
				$PImages = json_decode($product[0]->image_fids);
				$image = !empty($PImages[0]) ? $this->media->getThumbPathById($PImages[0], '600x800/') : FRONT_ASSETS_PATH . "images/default_product.png";
				$item_price=$product[0]->price;

				//Calculate discount
				$discount_status=0;
				$discount_price=0;
				$discount=getDiscount($cat_id,$pid);
				if($discount['discount_status']){
					$discount_status=1;
					$discount_price= $item_price - $discount['discounted_value'];
					$total_price += $discount_price;
				}else{
					$total_price += $item_price;
				}

				$col= array();
				$col['itemid']    = $key;
				$col['itemqty']    = $value;
				$col['itemname']  = $item_name;
				$col['itemlink']  = $item_link;
				$col['itemprice'] = $item_price;
				$col['itemimage'] = $image;
				$col['item_discount_status']=$discount_status;
				$col['item_discount_price'] = $discount_price;
			
				//$col['itemqty']=$itemqty;
				array_push($response['products'], $col);				
			}

			$response['status']      = "added";
            $response['totalitems']  = count($cart_items);
            $response['total_price'] = $total_price;
            $json                    = json_encode($cart_items, true);
            setcookie('cart_items_cookie', $json, time() + 2592000, "/");

		}
        echo json_encode($response, JSON_PRETTY_PRINT);
	} 

	function updateQnty(){
		$response =array();    
 
		$pid=$this->input->post('id', true);   
		$qnty=$this->input->post('qty', true);
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
	   
		##CHeck for refid exists or not
		$newQnty=0;
		foreach($saved_cart_items as $key=>$value){
			if($key==$pid && $qnty=='up'){
				$newQnty=$value+1;
				$cart_items[$key]=$newQnty;								
			}else if($key==$pid && $qnty=='down'){				
				$newQnty=$value-1;
				if ($newQnty <= 0) {
					$newQnty=$value;
				}
				$cart_items[$key]=$newQnty;								
			}else{
				$cart_items[$key]=$value;
			}
		}
			
		$json = json_encode($cart_items, true);
		setcookie('cart_items_cookie', $json, time() + 2592000, "/");
		
		//Get details of updated product
		$product= $this->product_model->selectdata("*", array('pid'=> $pid,'status'=>'active'), "ORDER BY pid DESC");
		$cat_id=$product[0]->cat_id;
		$item_price=$product[0]->price;
		//Calculate discount on product
		$discount_status=0;
		$discount_price=0;
		$discount=getDiscount($cat_id,$pid);
		if($discount['discount_status']){
			$discount_status=1;
			$discount_price= $item_price - $discount['discounted_value'];
			$item_subtotal = $discount_price*$newQnty;                     
 		}else{
			$item_subtotal = $item_price*$newQnty;                     
 		}
		//Get Updated Cart
		$cart=getnewCart($cart_items);
		//$response["products"]=$cart['products'];
		$response['item_subtotal'] = $item_subtotal;
		$response['total_items']=$cart['total_items'];
		$response['shipping_amount']=$cart['shipping_amount'];
		$response['cart_subtotal']=$cart['cart_subtotal'];
		$response['cart_total']=$cart['cart_total'];

 		echo json_encode($response,JSON_PRETTY_PRINT); 
	 }


	 function removeItem(){
		$response =array();    
		$pid=$this->input->post('id', true);

		// read
		$cookie = $_COOKIE['cart_items_cookie'];
		$cookie = stripslashes($cookie);
		$saved_cart_items = json_decode($cookie, true);

		// remove the item from the array
		if(array_key_exists($pid, $saved_cart_items)){
			unset($saved_cart_items[$pid]);
		}
		$json = json_encode($saved_cart_items, true);
		setcookie('cart_items_cookie', $json, time() + 2592000, "/");
		
		//Get Updated Cart
		$cart=getnewCart($saved_cart_items);
  		$response['total_items']=$cart['total_items'];
		$response['cart_subtotal']=$cart['cart_subtotal'];
		$response['shipping_amount']=$cart['shipping_amount'];
		$response['cart_total']=$cart['cart_total'];

		echo json_encode($response,JSON_PRETTY_PRINT); 		 
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
	 
	 function validatepromo(){
		//$this->load->model('promocode_model');   
		$response=array();   
		$promocode=$this->input->post('promocode');
		if ($this->session->has_userdata('user_id')) {
			$promodata=applyPromocode($promocode);
			if($promodata['status']=='success'){
				$response['promo_discounted_amount']=$promodata['promo_discounted_amount'];
				$response['promo_applied_cart_total']=$promodata['promo_applied_cart_total'];
				$response['status']="success";
			}else{
				$response['status'] = 'haserror';
				$response['error'] = $promodata['msg'];
			}
 		}else{
			$response['status'] = 'haserror';
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

	function completeOrder(){
		$response=array();   
 		$itemlist = '';
		$data=array();
		$selected_address=$this->input->post('selected_address');
		$payment_method=$this->input->post('payment_method');
		//$payment_method='cod';
		//$selected_address=1;

		if ($this->session->has_userdata('user_id') && !empty($payment_method)) {
 			
			$cookie           = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
			$cookie           = stripslashes($cookie);
			$saved_cart_items = json_decode($cookie, true);
 
			$cart_subtotal=0;
			$cart_total=0;
			$shipping_amount=0;
			$total_discount=0;
			if (count($saved_cart_items) > 0) {
				foreach ($saved_cart_items as $key => $value) {
						$product= $this->product_model->selectdata("*", array('pid'=> $key,'status'=>'active'), "ORDER BY pid DESC");
						
						$pid = $product[0]->pid;
						$qnty = $value;
						$cat_id=$product[0]->cat_id;
						$item_name = $product[0]->product_name;
						$item_link = site_url('product/') . $product[0]->url_slug;
						$PImages = json_decode($product[0]->image_fids);
						$image = !empty($PImages[0]) ? $this->media->getThumbPathById($PImages[0], '600x800') : FRONT_ASSETS_PATH . "images/default_product.png";
						$item_price=$product[0]->price;

						//Calculate discount
						$discount_status=0;
						$discount_price=0;
						$discount=getDiscount($cat_id,$pid);
						if($discount['discount_status']){
							$discount_status=1;
							$discount_price= $item_price - $discount['discounted_value'];
							$item_subtotal = $discount_price*$qnty;                     
						}else{
							$item_subtotal = $item_price*$qnty;                     
						}

						$cart_subtotal+=$item_subtotal;

						$col= array();
						$col['itemid']    = $key;
						$col['itemqnty']  = $value;
						$col['itemname']  = $item_name;
						$col['itemlink']  = $item_link;
						$col['itemimage'] = $image;
						$col['itemprice'] = $item_price;
						$col['item_discount_status']=$discount_status;
						$col['item_discount_price'] = $discount_price;
						$col['item_subtotal'] = $item_subtotal;
					
						//$col['itemqty']=$itemqty;
						array_push($data, $col);


						$itemlist .= '<tr>';
                        $itemlist .= '<td style="padding:5px;text-align:center;background:rgba(128,128,128,0.18)">' . $item_name . '</td>';
                        $itemlist .= '<td style="padding:5px;text-align:center;background:rgba(128,128,128,0.18)">' . $value . '</td>';
                        $itemlist .= '<td style="padding:5px;text-align:center;background:rgba(128,128,128,0.18)">₹​' . $item_price . '</td>';
                        $itemlist .= '<td style="padding:5px;text-align:right;background:rgba(128,128,128,0.18)">₹​' . $item_subtotal . '</td>';
                        $itemlist .= '</tr>';
				}

				$min_order_value=$this->dbsettings->MIN_CART_VALUE_DELIVERY;
				if($cart_subtotal<$min_order_value){
					$shipping_amount= $this->dbsettings->DELIVERY_CHARGES;
				}
				$cart_total=$cart_subtotal+$shipping_amount;

				$promocode=NULL;
				$promo_type=NULL;
				$promo_value=NULL;
				$promo_value_limit=NULL;
				
				if($this->session->has_userdata('promocode')){
					$promocode = $this->session->userdata('promocode');
					$promodata=validatePromocode($promocode,$saved_cart_items);
					if($promodata['status']=='success'){
						$cart_total=$promodata['promo_applied_cart_total'];
						$total_discount =$promodata['promo_discounted_amount'];
						$promo_type =$promodata['promo_type'];
						$promo_value =$promodata['promo_value'];
						$promo_value_limit =$promodata['promo_value_limit'];
					}
				}
			}

			$total_items=count($saved_cart_items);
			$cart_subtotal=$cart_subtotal;
			$total_discount=$total_discount;
			$shipping_amount=$shipping_amount;
			$total_amount=$cart_total+$shipping_amount;

			$order_wallet_amount=0;
			if($this->session->has_userdata('use_wallet')){
				$use_wallet=$this->session->userdata('use_wallet');
				if($use_wallet==1){
					$order_wallet_amount=getWallet();
				}
			}

			$invoice_no=randomstring();
			$user_id = $this->session->userdata('user_id');

			//Add order 
			$order_details=array('invoice_no'=>$invoice_no,
				'uid'=>$user_id,
				'address_id'=>$selected_address,
				'total_items'=>$total_items,
				'order_subtotal'=>$cart_subtotal,
				'shipping_amount'=>$shipping_amount,
				'promocode'=>$promocode,
				'promocode_type'=>$promo_type,
				'promocode_value'=>$promo_value,
				'promocode_value_limit'=>$promo_value_limit,
				'discount_amount'=>$total_discount,
				'wallet_amount'=>$order_wallet_amount,
				'total_amount'=>$total_amount,
				'payment_method'=>$payment_method,
				'created_at'=>date("Y-m-d H:i:s"),
				'updated_at'=>date("Y-m-d H:i:s")
			);
			//pr($order_details); 
			$this->db->insert('tbl_orders',$order_details);
			$order_id=$this->db->insert_id();
			//$order_id=1;

			//Insert Order address
			$address= $this->users_model->selectaddress("*", array('id' => $selected_address, 'uid' => $user_id), "ORDER BY id DESC");
			$customer_address = '';
			if (count($address) > 0) {
				foreach ($address as $ad) {
					$customer_address = $ad->addressline . ", " . $ad->addressline2 . ", " . $ad->city . ", " . $ad->state . ", India " . $ad->zipcode;
					$addressdata=array(
						'order_id'=> $order_id,
						'addressline' => $ad->addressline,
						'addressline2' => $ad->addressline2,
						'city' => $ad->city,
						'state' => $ad->state,
						'zipcode' => $ad->zipcode,
						'mobile'=>$ad->mobile,
						'date_added' => date("Y-m-d H:i:s"),
						'date_modified' => date("Y-m-d H:i:s")
					);
					$this->db->insert('tbl_order_address', $addressdata);
				}
			}



			//Insert Order items
			if (count($data) > 0) {
				foreach ($data as $items) {
					$co= array();
					$co['order_id']=$order_id;					 
					$co['item_id']=$items['itemid'] ;
					$co['item_name']=$items['itemname'];
					$co['item_price']=$items['itemprice'];
					$co['item_discounted_price']=$items['item_discount_price'];
					$co['item_qnty']=$items['itemqnty'];
					$co['date_added']=date('Y-m-d H:i:s');

					//pr($co);
					$this->db->insert('tbl_order_items',$co);
				}

				//Save Order Status
				$order_status_data=array(
					'order_id'=>$order_id,
					'order_status '=>0,
					'created_at'=>date('Y-m-d H:i:s'),                
					'updated_at'=>date('Y-m-d H:i:s')
				);
                $this->db->insert('tbl_order_status',$order_status_data);

				 
				//Add rewards points on order
				$wallet= $this->wallet_model->selectdata("*", array('uid' => $user_id,'status'=>'active'), "ORDER BY id DESC LIMIT 1");     
				$wallet_id=0;
				if(count($wallet)>0){
					$wallet_id=$wallet[0]->id;
					
					$current_wallet_amount=$wallet[0]->amount;
					//Insert Wallet transaction if wallet amount used in order
					if($this->session->has_userdata('use_wallet') && $this->session->userdata('use_wallet')==1 && !empty($wallet_id)){
						$this->session->unset_userdata('use_wallet');					
						$wt_data=array(
							'wallet_id'=>$wallet_id,
							'amount'=>$order_wallet_amount,
							'order_id'=>$order_id,
							'amount_type'=>'order',
							'operation' =>1,
							'status'=>'active',
							'date_added'=>date('Y-m-d H:i:s'),
							'date_modified'=>date('Y-m-d H:i:s')
						);
						$this->db->insert('tbl_wallet_transactions',$wt_data);		

						$current_wallet_amount=$current_wallet_amount-$order_wallet_amount;
						$this->db->where('id',$wallet_id);
						$this->db->update('tbl_wallet',array('amount'=>$current_wallet_amount));
					}

					$wallet_amount=($total_amount*$this->dbsettings->REWARD_POINTS_ON_ORDER)/100;
					$w_transaction=array(
						'uid'=>$user_id,
						'wallet_id'=>$wallet_id,
						'amount'=>$wallet_amount,
						'amount_type'=>'order',
						'operation' => 0,
						'status'=>'inactive',
						'date_added'=>date('Y-m-d H:i:s'),
						'date_modified'=>date('Y-m-d H:i:s')
					);
					$this->db->insert('tbl_wallet_transactions',$w_transaction);		
				}
						
				  //User info
                $user_info = $this->users_model->selectdata("*", array('id' => $user_id, 'status' => 'active'), "ORDER BY id DESC LIMIT 1");
                $customer_name = isset($user_info[0]) ? $user_info[0]->name : '';
                $customer_phone = isset($user_info[0]) ? $user_info[0]->mobile : '';
                $customer_email = isset($user_info[0]) ? $user_info[0]->email : '';

				 //Send Email
 
                #Mail content
                // $subject="Order Number #".$invoice_no." - Hello natur";
                // //$en_msg = file_get_contents(ALL_ASSETS_PATH . 'invoice.html');
                // $en_msg = str_replace('$customer_name', $customer_name);
                // $en_msg = str_replace('$customer_phone', $customer_phone, $en_msg);
                // $en_msg = str_replace('$customer_address', $customer_address, $en_msg);
                // $en_msg = str_replace('$itemlist', $itemlist, $en_msg);
                // $en_msg = str_replace('$subtotal', $cart_subtotal, $en_msg);
                // $en_msg = str_replace('$shipping_charge', $shipping_amount, $en_msg);
                // $en_msg = str_replace('$discount', $total_discount, $en_msg);
                // $en_msg = str_replace('$wallet_amount', $order_wallet_amount, $en_msg);
                // $en_msg = str_replace('$grand_total', $total_amount, $en_msg);
 
                // //echo $en_msg;die;
                // sendmail($customer_email, $customer_name, $subject, $en_msg);


				$this->session->set_userdata('message',"Your order has been placed successfully.");
				setcookie('cart_items_cookie','',time() + 2592000, "/");
				$this->session->unset_userdata('promocode');

			 }
			 
			 
			$response['status'] = 'success';

		}else{
			$response['status'] = 'haserror';
            $response['error'] = 'Invalid Request';
		}
		echo json_encode($response);
	}


	function success(){
		$this->data['page_title'] = 'View Cart';
		$this->data['extracss']='';
		$this->data['extrajs']= '';

		$data=array();
		$selected_address=$this->session->userdata('selected_address');
		$payment_method='razorpay';
		   
		if ($this->session->has_userdata('user_id') && !empty($payment_method) && $this->session->has_userdata('transaction_data')) {
 			
			$transaction_data=$this->session->userdata('transaction_data');
			$invoice_no=$transaction_data['order_id']; 
			
			$cookie           = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : '';
			$cookie           = stripslashes($cookie);
			$saved_cart_items = json_decode($cookie, true);
 			$itemlist = '';
			$cart_subtotal=0;
			$cart_total=0;
			$shipping_amount=0;
			$total_discount=0;
			if (count($saved_cart_items) > 0) {
				foreach ($saved_cart_items as $key => $value) {
						$product= $this->product_model->selectdata("*", array('pid'=> $key,'status'=>'active'), "ORDER BY pid DESC");
						
						$pid = $product[0]->pid;
						$qnty = $value;
						$cat_id=$product[0]->cat_id;
						$item_name = $product[0]->product_name;
						$item_link = site_url('product/') . $product[0]->url_slug;
						$PImages = json_decode($product[0]->image_fids);
						$image = !empty($PImages[0]) ? $this->media->getThumbPathById($PImages[0], '600x800') : FRONT_ASSETS_PATH . "images/default_product.png";
						$item_price=$product[0]->price;

						//Calculate discount
						$discount_status=0;
						$discount_price=0;
						$discount=getDiscount($cat_id,$pid);
						if($discount['discount_status']){
							$discount_status=1;
							$discount_price= $item_price - $discount['discounted_value'];
							$item_subtotal = $discount_price*$qnty;                     
						}else{
							$item_subtotal = $item_price*$qnty;                     
						}

						$cart_subtotal+=$item_subtotal;

						$col= array();
						$col['itemid']    = $key;
						$col['itemqnty']  = $value;
						$col['itemname']  = $item_name;
						$col['itemlink']  = $item_link;
						$col['itemimage'] = $image;
						$col['itemprice'] = $item_price;
						$col['item_discount_status']=$discount_status;
						$col['item_discount_price'] = $discount_price;
						$col['item_subtotal'] = $item_subtotal;
						
						$itemlist .= '<tr>';
                        $itemlist .= '<td style="padding:5px;text-align:center;background:rgba(128,128,128,0.18)">' . $item_name . '</td>';
                        $itemlist .= '<td style="padding:5px;text-align:center;background:rgba(128,128,128,0.18)">' . $value . '</td>';
                        $itemlist .= '<td style="padding:5px;text-align:center;background:rgba(128,128,128,0.18)">₹​' . $item_price . '</td>';
                        $itemlist .= '<td style="padding:5px;text-align:right;background:rgba(128,128,128,0.18)">₹​' . $item_subtotal . '</td>';
                        $itemlist .= '</tr>';
						//$col['itemqty']=$itemqty;
						array_push($data, $col);
				}

				$min_order_value=$this->dbsettings->MIN_CART_VALUE_DELIVERY;
				if($cart_subtotal<$min_order_value){
					$shipping_amount= $this->dbsettings->DELIVERY_CHARGES;
				}
				$cart_total=$cart_subtotal+$shipping_amount;

				$promocode=NULL;
				$promo_type=NULL;
				$promo_value=NULL;
				$promo_value_limit=NULL;
				
				if($this->session->has_userdata('promocode')){
					$promocode = $this->session->userdata('promocode');
					$promodata=validatePromocode($promocode,$saved_cart_items);
					if($promodata['status']=='success'){
						$cart_total=$promodata['promo_applied_cart_total'];
						$total_discount =$promodata['promo_discounted_amount'];
						$promo_type =$promodata['promo_type'];
						$promo_value =$promodata['promo_value'];
						$promo_value_limit =$promodata['promo_value_limit'];
					}
				}
			}

			$total_items=count($saved_cart_items);
			$cart_subtotal=$cart_subtotal;
			$total_discount=$total_discount;
			$shipping_amount=$shipping_amount;
			$total_amount=$cart_total+$shipping_amount;

			$order_wallet_amount=0;
			if($this->session->has_userdata('use_wallet')){
				$use_wallet=$this->session->userdata('use_wallet');
				if($use_wallet==1){
					$order_wallet_amount=getWallet();
					$total_amount=$total_amount-$order_wallet_amount;
				}
			}
			
			//$invoice_no=randomstring();
			$user_id = $this->session->userdata('user_id');


			//Add order 
			$order_details=array('invoice_no'=>$invoice_no,
				'uid'=>$user_id,
				'address_id'=>$selected_address,
				'total_items'=>$total_items,
				'order_subtotal'=>$cart_subtotal,
				'shipping_amount'=>$shipping_amount,
				'promocode'=>$promocode,
				'promocode_type'=>$promo_type,
				'promocode_value'=>$promo_value,
				'promocode_value_limit'=>$promo_value_limit,
				'wallet_amount'=>$order_wallet_amount,
				'discount_amount'=>$total_discount,
				'total_amount'=>$total_amount,
				'payment_method'=>$payment_method,
				'created_at'=>date("Y-m-d H:i:s"),
				'updated_at'=>date("Y-m-d H:i:s")
			);

			//pr($order_details); 
			$this->db->insert('tbl_orders',$order_details);
			$order_id=$this->db->insert_id();
			//$order_id=1;
			//Transaction data
			$transaction_data['d_order_id']=$order_id;
			$this->db->insert('tbl_transaction',$transaction_data);
 
			//Insert Order address
			$address= $this->users_model->selectaddress("*", array('id' => $selected_address, 'uid' => $user_id), "ORDER BY id DESC");
			if (count($address) > 0) {
				foreach ($address as $ad) {
					$addressdata=array(
						'order_id'=> $order_id,
						'addressline' => $ad->addressline,
						'addressline2' => $ad->addressline2,
						'city' => $ad->city,
						'state' => $ad->state,
						'zipcode' => $ad->zipcode,
						'mobile'=>$ad->mobile,
						'date_added' => date("Y-m-d H:i:s"),
						'date_modified' => date("Y-m-d H:i:s")
					);
					$this->db->insert('tbl_order_address', $addressdata);
				}
			}

			//Insert Order items
			if (count($data) > 0) {
				foreach ($data as $items) {
					$co= array();
					$co['order_id']=$order_id;					 
					$co['item_id']=$items['itemid'] ;
					$co['item_name']=$items['itemname'];
					$co['item_price']=$items['itemprice'];
					$co['item_discounted_price']=$items['item_discount_price'];
					$co['item_qnty']=$items['itemqnty'];
					$co['date_added']=date('Y-m-d H:i:s');

					//pr($co);
					$this->db->insert('tbl_order_items',$co);
				}

				//Save Order Status
				$order_status_data=array(
					'order_id'=>$order_id,
					'order_status '=>0,
					'created_at'=>date('Y-m-d H:i:s'),                
					'updated_at'=>date('Y-m-d H:i:s')
				);
                $this->db->insert('tbl_order_status',$order_status_data);

				
				//Add rewards points on order
				$wallet= $this->wallet_model->selectdata("*", array('uid' => $user_id,'status'=>'active'), "ORDER BY id DESC LIMIT 1");     
				if(count($wallet)>0){
					$wallet_id=$wallet[0]->id;
					
					$current_wallet_amount=$wallet[0]->amount;
					//Insert Wallet transaction if wallet amount used in order
					if($this->session->has_userdata('use_wallet') && $this->session->userdata('use_wallet')==1 && !empty($wallet_id)){
						$this->session->unset_userdata('use_wallet');					
						$wt_data=array(
							'wallet_id'=>$wallet_id,
							'amount'=>$order_wallet_amount,
							'order_id'=>$order_id,
							'amount_type'=>'order',
							'operation' =>1,
							'status'=>'active',
							'date_added'=>date('Y-m-d H:i:s'),
							'date_modified'=>date('Y-m-d H:i:s')
						);
						$this->db->insert('tbl_wallet_transactions',$wt_data);		

						$current_wallet_amount=$current_wallet_amount-$order_wallet_amount;
						$this->db->where('id',$wallet_id);
						$this->db->update('tbl_wallet',array('amount'=>$current_wallet_amount));
					}
					
					$wallet_amount=($total_amount*$this->dbsettings->REWARD_POINTS_ON_ORDER)/100;
					$w_transaction=array(
						'uid'=>$user_id,
						'wallet_id'=>$wallet_id,
						'amount'=>$wallet_amount,
						'amount_type'=>'order',
						'operation' => 0,
						'status'=>'inactive',
						'date_added'=>date('Y-m-d H:i:s'),
						'date_modified'=>date('Y-m-d H:i:s')
					);
					$this->db->insert('tbl_wallet_transactions',$w_transaction);		
				}

				 //User info
                $user_info = $this->users_model->selectdata("*", array('id' => $user_id, 'status' => 'active'), "ORDER BY id DESC LIMIT 1");
                $customer_name = isset($user_info[0]) ? $user_info[0]->name : '';
                $customer_phone = isset($user_info[0]) ? $user_info[0]->mobile : '';
                $customer_email = isset($user_info[0]) ? $user_info[0]->email : '';

				//Send Email
 
                #Mail content
                $subject="Order Number #".$invoice_no." - Dbamy";
                $en_msg = file_get_contents(ALL_ASSETS_PATH . 'invoice.html');
                $en_msg = str_replace('$customer_name', $customer_name, $en_msg);
                $en_msg = str_replace('$customer_phone', $customer_phone, $en_msg);
                $en_msg = str_replace('$customer_address', $address, $en_msg);
                $en_msg = str_replace('$itemlist', $itemlist, $en_msg);
                $en_msg = str_replace('$subtotal', $cart_subtotal, $en_msg);
                $en_msg = str_replace('$shipping_charge', $shipping_amount, $en_msg);
                $en_msg = str_replace('$discount', $total_discount, $en_msg);
                $en_msg = str_replace('$wallet_amount', $order_wallet_amount, $en_msg);
                $en_msg = str_replace('$grand_total', $total_amount, $en_msg);
 
                //echo $en_msg;die;
                sendmail($customer_email, $customer_name, $subject, $en_msg);
											 
				$this->session->set_userdata('message',"Your order has been placed successfully.");
				
				setcookie('cart_items_cookie','',time() + 2592000, "/");
				$this->session->unset_userdata('promocode');
				$this->session->unset_userdata('transaction_data');

			 }
			 
			$this->data['invoice_no']=$invoice_no;
			$this->render('public/cart/success');

		}else{
			 redirect('user/orders');
		}
 
  	}
	
	function usewallet(){
		$response=array();
		$use_wallet=$this->input->post('use_wallet');
		//$cart_total=$this->input->post('cart_total');

		if($use_wallet==1){
 			$this->session->set_userdata('use_wallet','1');			
		}else {
			$this->session->unset_userdata('use_wallet');
		}

		$response['status']='success';
		echo json_encode($response);
	}

}

