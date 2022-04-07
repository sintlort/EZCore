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
@foreach($pembelian2->PDetailPembelian as $index => $detail)
    <br>
    <br>
    <img
        src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(160)->generate($detail->kode_tiket)) !!} ">
    @if(array_key_last($detail) != $index)
        <div class="page_break"></div>
    @endif
@endforeach
</body>
</html>
