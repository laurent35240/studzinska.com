<?php
require 'vendor/autoload.php';
use Mailgun\Mailgun;
$mailgun = new Mailgun('key-9df464d68709f1de1aba29cac301bbba');

    if (!function_exists('http_response_code'))
    {
        function http_response_code($newcode = NULL)
        {
            static $code = 200;
            if($newcode !== NULL)
            {
                header('X-PHP-Response-Code: '.$newcode, true, $newcode);
                if(!headers_sent())
                    $code = $newcode;
            }
            return $code;
        }
    }

    // My modifications to mailer script from:
    // http://blog.teamtreehouse.com/create-ajax-contact-form
    // Added input sanitizing to prevent injection

    // Only process POST reqeusts.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the form fields and remove whitespace.
        $name = strip_tags(trim($_POST["name"]));
				$name = str_replace(array("\r","\n"),array(" "," "),$name);
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $message = trim($_POST["message"]);

        // Check that data was sent to the mailer.
        if ( empty($name) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set a 400 (bad request) response code and exit.
            http_response_code(400);
            echo "Oops! There was a problem with your submission. Please complete the form and try again.";
            exit;
        }

        // Set the recipient email address.
        // FIXME: Update this to your desired email address.
        $recipient = "studzinna@gmail.com";

        // Set the email subject.
        $subject = "New contact from $name";

        // Build the email content.
        $email_content = "Name: $name\n";
        $email_content .= "Email: $email\n\n";
        $email_content .= "Message:\n$message\n";

        try {
            // Send the email.
            $domain = 'mg.studzinska.com';
            $mailgun->sendMessage($domain, array('from'    => $email,
                'to'      => $recipient,
                'subject' => $subject,
                'text'    => $email_content));
            // Set a 200 (okay) response code.
            http_response_code(200);
            echo "Thank You! Your message has been sent.";
        } catch (\Exception $e) {
            // Set a 500 (internal server error) response code.
            http_response_code(500);
            echo "Oops! Something went wrong and we couldn't send your message. " . $e->getMessage();
        }
    } else {
        // Not a POST request, set a 403 (forbidden) response code.
        http_response_code(403);
        echo "There was a problem with your submission, please try again.";
    }

?>