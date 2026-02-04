<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0070C0;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Rapport des commandes bloquées</h2>
        </div>
        <div class="content">
            <p>Bonjour,</p>

            <p>Veuillez trouver ci-joint le rapport des commandes bloquées/expédiées généré le {{ $date }}.</p>

            <p><strong>Nombre de commandes bloquées : {{ $ordersCount }}</strong></p>
            <p><strong>Nombre de commandes expédiées : {{ $ordersSentCount }}</strong></p>

            <p>Ce rapport présente la liste des commandes qui ont été bloquées pour diverses raisons et nécessitent une attention particulière.</p>

            <p>Cordialement,<br>
            L’équipe CMC pour NoName Ventes Directes
</p>
        </div>
        <div class="footer">
            <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{ date('Y') }} NoName</p>
        </div>
    </div>
</body>
</html>
