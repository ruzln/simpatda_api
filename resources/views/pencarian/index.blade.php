<!-- pencarian.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Data</title>
</head>
<body>
    <h1>Pencarian Data</h1>

    @isset($wajibPajakList)
        <form action="{{ route('pencarian.hasilPencarian') }}" method="post">
            @csrf
            <label for="tanggal_awal">Tanggal Awal:</label>
            <input type="date" name="tanggal_awal" id="tanggal_awal" required>

            <label for="tanggal_akhir">Tanggal Akhir:</label>
            <input type="date" name="tanggal_akhir" id="tanggal_akhir" required>

            <label for="nama">Nama Wajib Pajak:</label>
            <select name="nama" id="nama" required>
                @foreach ($wajibPajakList as $namaPemilik => $namaWajibPajak)
                    <option value="{{ $namaWajibPajak }}">{{ $namaWajibPajak }}</option>
                @endforeach
            </select>

            <button type="submit">Cari</button>
        </form>
    @endisset

    @if (!empty($result))

    <table border="1" style="border: aqua">
        <thead>
            <tr>
                <td>
                    @if (isset($result[0]['NAMA']))
                    <strong>Nama Perusahaan </strong>
                </td>
                <td colspan="6">: {{ $result[0]['NAMA'] }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>NPWPD </strong> 
                </td>
                    <td colspan="6">: {{ $result[0]['NPWPD'] }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Tanggal Ketetapan</th>
                <th>Tanggal Jatuh Tempo</th>
                <th>Nomor Ketetapan</th>
                <th>Masa Pajak</th>
                <th>Pokok</th>
                <th>Total Seluruh</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($result as $item)
                <tr>
                   
                    <td>{{ \Carbon\Carbon::parse($item['TGL_KETETAPAN'])->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item['TGLJATUHTEMPO'])->format('d/m/Y') }}</td>
                    <td>{{ $item['NO_KETETAPAN'] }}</td>
                    <td>{{ $item['MASA_PAJAK'] }}</td>
                    <td>{{ 'Rp ' . number_format($item['POKOK'], 2, ',', '.') }}</td>
                    <td>{{ 'Rp ' . number_format($item['JML_SELURUH'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button><a href="{{ route('pencarian.index') }}">Kembali ke Pencarian</a></button>
@else
    <p>Tidak ada hasil pencarian.</p>
@endif

</body>
</html>
