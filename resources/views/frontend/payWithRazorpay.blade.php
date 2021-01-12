@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")

@section('content') 
   <div class="page-wrapper">
        <main class="main">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payment</li>
                    </ol>
                </div><!-- End .container -->
            </nav>
            <style type="text/css">
                .razorpay-payment-button{
                    color: #fff;
                    border-color: #32394a;
                    background-color: #32394a;
                    cursor: pointer;width: 200px;
                    padding: 10px;
                }
            </style>
            <div class="container">
            <div class="row">
        <div class="col-md-12 col-md-offset-12">
            @if($message = Session::get('error'))
                <div class="alert alert-danger alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <strong>Error!</strong> {{ $message }}
                </div>
            @endif
            {!! Session::forget('error') !!}
            @if($message = Session::get('success'))
                <div class="alert alert-info alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <strong>Success!</strong> {{ $message }}
                </div>
            @endif
            {!! Session::forget('success') !!}
            <div class="panel panel-default">
                <!-- <div class="panel-heading">Payment</div> -->

                <div class="panel-body text-center">
                    <h3>₹ <?= $payAmt; ?></h3>
                    <form action="{!!route('payment')!!}" method="POST" >
                        <!-- Note that the amount is in paise = 50 INR -->
                        <!--amount need to be in paisa-->
                        <script src="https://checkout.razorpay.com/v1/checkout.js"
                                data-key="{{ Config::get('key.razor_key') }}"
                                data-amount="<?= $payAmt*100; ?>"
                                data-buttontext="Pay Now"
                                data-name="Lahore Watch Co."
                                data-description="Payment Amount"
                                data-image="{{ URL::asset('public/frontend/assets/images/logo.png') }}"
                                data-prefill.name="name"
                                data-prefill.email="email"
                                data-theme.color="#282d3b">
                        </script>
                        <input type="hidden" name="_token" value="{!!csrf_token()!!}">
                        <input type="hidden" name="payAmt" value="<?= $payAmt; ?>">
                        <input type="hidden" name="payOption" value="<?= $pay_option; ?>">
                        <input type="hidden" name="address_id" value="<?= $address_id; ?>">
                    </form>
                </div>
                <a class="btn btn-theme btn_back" href="{{route('checkout')}}"><i class="fa fa-arrow-left" aria-hidden="true"></i>
 Go Back</a>
            </div>
        </div>
    </div>
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

    <script type="text/javascript">           
        $(document).ready(function() { 
            $(function() {     
                $('.razorpay-payment-button').click(); 
            }); 
        });
    </script>

@endsection

@section('extrascript')
@endsection


