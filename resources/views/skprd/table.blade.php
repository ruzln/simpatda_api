<table class="table table-bordered table-sm">
    <thead class="thead-light">
        <tr>
            <th>No</th>
            <th>No SK</th>
            <th>Wajib Pajak</th>
            <th>Pajak</th>
            <th>Bayar</th>
            <th>Sisa</th>
            <th>Masa</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($data as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['no_sk'] }}</td>
            <td>
                <b>{{ $row['nama_wp'] }}</b><br>
                <small>{{ $row['npwpd'] }}</small>
            </td>
            <td class="text-right">{{ number_format($row['jml_pajak']) }}</td>
            <td class="text-right">{{ number_format($row['jml_bayar']) }}</td>
            <td class="text-right">{{ number_format($row['jml_sisa']) }}</td>
            <td>
                @if($row['masa'])
                    {{ $row['masa']['dari'] }}<br>
                    <small>s/d {{ $row['masa']['sampai'] }}</small>
                @else
                    -
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center text-muted">
                Data tidak ditemukan
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
