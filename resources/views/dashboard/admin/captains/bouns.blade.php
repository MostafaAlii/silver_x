@extends('layouts.master')
@section('css')
@section('title')
{{$data['captain']->owner->name . ' ' .$data['title']}}
@stop
@endsection
@section('page-header')
<!-- breadcrumb -->
<div class="page-title">
    <div class="row">
        <div class="col-sm-6">
            <h4 class="mb-0">{{$data['captain']->owner->name . ' ' .$data['title']}}</h4>
        </div>
        <div class="col-sm-6">
            <ol class="float-left pt-0 pr-0 breadcrumb float-sm-right ">
                <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="default-color">Dasboard</a></li>
                <li class="breadcrumb-item"><a href="{{route('captains.show', $data['captain']->uuid)}}" class="default-color">{{$data['captain']->owner->name . ' Profile'}}</a></li>
                <li class="breadcrumb-item active text-success">{{$data['captain']->owner->name . ' ' .$data['title']}}</li>
            </ol>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection
@section('content')
@include('layouts.common.partials.messages')
<!-- start profile content -->
<div class="profile-page">
    <!-- start profile-page-container -->
    <!-- Start User Info -->
    <div class="row">
        <div class="col-lg-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="user-bg" style="background: url({{asset('assets/images/user-bg.jpg') }});">
                        <div class="user-info">
                            <div class="row">
                                <div class="col-lg-4 align-self-center">
                                    <div class="user-dp"><img
                                            src="{{ $data['captain']?->avatar ? asset('dashboard/images/driver_document/' . $data['captain']->owner->email . $data['captain']->owner->phone . '_' . $data['captain']->uuid  . '/' . $data['captain']->avatar) : asset('dashboard/default/default_admin.jpg') }}"
                                            alt="{{$data['captain']->owner->name}}"></div>
                                    <div class="user-detail">
                                        <h4 class="nametext-light">
                                            <p class="mb-0">
                                                <span style="font-size: 12px;" class="fa fa-circle text-{{ $data['captain']->owner->status == 'active' ? 'success' : 'danger' }}"></span>
                                                <i class="fa {{ $data['captain']->owner->gender == 'male' ? 'fa-male text-primary' : 'fa-female text-purple' }}"></i>
                                                {{$data['captain']->owner->name}}
                                            </p> 
                                            <p class="mb-0">{{$data['captain']->owner->email}}</p>
                                            <p class="mb-0">{{'Phone' . $data['captain']->owner->phone}}</p>                                           
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End User Info -->

    <!-- Start Content and Tabs -->
    <div class="row">
        <!-- Start Tabs -->
        <div class="col-xl-12 col-lg-12 mb-30">
            <div class="card mb-30">
                <div class="card-body">
                    <h5 class="card-title border-0 pb-0">{{$data['captain']->owner->name . ' Bouns'}}</h5>
                    <div class="table-responsive">
                        <table class="mb-0 table">
                            <thead class="thead">
                                <tr>
                                    <th scope="col">Bouns</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Update At</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data['captain']->bounus as $bouns)
                                <tr>
                                    <td>{{$bouns->bout}}</td>
                                    <td>{{$bouns->status}}</td>
                                    <td>{{ $bouns->created_at->diffForHumans() }}</td>
                                    <td>{{ $bouns->updated_at->diffForHumans() }}</td>
                                    <td>
                                        <a class="modal-effect btn btn-sm btn-info" data-effect="effect-scale" data-toggle="modal" href="#editStatus{{$bouns->id}}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @include('dashboard.admin.captains.btn.modals.updateBounsStatus')
                                @empty
                                    
                                @endforelse
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Tabs -->
    </div>
    <!-- End Content and Tabs -->
    <!-- End profile-page-container -->
</div>
<!-- end profile content -->
@endsection
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
    integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>

</script>
@endsection