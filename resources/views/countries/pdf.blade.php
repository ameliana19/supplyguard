<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Negara - SupplyGuard</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        p { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f6fa; font-weight: bold; }
        tr:nth-child(even) { background: #fafafa; }
        .footer { margin-top: 20px; font-size: 10px; color: #999; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer;">🖨️ Cetak / Simpan PDF</button>
        <a href="{{ route('countries.index') }}" style="margin-left: 10px;">← Kembali</a>
    </div>

    <h1>SupplyGuard — Daftar Negara</h1>
    <p>Diekspor pada {{ now()->format('d M Y H:i') }} | Total: {{ $countries->count() }} negara</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Negara</th>
                <th>Ibu Kota</th>
                <th>Wilayah</th>
                <th>Mata Uang</th>
                <th>Populasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($countries as $i => $country)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $country->name }}</td>
                <td>{{ $country->capital }}</td>
                <td>{{ $country->region }}</td>
                <td>{{ $country->currency }}</td>
                <td>{{ number_format($country->population) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">© SupplyGuard — Pengendalian Risiko Rantai Pasok Global</div>
</body>
</html>
