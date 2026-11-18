<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planned Trip Confirmation - Feel La Union</title>
</head>
<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f1f5f9; color: #0f172a; margin: 0; padding: 28px 14px;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; max-width: 520px; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden;">
        <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); padding: 28px 24px; text-align: center; color: #ffffff;">
                <div style="font-size: 12px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; opacity: 0.9;">Feel La Union</div>
                <h1 style="margin: 6px 0 0; font-size: 21px; font-weight: 800;">Your Planned Trip is Scheduled!</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 26px 24px;">
                <p style="margin: 0 0 12px; font-size: 15px;">Hi <strong>{{ $userName }}</strong>,</p>
                <p style="margin: 0 0 18px; color: #475569; font-size: 14px; line-height: 1.55;">
                    Your itinerary has been saved to your trip calendar. Here are the details for your upcoming adventure in La Union:
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; margin-bottom: 18px;">
                    <div style="font-size: 16px; font-weight: 800; color: #1e3a8a; margin-bottom: 8px;">{{ $tripTitle }}</div>
                    <div style="font-size: 13px; color: #334155; margin-bottom: 4px;"><strong>📅 Trip Date:</strong> {{ $tripDate }}</div>
                    <div style="font-size: 13px; color: #334155; margin-bottom: 4px;"><strong>👥 Travelers:</strong> {{ $totalPax }} {{ $totalPax === 1 ? 'Person (Solo)' : 'Pax' }}</div>
                    <div style="font-size: 13px; color: #334155;"><strong>💰 Estimated Cost:</strong> ₱{{ $totalCost }}</div>
                </div>

                @if(!empty($stops))
                    <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">📍 Planned Stops ({{ count($stops) }}):</div>
                    <ol style="margin: 0 0 18px; padding-left: 20px; color: #334155; font-size: 13.5px; line-height: 1.65;">
                        @foreach($stops as $stop)
                            <li>{{ $stop }}</li>
                        @endforeach
                    </ol>
                @endif

                <p style="margin: 0; color: #64748b; font-size: 12.5px; line-height: 1.5;">
                    Open the <strong>Intan Elyu</strong> app on your scheduled trip date and tap <strong>Start Trip</strong> under Saved Trips to navigate and check in at each stop!
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
