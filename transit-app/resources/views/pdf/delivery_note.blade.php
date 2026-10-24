<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon de Livraison - #{{ str_pad($route->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .header table { width: 100%; }
        .header td.logo { width: 50%; font-size: 24px; font-weight: bold; color: #4f46e5; }
        .header td.info { width: 50%; text-align: right; color: #666; font-size: 12px; }
        h1 { color: #1e293b; font-size: 24px; margin-bottom: 5px; }
        .badge { background-color: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; color: #475569; text-transform: uppercase; }
        .grid-table { width: 100%; border-collapse: collapse; }
        .grid-table td { padding: 8px; vertical-align: top; }
        .label { font-size: 12px; color: #64748b; font-weight: bold; text-transform: uppercase; }
        .value { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; height: 50px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .signatures { width: 100%; margin-top: 60px; }
        .signatures td { width: 33%; text-align: center; }
        .sig-box { min-height: 80px; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    STN RAGHD TMS
                    <div style="font-size: 12px; color: #64748b; font-weight: normal; margin-top: 5px;">Transportation Management System</div>
                </td>
                <td class="info">
                    <strong>Date de Génération:</strong> {{ now()->format('d/m/Y H:i') }}<br>
                    <strong>ID Document:</strong> BL-{{ date('Y') }}-{{ str_pad($route->id, 5, '0', STR_PAD_LEFT) }}<br>
                    123 Avenue Hasan II, Casablanca, Maroc
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px; text-align: center;">
        <h1>BON DE LIVRAISON (DELIVERY NOTE)</h1>
        <span class="badge">{{ strtoupper($route->status) }}</span>
    </div>

    <div class="section">
        <div class="section-title">Détails de la Mission</div>
        <table class="grid-table">
            <tr>
                <td style="width: 50%;">
                    <div class="label">Origine</div>
                    <div class="value">{{ $route->start_location }}</div>
                </td>
                <td style="width: 50%;">
                    <div class="label">Destination</div>
                    <div class="value">{{ $route->end_location }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Distance Totale</div>
                    <div class="value">{{ $route->total_distance }} KM</div>
                </td>
                <td>
                    <div class="label">Arrivée Effective</div>
                    <div class="value">{{ $route->actual_arrival ? \Carbon\Carbon::parse($route->actual_arrival)->format('d/m/Y H:i') : 'Non renseigné' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Type de Cargaison</div>
                    <div class="value">{{ $route->cargo_type ?: 'Marchandise Générale' }}</div>
                </td>
                <td>
                    <div class="label">Météo / Trafic</div>
                    <div class="value">{{ $route->weather }} / {{ $route->traffic_status }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Acteurs du Transport</div>
        <table class="grid-table">
            <tr>
                <td style="width: 50%;">
                    <div class="label">Chauffeur Assigné</div>
                    <div class="value">{{ $route->driver ? $route->driver->first_name . ' ' . $route->driver->last_name : 'N/A' }}</div>
                    <div class="label" style="margin-top: 10px;">Téléphone / Permis</div>
                    <div class="value" style="font-weight: normal;">{{ $route->driver ? $route->driver->phone . ' / ' . $route->driver->license_number : 'N/A' }}</div>
                </td>
                <td style="width: 50%;">
                    <div class="label">Véhicule Utilisé</div>
                    <div class="value">{{ $route->transport ? $route->transport->name : 'N/A' }}</div>
                    <div class="label" style="margin-top: 10px;">Immatriculation</div>
                    <div class="value" style="font-weight: normal;">{{ $route->transport ? $route->transport->plate_number : 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="label">Signature du Chauffeur</div>
                <div class="sig-box"></div>
            </td>
            <td>
                <div class="label">Signature Logistique (RAGHD TMS)</div>
                <div class="sig-box"></div>
            </td>
            <td>
                <div class="label">Signature du Client / Destinataire</div>
                <div class="sig-box"></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Document généré automatiquement par STN RAGHD TMS - Le système intelligent de logistique marocaine.
    </div>

</body>
</html>
