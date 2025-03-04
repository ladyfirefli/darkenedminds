<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Confirmation</title>

  <link rel="stylesheet" href="../css/tourneystyles.css">
  <style>


    /* Email Container */
    .email-container {
      background-color: rgba(255, 255, 255, 0.8);
      padding: 2rem;
    margin: 1rem auto;
    max-width: 900px;
    text-align: left;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    ul li {
    display: flex;
    align-items: left;
    justify-content: left;
    margin: 1rem 0;
}

  

    /* Button Styling */
    .btn {
      display: inline-block;
      padding: 12px 24px;
      font-size: 16px;
      background-color: #e63946;
      color: #ffffff;
      text-decoration: none;
      border-radius: 8px;
      transition: background-color 0.3s;
      margin-top: 1rem;
    }
    .btn:hover {
      background-color: #c21d2a;
    }

  </style>
</head>
<body>
   <!-- Navigation Bar -->
   <?php include 'navbar.php'; ?>

  <div class="email-container">
    <div class="header">
      <h1>Thanks for Registering!</h1>
    </div>
    <p>Hi there,</p>
    <p>We’ve received your registration for the upcoming <strong>Master and Padawan Apex Legends Tournament</strong> on November 3rd, and your spot is confirmed! Here are a few things to remember:</p>
    <ul>
      <li><strong>Match Details:</strong> Tournament starts at [6pm UTC]. Make sure to join the Darkened Minds Discord for updates and match announcements.</li>
      <li><strong>Scoring:</strong> Padawans get double points, so make those plays count!</li>
      <li><strong>Prizes:</strong> 1st place gets the cash pool, 2nd place gets a custom hoodie, and 3rd place wins exclusive stickers!</li>
      <li><strong>Registration Fee:</strong> Please complete your registration by paying the $2 fee via PayPal @DarkenedMinds. Click the button below:</li>
    </ul>
    <p><a href="https://www.paypal.com/paypalme/DarkenedMinds/2" class="btn">Pay $2 Registration Fee</a></p>
    <p>If you have any questions, feel free to reply to this email or message one of the admins on Discord.</p>
    <p>Good luck, and may the best team win!</p>
    <p>— The Darkened Minds Admin Team</p>

    <div class="footer">
      <p>&copy; 2024 Darkened Minds Tournament | All rights reserved</p>
    </div>
  </div>
</body>
</html>
