<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { padding: 20px; }
        .order-summary { margin: 20px 0; }
        .order-header { font-weight: bold; margin: 15px 0; }
        .changes-list { margin-left: 20px; }
        .missing-products { margin-top: 20px; }
        .divider { border-top: 1px solid #ccc; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>{{ $description }} - Successful</h2>

        <div class="order-summary">
            <h3>Processed Orders Summary:</h3>
            <div class="divider"></div>

            @foreach($orderSummary as $summary)
                <div class="order-header">
                    Order ID: {{ $summary['id'] }} (Ref: {{ $summary['reference'] }})
                </div>
                
                @if(empty($summary['changes']))
                    <p>- No changes needed</p>
                @else
                    <p>Changes made:</p>
                    <div class="changes-list">
                        @foreach($summary['changes'] as $change)
                            <p>- {{ $change }}</p>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>

        @if(!empty($notFoundProducts))
            <div class="missing-products">
                <h3>Missing Products:</h3>
                <div class="divider"></div>
                @foreach($notFoundProducts as $product)
                    <p>{{ $product }}</p>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>