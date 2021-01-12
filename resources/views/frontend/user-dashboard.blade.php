@extends('frontend/common/webmaster')
@section('title'," | $seo->meta_title")

@section('content') 
   <div class="page-wrapper">
        <main class="main">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </div><!-- End .container -->
            </nav>

            <div class="container">
                <div class="row">
                    <div class="col-lg-9 order-lg-last dashboard-content">
                        
                        <h3>Account Information</h3>

                        <div class="row" id="myOrder">
                            <div class="col-md-8">
                                <div class="card" >
                                    <div class="card-header">
                                        Contact Information
                                        <a href="javascript:void(0);" data-toggle="modal" data-target="#chngeCotactModal" class="card-edit">Edit</a>
                                    </div><!-- End .card-header -->

                                    <div class="card-body">

                                        @foreach($usersData as $data)
                                        <?php $usr_mob = $data->phone;
                                        $usr_name = $data->name; ?>
                                        <p>
                                           {{$data->name}}
                                           <br>
                                             {{$data->email}}<br>
                                             {{$data->phone}}
                                        </p>
 
                                        @endforeach
                                    </div><!-- End .card-body -->
                                </div><!-- End .card -->
                            </div><!-- End .col-md-6 -->

                        
                        </div><!-- End .row -->

                    </div><!-- End .col-lg-9 -->

                    <aside class="sidebar col-lg-3">
                        <div class="widget widget-dashboard">
                            <h3 class="widget-title">My Account</h3>

                            <ul class="list">
                                <li class="active"><a href="{{route('user-profile')}}">Account Information</a></li>
                                <li><a href="{{route('user-address')}}">Address Book</a></li>
                                <li><a href="{{route('user-orders')}}">My Orders</a></li>
                                <li><a href="{{route('change-password')}}">Change Password</a></li>
                            </ul>
                        </div><!-- End .widget -->
                    </aside><!-- End .col-lg-3 -->
                </div><!-- End .row -->
            </div><!-- End .container -->

            <div class="mb-5"></div><!-- margin -->
        </main><!-- End .main -->

        <div class="modal fade" id="chngeCotactModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Profile Info</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <form action="{{ route('updateProfile') }}" method="POST" id="update-profile">
                    @csrf
      <div class="modal-body">
                    <div class="form-group required-field">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" value="{{$usr_name}}" id="updatename" name="name" required>
                    </div><!-- End .form-group -->

                    <div class="form-group">
                        <label for="mob_no">Phone Number</label>
                        <input type="tel" class="form-control" value="{{$usr_mob}}" id="mob_no" name="mob_no">
                    </div><!-- End .form-group -->

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
                </form>
    </div>
  </div>
</div>
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