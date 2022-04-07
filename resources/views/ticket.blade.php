<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>invoice</title>
    <style>
        .page_break {
            page-break-before: always;
        }
    </style>
</head>

<body>
@foreach($pembelian2->PDetailPembelian as $detail)
    <br>
    <br>
    <img
        src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(160)->generate($detail->kode_tiket)) !!} ">
    <div class="page_break"></div>
@endforeach
</body>
</html>
