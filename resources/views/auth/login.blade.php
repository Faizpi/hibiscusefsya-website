@extends('layouts.app')

@section('content')

<style>
    body {
        background: #ffffff !important; /* Override background biru dari layout */
    }
</style>

<div style="background:#ffffff; min-height:100vh; width:100%; padding:40px 0;">

    <div class="container">

        <div class="row justify-content-center align-items-center" style="min-height:80vh;">

            {{-- Logo Desktop (samping form) --}}
            <div class="col-lg-6 text-center d-none d-lg-block">
                <img src="{{ asset('assets/img/logoHE.png') }}" alt="Hibiscus Efsya Logo" class="img-fluid px-5">
                <h4 class="text-gray-700 mt-4">Platform Akuntansi Online No. 1 di Indonesia</h4>
            </div>

            <div class="col-lg-6">
                {{-- Logo Mobile (di atas form) --}}
                <div class="text-center d-lg-none mb-4">
                    <img src="{{ asset('assets/img/logoHE.png') }}" alt="Hibiscus Efsya Logo" style="max-width: 200px; height: auto;">
                </div>
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Selamat Datang!</h1>
                            </div>

                            <form class="user" method="POST" action="{{ route('login') }}">
                                @csrf
                                
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-user @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" placeholder="Masukkan Email..." required>
                                    @error('email')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input type="password" class="form-control form-control-user @error('password') is-invalid @enderror"
                                        name="password" placeholder="Kata Sandi" required>
                                    @error('password')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox small">
                                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                        <label class="custom-control-label" for="remember">Ingat Saya</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Login
                                </button>

                                <hr>
                            </form>

                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@endsection
