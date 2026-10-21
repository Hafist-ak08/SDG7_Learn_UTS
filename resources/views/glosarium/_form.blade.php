@if ($errors->any())
    <div class="alert alert-error">
        <strong>Periksa kembali isian Anda:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="field">
    <label for="istilah">Istilah</label>
    <input id="istilah" type="text" name="istilah" value="{{ old('istilah', $glosarium->istilah ?? '') }}">
    @error('istilah') <div class="err">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="definisi">Definisi</label>
    <textarea id="definisi" name="definisi" rows="5">{{ old('definisi', $glosarium->definisi ?? '') }}</textarea>
    @error('definisi') <div class="err">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="kategori">Kategori</label>
    <select id="kategori" name="kategori">
        @foreach (['umum' => 'Umum', 'surya' => 'Surya', 'angin' => 'Angin', 'air' => 'Air', 'biomassa' => 'Biomassa'] as $val => $label)
            <option value="{{ $val }}" @selected(old('kategori', $glosarium->kategori ?? 'umum') === $val)>{{ $label }}</option>
        @endforeach
    </select>
    @error('kategori') <div class="err">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="status">Status</label>
    <select id="status" name="status">
        @foreach (['draft' => 'Draft', 'terbit' => 'Terbit'] as $val => $label)
            <option value="{{ $val }}" @selected(old('status', $glosarium->status ?? 'draft') === $val)>{{ $label }}</option>
        @endforeach
    </select>
    @error('status') <div class="err">{{ $message }}</div> @enderror
</div>

<button class="btn" type="submit">Simpan</button>
<a href="{{ route('glosarium.index') }}">Batal</a>