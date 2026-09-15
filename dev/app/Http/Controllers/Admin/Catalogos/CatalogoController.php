<?php

namespace App\Http\Controllers\Admin\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\MateriaLegal;
use App\Models\Origen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * MateriaLegal, Origen y Departamento son CRUDs idénticos (nombre, slug,
 * activo, orden) — un solo controller resuelve el modelo desde la ruta en
 * vez de triplicar la misma lógica (NET-004 en docs/).
 */
class CatalogoController extends Controller
{
    protected array $catalogos = [
        'materias-legales' => ['modelo' => MateriaLegal::class, 'titulo' => 'Materias Legales', 'singular' => 'Materia Legal'],
        'origenes' => ['modelo' => Origen::class, 'titulo' => 'Orígenes', 'singular' => 'Origen'],
        'departamentos' => ['modelo' => Departamento::class, 'titulo' => 'Departamentos', 'singular' => 'Departamento'],
    ];

    public function index(string $catalogo): View
    {
        $config = $this->configPara($catalogo);

        return view('admin.catalogos.index', [
            'catalogo' => $catalogo,
            'config' => $config,
            'registros' => $config['modelo']::query()->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }

    public function create(string $catalogo): View
    {
        $config = $this->configPara($catalogo);

        return view('admin.catalogos.form', [
            'catalogo' => $catalogo,
            'config' => $config,
            'registro' => new ($config['modelo'])(),
        ]);
    }

    public function store(Request $request, string $catalogo): RedirectResponse
    {
        $config = $this->configPara($catalogo);
        $data = $this->validar($request, $config['modelo']);

        $config['modelo']::create($data);

        return redirect()->route('admin.catalogos.index', $catalogo)
            ->with('status', $config['singular'].' creado(a) correctamente.');
    }

    public function edit(string $catalogo, int $registro): View
    {
        $config = $this->configPara($catalogo);
        $model = $config['modelo']::findOrFail($registro);

        return view('admin.catalogos.form', [
            'catalogo' => $catalogo,
            'config' => $config,
            'registro' => $model,
        ]);
    }

    public function update(Request $request, string $catalogo, int $registro): RedirectResponse
    {
        $config = $this->configPara($catalogo);
        $model = $config['modelo']::findOrFail($registro);
        $data = $this->validar($request, $config['modelo'], $model->id);

        $model->update($data);

        return redirect()->route('admin.catalogos.index', $catalogo)
            ->with('status', $config['singular'].' actualizado(a) correctamente.');
    }

    public function destroy(string $catalogo, int $registro): RedirectResponse
    {
        $config = $this->configPara($catalogo);
        $config['modelo']::findOrFail($registro)->delete();

        return redirect()->route('admin.catalogos.index', $catalogo)
            ->with('status', $config['singular'].' eliminado(a) correctamente.');
    }

    protected function configPara(string $catalogo): array
    {
        abort_unless(isset($this->catalogos[$catalogo]), Response::HTTP_NOT_FOUND);

        return $this->catalogos[$catalogo];
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    protected function validar(Request $request, string $modelo, ?int $ignorarId = null): array
    {
        $tabla = (new $modelo())->getTable();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique($tabla, 'slug')->ignore($ignorarId),
            ],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['activo'] = $request->boolean('activo');
        $data['orden'] ??= 0;

        return $data;
    }
}
