<?php

/*  
|--------------------------------------------------------------------------
| Web Routes 
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    return "Cache is cleared"; 
});

Route::get('/test', "MainController@test")->name('test');

Route::get('/', "MainController@frontendIndex")->name('index');

Route::get('/about-us', "MainController@about")->name('about');

Route::get('/repair-and-services', "MainController@repairServices")->name('repairServices');

Route::get('/contact-us', "MainController@contact")->name('contact');

Route::post('/contact-us/send', 'InquiryController@sendcontactmail')->name('contactMail');

Route::post('/watch/send', 'InquiryController@sendwatchmail')->name('watchMail');
 
Route::get('/watches/{name}/{slug}', 'MainController@watchFinder')->name('watchFinder');

Route::get('/blog', "MainController@blog")->name('blog');

Route::get('/blog/{slug}', "MainController@blogDetail")->name('blogView');

Route::get('/brand', "MainController@brandSearch")->name('brandSearch');
Route::post('/searchbar', "MainController@searchbar")->name('searchbar');

Route::get('/brand/{slug}', "MainController@brand")->name('brand');

Route::get('/product', "MainController@product")->name('product');

Route::get('/accessories', "MainController@accessories")->name('accessories');

Route::get('/product/{id}', "MainController@productDetail")->name('productView');

Route::get('/get-brand', "MainController@getBrand")->name('getBrand');

Route::any('/filter', "MainController@filters")->name('filter');

Route::post('/acc-filter', "MainController@accfilters")->name('accfilter');

Route::post('/sale-filter', "MainController@sale_filters")->name('salefilter');

Route::get('/sale', "MainController@sale")->name('sale');

Route::get('/store', "MainController@store")->name('store'); 


Route::get('/imageForm', "DropdownController@imageForm")->name('imageForm');

Route::post('/image-upload/upload', "DropdownController@image_upload")->name('image_upload');

Route::get('/404', "MainController@pageNotFound")->name('error');



Route::get('/user-login', "MainController@userlogin")->name('userlogin');
Route::post('/userloginCheck', "MainController@userloginCheck")->name('loginCheck');

Route::get('/user-register', "MainController@userRegister")->name('userRegister');

Route::post('/user-insert', "InquiryController@sendverificationMail")->name('user-insert');

  

Route::get('/email-varification', "MainController@emailVarification")->name('emailVarification');




Route::get('/user-profile/', "MainController@userDashboard")->name('user-profile');
Route::get('/user-address/', "MainController@userAddress")->name('user-address');
Route::get('/user-orders/', "MainController@userOrders")->name('user-orders');
Route::post('/updateProfile/', "MainController@updateProfile")->name('updateProfile');
Route::get('/change-password/', "MainController@changePassword")->name('change-password');
Route::post('/changeUsrPassword/', "MainController@changeUsrPassword")->name('changeUsrPassword');
Route::get('/edit-address', "MainController@editAddress")->name('edit-address');
Route::post('/addAddressForm', "MainController@addAddressForm")->name('addAddressForm');
Route::post('/UpdateAddressForm', "MainController@UpdateAddressForm")->name('UpdateAddressForm');
Route::post('/deleteAddressForm', "MainController@deleteAddressForm")->name('deleteAddressForm');
Route::post('/viewAddressForm', "MainController@viewAddressForm")->name('viewAddressForm');
Route::post('/CheckoutProduct', "MainController@CheckoutProduct")->name('CheckoutProduct');
Route::get('/OrderDetails/{id}', "MainController@OrderDetails")->name('OrderDetails');
Route::get('/CancelOrder/{id}', "MainController@CancelOrder")->name('CancelOrder');
Route::get('/sendOrdermail/{id}', "MainController@sendOrdermail")->name('sendOrdermail');


Route::match('/edit-address/{id?}', "MainController@editAddress")->name('edit-address');


Route::get('/user-logout', "MainController@userLogout")->name('user-logout');
/*Route::post('/cart', 'MainController@Cart')->name('cart');*/




Route::match(['get','post'], '/add-to-cart', 'MainController@addToCart')->name('addToCart');


Route::match(['get','post'], '/removeitem', 'MainController@RemoveItem')->name('removeitem');
Route::match(['get','post'], '/removeAllitem', 'MainController@RemoveAllitem')->name('removeAllitem');

Route::post('/remove-cart-item', 'MainController@removeCartitem')->name('removeCartitem');


Route::get('/checkout-page', 'MainController@checkoutPage')->name('checkoutPage');
Route::get('/checkout', 'MainController@checkout')->name('checkout');


Route::post('/update-cart-qnty', 'MainController@updateQnty')->name('updateQnty');
Route::post('/validate-promo', 'MainController@validatepromo')->name('validatepromo');

// Get Route For Show Payment Form
Route::get('/PaymentPage', 'MainController@PaymentPage')->name('PaymentPage');
// Post Route For Makw Payment Request
Route::post('payment', 'MainController@payment')->name('payment');

 

/*------------------------------------------------ Admin route ------------------------------------------------*/

Route::Group(['prefix' => 'admin', 'middleware' => ['auth']], function () {

Route::get('/', function () {
    return view('admin.index');
})->name('admin');

Route::get('/changepassword', "DropdownController@passwordedit")->name('admin.passwordEdit');

Route::put('/changepassword/update', "DropdownController@changepassword")->name('admin.updatePassword');

});

/*------------------------------------------------ Admin route end ---------------------------------------------*/

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
// Route::get('{path}','HomeController@index')->where( 'path', '([A-z\d\-\/_.]+)?' );
Route::Group(['middleware' => ['auth']], function () {
    Route::get('/admin/{any}', function () {
        return view('admin.index');
    })->where('any', '.*');
});



////====----user login----====/////

