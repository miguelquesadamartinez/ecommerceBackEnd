<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Rapport de produits manquants' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
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
                    <th>Code CIP13</th>
                    <th>Référence de commande</th>
                    <th>Quantité</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product['cip13'] }}</td>
                    <td>{{ $product['order_reference'] }}</td>
                    <td>{{ $product['quantity'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <p>Veuillez vérifier et créer ces produits pour pouvoir traiter correctement les commandes.</p>
    </div>
    
    <div class="footer">
        Email généré le {{ date('d/m/Y à H:i:s') }}
    </div>
</body>
</html>