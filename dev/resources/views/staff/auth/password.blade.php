@extends('layouts.staff')

@section('page_title', 'Cambiar contraseña')

@section('content')
    <div class="card card-outline card-primary" style="max-width: 480px;">
        <div class="card-header">
            <h3 class="card-title">Debes cambiar tu contraseña</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('staff.password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="password" class="form-label">Nueva contraseña</label>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>
@stop
