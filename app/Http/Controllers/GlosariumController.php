<?php

namespace App\Http\Controllers;

use App\Models\Glosarium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GlosariumController extends Controller
{
    private function rules(?Glosarium $glosarium = null): array
    {
        return [
            'istilah'  => [
                'required', 'string', 'min:2', 'max:100',
                Rule::unique('glosarium', 'istilah')->ignore($glosarium?->id),
            ],
            'definisi' => ['required', 'string', 'min:10', 'max:1000'],
            'kategori' => ['required', Rule::in(['surya', 'angin', 'air', 'biomassa', 'umum'])],
            'status'   => ['required', Rule::in(['draft', 'terbit'])],
        ];
    }

    private function messages(): array
    {
        return [
            'istilah.required'  => 'Istilah wajib diisi.',
            'istilah.min'       => 'Istilah minimal 2 karakter.',
            'istilah.unique'    => 'Istilah ini sudah ada di glosarium.',
            'definisi.required' => 'Definisi wajib diisi.',
            'definisi.min'      => 'Definisi minimal 10 karakter.',
            'kategori.in'       => 'Kategori tidak valid.',
            'status.in'         => 'Status tidak valid.',
        ];
    }

    public function index()
    {
        $items = Glosarium::orderBy('istilah')->get();

        return view('glosarium.index', compact('items'));
    }

    public function create()
    {
        return view('glosarium.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());
        Glosarium::create($data);

        return redirect()->route('glosarium.index')
            ->with('success', 'Istilah berhasil ditambahkan.');
    }

    public function edit(Glosarium $glosarium)
    {
        return view('glosarium.edit', compact('glosarium'));
    }

    public function update(Request $request, Glosarium $glosarium)
    {
        $data = $request->validate($this->rules($glosarium), $this->messages());
        $glosarium->update($data);

        return redirect()->route('glosarium.index')
            ->with('success', 'Istilah berhasil diperbarui.');
    }

    public function destroy(Glosarium $glosarium)
    {
        $glosarium->delete();

        return redirect()->route('glosarium.index')
            ->with('success', 'Istilah berhasil dihapus.');
    }
}