<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>USSD Test Interface</title>

  <!-- ✅ Bootstrap 5 CDN Links -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background-color: #f8f9fa;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .ussd-card {
      width: 360px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .screen {
      background-color: #e9ecef;
      min-height: 120px;
      border-radius: 10px;
      padding: 15px;
      font-family: monospace;
      white-space: pre-line;
      text-align: left;
    }
  </style>
</head>
<body>

  <div class="card ussd-card">
    <div class="card-body text-center">
      <h4 class="card-title mb-3">📱 USSD Test Simulator</h4>
      <input type="text" id="ussdCode" class="form-control mb-3" placeholder="Enter USSD code (e.g. *123#)">
      <button class="btn btn-primary w-100 mb-3" onclick="sendUSSD()">Dial</button>
      <div class="screen" id="screen">Response will appear here...</div>
    </div>
  </div>

  <script>
    function sendUSSD() {
      const ussdCode = document.getElementById('ussdCode').value.trim();
      const screen = document.getElementById('screen');

      if (!ussdCode) {
        screen.textContent = "⚠️ Please enter a USSD code.";
        return;
      }

      // Example simulated responses
      let response = "";
      switch (ussdCode) {
        case "*123#":
          response = "Welcome to FarmNet!\n1. Market Prices\n2. Weather Info\n3. Exit";
          break;
        case "*123*1#":
          response = "Today's Maize Price: MWK 450/kg";
          break;
        case "*123*2#":
          response = "Weather: Sunny, 28°C, 10% chance of rain.";
          break;
        case "*123*3#":
          response = "Thank you for using FarmNet!";
          break;
        default:
          response = "❌ Invalid USSD code. Try *123#";
      }

      screen.textContent = response;
    }
  </script>
</body>
</html>
