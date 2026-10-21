@extends('glosarium.layout')
@section('title', 'Glosarium Energi')

@section('content')
    <h1>Glosarium Istilah Energi</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p><a class="btn" href="{{ route('glosarium.create') }}">+ Tambah Istilah</a></p>

    <table>
        <thead>
            <tr>
                <th>No</th><th>Istilah</th><th>Definisi</th>
                <th>Kategori</th><th>Status</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $item->istilah }}</strong></td>
                    <td>{{ \Illuminate\Support\Str::limit($item->definisi, 80) }}</td>
                    <td>{{ ucfirst($item->kategori) }}</td>
                    <td>
                        @if ($item->status === 'terbit')
                            <span class="badge badge-terbit">Terbit</span>
                        @else
                            <span class="badge badge-draft">Draft</span>
                        @endif
                    </td>
                    <td>
                        <a class="btn btn-small" href="{{ route('glosarium.edit', $item) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('glosarium.destroy', $item) }}"
                              onsubmit="return confirm('Hapus istilah ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach

            @if ($items->isEmpty())
                <tr><td colspan="6">Belum ada istilah. Klik "Tambah Istilah" untuk memulai.</td></tr>
            @endif
        </tbody>
    </table>
@endsection