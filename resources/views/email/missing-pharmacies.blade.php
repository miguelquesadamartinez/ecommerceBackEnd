<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #0056b3;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>{{ $subject }}</h2>
        
        <p>{{ $textContent }}</p>
        
        <table>
            <thead>
                <tr>
                    <th>CIP</th>
                    <th>Référence de commande</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pharmacies as $pharmacy)
                <tr>
                    <td>{{ $pharmacy['cip_id'] }}</td>
                    <td>{{ $pharmacy['order_reference'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <p>Veuillez vérifier et créer ces pharmacies pour pouvoir traiter correctement les commandes.</p>
    </div>
    
    <div class="footer">
        Email généré le {{ date('d/m/Y à H:i:s') }}
    </div>
</body>
</html>
