@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', 'Netley · Portal Cliente')

@section('auth_body')
    <form action="{{ route('portal.login') }}" method="POST">
        @csrf

        <label for="usuario" class="visually-hidden">Usuario</label>
        <div class="input-group mb-3">
            <input type="text" name="usuario" id="usuario"
                class="form-control @error('usuario') is-invalid @enderror"
                value="{{ old('usuario') }}" placeholder="Usuario" autofocus>
            <div class="input-group-text"><span class="bi bi-person"></span></div>
            @error('usuario')
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
