<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoriaDocumento;
use App\Enums\OrigenDocumento;
use App\Http\Controllers\Controller;
use App\Models\Proceso;
use App\Models\ProcesoDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcesoDocumentoController extends Controller
{
    public function store(Request $request, Proceso $proceso): RedirectResponse
    {
        $data = $request->validate([
            'archivo' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
            'categoria' => ['required', Rule::enum(CategoriaDocumento::class)],
            'nombre' => ['nullable', 'string', 'max:255'],
        ], [
            'archivo.max' => 'El archivo no puede pesar más de 10 MB.',
            'archivo.mimes' => 'Formato no permitido. Se aceptan: PDF, imágenes (JPG/PNG), Word y Excel.',
        ]);

        /** @var \Illuminate\Http\UploadedFile $archivo */
        $archivo = $data['archivo'];
        $ruta = $archivo->store("proceso_documentos/{$proceso->id}", 'public');

        $proceso->documentos()->create([
            'categoria' => $data['categoria'],
            'nombre' => $data['nombre'] ?: $archivo->getClientOriginalName(),
            'archivo' => $ruta,
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime_type' => $archivo->getClientMimeType(),
            'tamano' => $archivo->getSize(),
            'origen' => OrigenDocumento::Staff,
            'user_id' => $request->user('web')->id,
        ]);

        return redirect()->route('admin.procesos.show', $proceso)->with('status', 'Documento subido correctamente.');
    }

    public function download(ProcesoDocumento $documento): StreamedResponse
    {
        if (! Storage::disk('public')->exists($documento->archivo)) {
            abort(404, 'El archivo ya no está disponible en el servidor.');
        }

        return Storage::disk('public')->download($documento->archivo, $documento->nombre_original ?: $documento->nombre);
    }

    public function destroy(ProcesoDocumento $documento): RedirectResponse
    {
        $proceso = $documento->proceso;

        $documento->delete();

        return redirect()->route('admin.procesos.show', $proceso)->with('status', 'Documento eliminado correctamente.');
    }
}
