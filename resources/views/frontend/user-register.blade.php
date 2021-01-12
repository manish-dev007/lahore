@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")

@section('content') 


<style>
    .main{ width:40%; margin: 5% auto;  }
</style>
   <div class="page-wrapper">
<main class="main">
<div class="mfp-cont"><div class="modal-wrapper">
    <div class="container">
        <div class="row row-sparse">

 
            <div class="col-md-12">
                <h2 class="title mb-2">Register</h2>

                <form class="registre_user" action="{{route('user-insert')}}" method="post">
                    @csrf

                    <label for="register-email">User Name <span class="required">*</span></label>
                    <input name="name" type="name" class="form-input form-wide mb-2" id="register-name" required="">
 

                    <label for="register-email">Email address <span class="required">*</span></label>
                    <input name="email" type="email" class="form-input form-wide mb-2" id="register-email" required="">

                     <label for="register-phone">Phone <span class="required">*</span></label>
                    <input name="phone" type="phone" class="form-input form-wide mb-2" id="register-phone" required="">    

                    <label for="register-password">Password <span class="required">*</span></label>
                    <input name="password" type="password" class="form-input form-wide mb-2" id="register-password" required="">

                    <label for="register-password">Confirm Password <span class="required">*</span></label>
                    <input name="cpassword" type="password" class="form-input form-wide mb-2" id="register-cpassword" required="">

                    

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary btn-md">Register</button>


                            @if(session()->has('success'))
                                    <div class=" alert alert-success">{{ session()->get('success') }}</div>
                                    @endif
                                    @if(session()->has('Error'))
                                    <div class=" alert alert-danger">{{ session()->get('Error') }}</div>
                                    @endif
                        <p style="position: absolute;right: 15px;">Alredy registered? <a href="{{ route('userlogin') }}" class="forget-password">Login here</a></p>
                    </div><!-- End .form-footer -->
                </form>
            </div><!-- End .col-md-6 -->






        </div><!-- End .row -->
    </div><!-- End .container -->


                            </main><!-- End .main -->
</div>
<script>
     $(".registre_user").submit(function(event){
      event.preventDefault();
      var uname = $("#register-name").val();
      var uemail = $("#register-email").val();
      var uphone = $("#register-phone").val();
      var password = $("#register-password").val();
      var cpassword = $("#register-cpassword").val();

      if(uname == ''){
        alert('Please Enter your Name..');
        $("#register-name").focus();
      }else if(uemail == ''){
        alert('Please Enter your Email..');
        $("#register-email").focus();
      }else if(uphone == ''){
        alert('Please Enter your Phone..');
        $("#register-phone").focus();
      }else if(password == ''){
        alert('Please Enter password..');
        $("#register-password").focus();
      }else if(cpassword == ''){
        alert('Please Confirm the Password..');
        $("#register-cpassword").focus();
      }else if(cpassword != password){
        alert('Password and Confirm Password Should Match..');
        $("#register-cpassword").focus();
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
                        alert('Already Registered..Please login to Continue..')
                    }else{
                         window.location.href = 'user-login';
                    }
               }

           });
      }      

   });
</script>
   <!-- Plugins JS File -->
    <script src="{{ URL::asset('public/frontend/assets/js/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('public/frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::asset('public/frontend/assets/js/plugins.min.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ URL::asset('public/frontend/assets/js/main.min.js') }}"></script>
@endsection

@section('extrascript')
@endsection