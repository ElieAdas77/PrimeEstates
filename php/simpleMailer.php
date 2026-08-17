<?php



function sendEmail($toEmail, $subject, $htmlBody) {
    $config = require __DIR__ . "/mailConfig.php";

    $host = $config["host"];
    $port = $config["port"];
    $username = $config["username"];
    $password = $config["password"];
    $fromEmail = $config["fromEmail"];
    $fromName = $config["fromName"];

    $smtp = fsockopen($host, $port, $errno, $errstr, 15);
    if (!$smtp) {
        return "Could not connect to mail server: $errstr";
    }

    stream_set_timeout($smtp, 15);

    $read = function () use ($smtp) {
        $data = "";
        while ($line = fgets($smtp, 515)) {
            $data .= $line;
            
            if (substr($line, 3, 1) === " ") break;
        }
        return $data;
    };

    $write = function ($command) use ($smtp) {
        fwrite($smtp, $command . "\r\n");
    };

    $expect = function ($expectedCode) use ($read, &$smtp) {
        $response = $read();
        $code = substr($response, 0, 3);
        if ($code !== (string) $expectedCode) {
            return "SMTP error: $response";
        }
        return true;
    };

    $read(); // initial server greeting

    $write("EHLO primeestates.local");
    if (($err = $expect(250)) !== true) { fclose($smtp); return $err; }

    $write("STARTTLS");
    if (($err = $expect(220)) !== true) { fclose($smtp); return $err; }

    if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($smtp);
        return "Could not start TLS encryption.";
    }

    
    $write("EHLO primeestates.local");
    if (($err = $expect(250)) !== true) { fclose($smtp); return $err; }

    $write("AUTH LOGIN");
    if (($err = $expect(334)) !== true) { fclose($smtp); return $err; }

    $write(base64_encode($username));
    if (($err = $expect(334)) !== true) { fclose($smtp); return $err; }

    $write(base64_encode($password));
    if (($err = $expect(235)) !== true) {
        fclose($smtp);
        return "SMTP authentication failed \u2014 check your Gmail address and App Password in mailConfig.php.";
    }

    $write("MAIL FROM:<$fromEmail>");
    if (($err = $expect(250)) !== true) { fclose($smtp); return $err; }

    $write("RCPT TO:<$toEmail>");
    if (($err = $expect(250)) !== true) { fclose($smtp); return $err; }

    $write("DATA");
    if (($err = $expect(354)) !== true) { fclose($smtp); return $err; }

    $headers = [
        "From: $fromName <$fromEmail>",
        "To: <$toEmail>",
        "Subject: $subject",
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
    ];

    
    $escapedBody = preg_replace("/^\./m", "..", $htmlBody);

    $write(implode("\r\n", $headers) . "\r\n\r\n" . $escapedBody . "\r\n.");
    if (($err = $expect(250)) !== true) { fclose($smtp); return $err; }

    $write("QUIT");
    fclose($smtp);

    return true;
}
