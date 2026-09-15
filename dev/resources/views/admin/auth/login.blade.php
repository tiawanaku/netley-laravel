@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', 'Netley · Admin')

@section('auth_body')
    <form action="{{ route('admin.login') }}" method="POST">
        @csrf

        <label for="email" class="visually-hidden">Correo electrónico</label>
        <div class="input-group mb-3">
            <input type="email" name="email" id="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="Correo electrónico" autofocus>
            <div class="input-group-text"><span class="bi bi-envelope"></span></div>
            @error('email')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <label for="password" class="visually-hidden">Contraseña</label>
        <div class="input-group mb-3">
            <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Contraseña">
            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
            @error('password')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="row">
            <div class="col-7">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
            </div>
            <div class="col-5">
                <button type="submit" class="btn btn-primary btn-block w-100">Ingresar</button>
            </div>
        </div>
    </form>
@stop
