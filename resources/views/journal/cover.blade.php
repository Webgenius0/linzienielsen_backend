<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $journal->title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
        }
        .journal-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .journal-page {
            page-break-after: always;
        }
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

    <div class="journal-title">{{ $journal->title }}</div>

</body>
</html>
