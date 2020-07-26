<?php
  /*
          File: web_service_trigger_relay.php
       Created: 07/23/2020
       Updated: 07/26/2020
    Programmer: Cuates
    Updated By: Cuates
       Purpose: Trigger web service Relay
  */

  // Include error check class
  include ("checkerrorclass.php");

  // Create an object of error check class
  $checkerrorcl = new checkerrorclass();

  // Set variables
  $developerNotify = 'cuates@email.com'; // Production email(s)
  // $developerNotify = 'cuates@email.com'; // Development email(s)
  $endUserEmailNotify = 'cuates@email.com'; // Production email(s)
  // $endUserEmailNotify = 'cuates@email.com'; // Development email(s)
  $externalEndUserEmailNotify = ''; // Production email(s)
  // $externalEndUserEmailNotify = 'cuates@email.com'; // Development email(s)
  $scriptName = 'Web Service Trigger Relay'; // Production
  // $scriptName = 'TEST Web Service Trigger Relay TEST'; // Development
  $fromEmailServer = 'Email Server';
  $fromEmailNotifier = 'email@email.com';

  // Retrieve any other issues not retrieved by the set_error_handler try/catch
  // Parameters are function name, $email_to, $email_subject, $from_mail, $from_name, $replyto, $email_cc and $email_bcc
  register_shutdown_function(array($checkerrorcl,'shutdown_notify'), $developerNotify, $scriptName . ' Error', $fromEmailNotifier, $fromEmailServer, $fromEmailNotifier);

  // Function to catch exception errors
  set_error_handler(function ($errno, $errstr, $errfile, $errline)
  {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
  });

  // Attempt script logic
  try
  {
    // Declare download directory;
    define ('TEMPDOC', '/var/www/html/Temp_Directory/');

    // Include class
    include ("web_service_class.php");

    // Create an object of class
    $web_service_cl = new web_service_class();

    // Initialize parameters
    $eqlArray = array();
    $payload = array();
    $param_01 = 0;
    $param_02 = "";
    $param_03 = "";
    $notFound = "/\bHTTP Status 404\b/i";
    $authenticationFailed = "/\bHTTP Status 401\b/i";
    $methodNotPermitted = "/\bHTTP Status 405\b/i";
    // $errorPrefixFilename = "web_service_trigger_relay_issue_"; // Production
    $errorPrefixFilename = "web_service_trigger_relay_dev_issue_"; // Development
    $errormessagearray = array();
    $lineBreakString = array("\r\n", "\r", "\n");

    // get the HTTP method, path and body of the request
    $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? trim(strtoupper($_SERVER['REQUEST_METHOD'])) : trim("");
    $httpAccept = isset($_SERVER['HTTP_ACCEPT']) ? trim(strtolower($_SERVER['HTTP_ACCEPT'])) : trim("");
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? trim(strtolower($_SERVER['CONTENT_TYPE'])) : trim("");
    $httpAcceptCharSet = isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? trim(strtoupper($_SERVER['HTTP_ACCEPT_CHARSET'])) : trim("");
    $validRequestMethod = "GET";

    // Pull query string, if any, via which the page was accessed after the '?' mark
    $url = isset($_SERVER['QUERY_STRING']) ? trim($_SERVER['QUERY_STRING']) : trim("");

    // Check if URL is empty string
    if(trim($url) !== "")
    {
      // Explode query string by Ampersand '&'
      $ampArray = explode('&', $url);

      // Loop through array of Ampersand
      foreach($ampArray as $vals)
      {
        // Empty temporary array
        $tempArray = array();

        // Explode array by Equal '='
        $tempArray = explode('=', $vals);

        // Explode array by Equal '='
        $eqlArray[strtolower(reset($tempArray))] = trim(end($tempArray));
      }

      // Set variable for payload
      $payload = json_encode($eqlArray);

      // Check if Request Method is provided
      $webserviceResponse = $web_service_cl->validateJSONWebServiceCall($requestMethod, $validRequestMethod, $httpAccept, $contentType, $httpAcceptCharSet, $payload);

      // Check if return is an array
      if (is_array($webserviceResponse))
      {
        // Check if not server error
        if(!isset($webserviceResponse['SError']) && !array_key_exists('SError', $webserviceResponse))
        {
          // Set values within parameter
          $server_response = $webserviceResponse["SRes"];
          $server_message = $webserviceResponse["SMesg"];

          // Check if issues exist in the return json string
          if ($server_response === "Success")
          {
            // Set variables
            $value01Value = isset($webserviceResponse['Payload']['value01']) ? trim($webserviceResponse['Payload']['value01']) : trim("");
            $value02Value = isset($webserviceResponse['Payload']['value02']) ? trim($webserviceResponse['Payload']['value02']) : trim("no");

            // Check if variable is set
            if ($value01Value !== "")
            {
              // Extract new router data
              $extractDataResponse = $web_service_cl->triggerWebService($value01Value);

              // Explode response
              $extractDataResponseReturn = explode('~', $extractDataResponse);

              // Set variables
              $extractDataResponseResp = reset($extractDataResponseReturn);
              $extractDataResponseMesg = next($extractDataResponseReturn);

              // Check if no server error
              if($extractDataResponseResp === "Success")
              {
                // Change the return string to a json object array
                $extractDataResponseMesgReturn = json_decode($extractDataResponseMesg, true);

                // Check if JSON was decoded properly
                if (json_last_error() === JSON_ERROR_NONE)
                {
                  // Check if status code is set
                  if(isset($extractDataResponseMesgReturn["param_01"]) && isset($extractDataResponseMesgReturn["param_02"]))
                  {
                    // Set values from the CURL call
                    $param_01Value = $extractDataResponseMesgReturn["param_01"];
                    $param_02Value = $extractDataResponseMesgReturn["param_02"];

                    // Check if status code is equal to 0
                    if(trim($param_01Value) === 0)
                    {
                      // Set values
                      $param_01 = $param_01Value;
                      $param_02 = 'SUCCESS';
                    }
                    else if (trim($param_01Value) === -1 && strtolower($value02Value) === "yes" && preg_match("/Internal Server Error/i", $param_02Value))
                    {
                      // Set values
                      $param_01 = 5;
                      $param_02 = 'Programmer value02 2020-01-01';
                    }
                    else if(preg_match("/interpret = \[ACTIVE]/", $param_02Value))
                    {
                      // Set values
                      $param_01 = 1;
                      $param_02 = $param_02Value;
                      $param_03 = $param_01Value;
                    }
                    else if(preg_match("/interpret \[5]/", $param_02Value))
                    {
                      // Set values
                      $param_01 = 2;
                      $param_02 = $param_02Value;
                    }
                    else
                    {
                      // Set values
                      $param_01 = $param_01Value;
                      $param_02 = $param_02Value;
                    }
                  }
                  else if(isset($extractDataResponseMesgReturn["param_01"]) && isset($extractDataResponseMesgReturn["param_02"]))
                  {
                    // Set values
                    $param_01 = $extractDataResponseMesgReturn["param_01"];
                    $param_02 = $extractDataResponseMesgReturn["param_02"];
                  }
                  else
                  {
                    // Set variables
                    $param_02 = "Trigger JSON Response Missing Parameters Issue";

                    // Append error message
                    array_push($errormessagearray, array('Trigger JSON Response Missing Parameters Issue', $value01Value, $value02Value, 'Error', str_replace($lineBreakString, '', $extractDataResponseMesg)));
                  }
                }
                else if (!preg_match($notFound, $extractDataResponseMesg))
                {
                  // Check if response is HTTP Status 404 Not Found

                  // Set variables
                  $param_02 = "API not found Issue";
                }
                else if (!preg_match($authenticationFailed, $extractDataResponseMesg))
                {
                  // Check if response is HTTP Status 401 Authentication Failed

                  // Set variables
                  $param_02 = "Authentication Failed";
                }
                else if (!preg_match($methodNotPermitted, $extractDataResponseMesg))
                {
                  // Check if response is HTTP Status 405 Method not permitted POST or GET

                  // Set variables
                  $param_02 = "GET or POST method not Permitted for API call";
                }
                else
                {
                  // Set variables
                  $param_02 = "Trigger JSON Decode Issue";

                  // Append error message
                  array_push($errormessagearray, array('Trigger JSON Decode Issue', $value01Value, $value02Value, 'Error', str_replace($lineBreakString, '', $extractDataResponseMesg)));
                }
              }
              else
              {
                // Set variables
                $param_02 = $extractDataResponseMesg;

                // Set array with error records for processing
                array_push($errormessagearray, array('Trigger Web Service CURL Call', $value01Value, $value02Value, 'Error', str_replace($lineBreakString, '', $param_02)));
              }
            }
            else
            {
              // Set message
              $param_02 = "Process halted, parameters and/or values not properly provided";
            }
          }
          else
          {
            // Set message
            $param_02 = $server_message;

            // Set array with error records for processing
            array_push($errormessagearray, array('Validate Payload JSON Web Service Call Server-side returned an issue try again', '', '', 'Error', $param_02));
          }
        }
        else
        {
          // Set message
          $param_02 = reset($webserviceResponse);

          // Set array with error records for processing
          array_push($errormessagearray, array('Validate JSON Web Service Call Server-side returned an issue try again', '', '', 'Error', $param_02));
        }
      }
      else
      {
        // Set message
        $param_02 = $webserviceResponse;

        // Append error message
        array_push($errormessagearray, array('Validate JSON Web Service Call Response Is Not An Array Issue', '', '', 'Error', $param_02));
      }
    }
    else
    {
      // Set message
      $param_02 = "URL query returned no parameters try again";
    }

    // Check if error message array is not empty
    if (count($errormessagearray) > 0)
    {
      // Set prefix file name and headers
      $errorFilename = $errorPrefixFilename . date("Y-m-d_H-i-s") . '.csv';
      $colHeaderArray = array(array('Process', 'Value 01', 'Value 02', 'Response', 'Message'));

      // Initialize variable
      $to = "";
      $to = $developerNotify;
      $to_cc = "";
      $to_bcc = "";
      $fromEmail = $fromEmailNotifier;
      $fromName = $fromEmailServer;
      $replyTo = $fromEmailNotifier;
      $subject = $scriptName . " Error";

      // Set the email headers
      $headers = "From: " . $fromName . " <" . $fromEmail . ">" . "\r\n";
      // $headers .= "CC: " . $to_cc . "\r\n";
      // $headers .= "BCC: " . $to_bcc . "\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
      // $headers .= "X-Priority: 3\r\n";

      // Mail priority levels
      // "X-Priority" (values: 1, 3, or 5 from highest[1], normal[3], lowest[5])
      // Set priority and importance levels
      $xPriority = "";

      // Set the email body message
      $message = "<!DOCtype html>
      <html>
        <head>
          <title>"
            . $scriptName .
            " Error
          </title>
          <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
          <!-- Include next line to use the latest version of IE -->
          <meta http-equiv=\"X-UA-Compatible\" content=\"IE=Edge\" />
        </head>
        <body>
          <div style=\"text-align: center;\">
            <h2>"
              . $scriptName .
              " Error
            </h2>
          </div>
          <div style=\"text-align: center;\">
            There was an issue with " . $scriptName . " Error process.
            <br />
            <br />
            Do not reply, your intended recipient will not receive the message.
          </div>
        </body>
      </html>";

      // Call notify developer function
      $web_service_cl->notifyDeveloper(TEMPDOC, $errorFilename, $colHeaderArray, $errormessagearray, $to, $to_cc, $to_bcc, $fromEmail, $fromName, $replyTo, $subject, $headers, $message, $xPriority); // Only utilized for testing purposes
    }

    // Encode and send JSON back to Client-Side
    echo json_encode(array('param_01' => $param_01, 'param_02' => $param_02, 'param_03' => $param_03));
  }
  catch(Exception $e)
  {
    // Call to the function
    $checkerrorcl->caught_error_notify($e, $developerNotify, $scriptName . ' Error', $fromEmailNotifier, $fromEmailServer, $fromEmailNotifier);

    // Encode and send JSON back to Client-Side
    echo json_encode(array('param_01' => '0', 'param_02' => 'There was an issue with trigger relay', 'param_03' => ''));
  }
?>