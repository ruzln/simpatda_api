<form method="POST" action="{{ route('pencarian.SelfAsessment') }}">
    @csrf
    <label for="tglmulai">Tanggal Mulai:</label>
    <input type="date" id="tglmulai" name="tglmulai" required><br><br>

    <label for="tglsampai">Tanggal Sampai:</label>
    <input type="date" id="tglsampai" name="tglsampai" required><br><br>

    <label for="in_jenispajak">Jenis Pajak:</label>
    <input type="text" id="in_jenispajak" name="in_jenispajak" required><br><br>

    <button type="submit">Submit</button>
</form>