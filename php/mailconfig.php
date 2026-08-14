<?php



$smtpUsername = getenv("PRIMEESTATES_SMTP_USERNAME");
$smtpPassword = getenv("PRIMEESTATES_SMTP_PASSWORD");

if (!$smtpUsername || !$smtpPassword) {

    error_log("SMTP credentials not set. Set PRIMEESTATES_SMTP_USERNAME and PRIMEESTATES_SMTP_PASSWORD as environment variables.");
}

return [
    "host" => "smtp.gmail.com",
    "port" => 587,
    "username" => $smtpUsername,
    "password" => $smtpPassword,
    "fromEmail" => $smtpUsername,
    "fromName" => "PrimeEstates",
];

