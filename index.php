<?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $name = $_POST['name'];
          $email = $_POST['email_address'];
          $companyName = $_POST['companyName'];
          $phone = $_POST['phone'];
          $options = [];
          if (isset($_POST['option1'])) {
              $options[] = $_POST['option1'];
          }
          if (isset($_POST['option2'])) {
              $options[] = $_POST['option2'];
          }
          if (isset($_POST['option3'])) {
              $options[] = $_POST['option3'];
          }
          $optionsString = implode(', ', $options);
      
          // Get current date and time in the "Africa/Cairo" timezone
          $dateTime = new DateTime("now", new DateTimeZone('Africa/Cairo'));
          $currentDateTime = $dateTime->format('Y-m-d\TH:i:s');
          $endDateTime = $dateTime->add(new DateInterval('PT1H'))->format('Y-m-d\TH:i:s');
      
          $credentials = __DIR__ . '/credentials2.json';
          require __DIR__ . '/vendor/autoload.php';
      
          $client = new Google_Client();
          $client->setApplicationName('zoro');
          $client->setScopes(array(Google_Service_Calendar::CALENDAR));
          $client->setAuthConfig($credentials);
          $client->setAccessType('offline');
          $client->getAccessToken();
          $client->getRefreshToken(); 
      
          $service = new Google_Service_Calendar($client);
      
          // Generate a unique event ID using a hash
          $uniqueId = md5($name . $email . $currentDateTime);
      
          $event = new Google_Service_Calendar_Event(array(
              'id' => $uniqueId,
              'summary' => 'Contact Form Submission from ' . $name,
              'description' => "Email: $email\nCompany: $companyName\nPhone: $phone\nOptions: $optionsString",
              'start' => array(
                  'dateTime' => $currentDateTime,
                  'timeZone' => 'Africa/Cairo',
              ),
              'end' => array(
                  'dateTime' => $endDateTime,
                  'timeZone' => 'Africa/Cairo',
              ),
              'reminders' => array(
                  'useDefault' => FALSE,
                  'overrides' => array(
                      array('method' => 'email', 'minutes' => 24 * 60),
                      array('method' => 'popup', 'minutes' => 10),
                  ),
              ),
          ));
      
          // Insert the event
          try {
              $service->events->insert('zoroom637@gmail.com', $event);
              echo 'Event created: ' . $event->htmlLink;
          } catch (Google_Service_Exception $e) {
              // Handle duplicate event error
              if ($e->getCode() == 409) { // 409 Conflict is returned when the event already exists
                  echo 'Event already exists.';
              } else {
                  echo 'An error occurred: ' . $e->getMessage();
              }
          }
      }
  ?>      
