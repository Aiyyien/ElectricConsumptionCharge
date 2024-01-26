<?php
function calculateElectricityCharges($voltage, $current, $hoursUsed) {
    $powerInKW = $voltage * $current / 1000; // Power in kW
    $energyInKWh = $powerInKW * $hoursUsed; // Energy in kWh

    // Define the tariff rates in sen/kWh
    $tariffRates = [
        200 => 21.80,
        300 => 33.40,
        600 => 51.60,
        900 => 54.60,
        'rest' => 57.10 // Rate for above 900 kWh
    ];

    $totalCharge = 0;
    $remainingEnergy = $energyInKWh;

    // Iterate over each tariff rate
    foreach ($tariffRates as $threshold => $rate) {
        if ($threshold === 'rest') {
            $totalCharge += $remainingEnergy * $rate; // Remaining energy charged at the highest rate
        } else {
            $energyAtThisRate = min($remainingEnergy, $threshold);
            $totalCharge += $energyAtThisRate * $rate;
            $remainingEnergy -= $energyAtThisRate;
        }
        if ($remainingEnergy <= 0) break; // Exit loop if all energy charged
    }

    $totalCharge /= 100; // Convert sen to RM
    $minimumCharge = 3.00; // Minimum charge in RM
    if ($totalCharge < $minimumCharge) {
        $totalCharge = $minimumCharge;
    }

    return [
        'power' => $powerInKW,
        'energy' => $energyInKWh,
        'totalCharge' => $totalCharge
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $voltage = $_POST['voltage'];
    $current = $_POST['current'];
    $hours = $_POST['hours'];

    $result = calculateElectricityCharges($voltage, $current, $hours);
    $hourlyRate = 0;
    if ($hours > 0) {
        $hourlyRate = $result['totalCharge'] / $hours;
    }

    // Calculate daily rate assuming a day is 24 hours
    $dailyRate = $hourlyRate * 24;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Electricity Charge Calculator</title>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
  <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />
  <link type="text/css" rel="stylesheet" href="css/style.css" />

   <style>
        .form-header 
        {
            text-align: center;
            margin-bottom: 20px;
            font-size: 50px; 
            font-weight: bold;
            color: #FFD700;
            text-shadow: 2px 2px 4px #000000; 
            font-family: 'Montserrat', sans-serif;
            padding: 20px 10px;
            background-color: #1A1A1A; 
            border-bottom: 3px solid #FFD700;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        }

        .results-display 
        {
            color: #FFFFFF;
            width: 300px;
            height: 350px;
            padding: 30px;
            margin: 60px auto; 
            margin-top: 0px; 
            background-color: black;
            border-radius: 10px;
        }
    </style>
</head>

<body>
  <div id="booking" class="section">
    <div class="section-center">
      <div class="container">
        <div class="row">
            <div class="form-header">
              <h1>ELECTRICITY CONSUMPTION CHARGE</h1>
            </div>
          <div class="col-md-6">
          <div class="booking-form">
            
            <form method="post" action="">
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <span class="form-label">Voltage (V):</span>
                    <input class="form-control" type="number" placeholder="Enter Voltage" id="voltage" name="voltage" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <span class="form-label">Current (A):</span>
                    <input class="form-control" type="float" placeholder="Enter Current" id="current" name="current" required>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <span class="form-label">Current Rate (per kWh):</span>
                <input class="form-control" type="float" placeholder="Enter Current Rate" id="rate" name="rate" required>
              </div>
              <div class="form-group">
    <span class="form-label">Hours Used:</span>
    <input class="form-control" type="number" placeholder="Enter Hours" id="hours" name="hours" required>
</div>
              <div class="form-btn">
                <button type="submit" class="submit-btn">Calculate</button>
              </div>
            </form>
          </div>
        </div>
            
            
<div class="col-md-6">
    <?php if (isset($result)): ?>
        <div class="results-display mt-3">
            <h3>RESULTS</h3>
            <table class="table table-bordered">
                <tr>
                    <th>Parameter</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Power</td>
                    <td><?php echo number_format($result['power'], 2); ?> kW</td>
                </tr>
                <tr>
                    <td>Energy</td>
                    <td><?php echo number_format($result['energy'], 2); ?> kWh</td>
                </tr>
                <tr>
                    <td>Rate per Hour</td>
                    <td>RM<?php echo number_format($hourlyRate, 2); ?></td>
                </tr>
                <tr>
                    <td>Rate per Day</td>
                    <td>RM<?php echo number_format($dailyRate, 2); ?></td>
                </tr>
                <tr>
                    <td>Total Charge</td>
                    <td>RM<?php echo number_format($result['totalCharge'], 2); ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>
</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>