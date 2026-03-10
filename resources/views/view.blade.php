<!-- resources/views/pegawai.blade.php -->

@foreach ($data as $item)
    <h2>{{ $item['jenis_pajak'] }}</h2>
    <table>
        <thead>
            <tr>
                <th>NPWPD</th>
                <th>Jenis Pajak</th>
                <th>Nomor</th>
                <th>Nama</th>
                <th>Jumlah Pajak</th>
                <th>Jumlah Seluruh</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($item['data'] as $row)
                <tr>
                    <td>{{ $row->NPWPD }}</td>
                    <td>{{ $row->JENISPAJAK }}</td>
                    <td>{{ $row->NOMOR }}</td>
                    <td>{{ $row->NAMA }}</td>
                    <td>{{ $row->JML_PAJAK }}</td>
                    <td>{{ $row->JML_SELURUH }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
