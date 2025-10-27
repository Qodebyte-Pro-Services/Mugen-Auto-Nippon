<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = filter_var(trim($_POST['from_name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['reply_to']), FILTER_SANITIZE_EMAIL);
    $phone = isset($_POST['phone']) ? filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING) : '';
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);
    
    // Validation checks
    $errors = [];
    
    // Name validation
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Name is required and must be at least 2 characters long.";
    }
    
    if (preg_match('/[0-9!@#$%^&*()_+=\[\]{};\':"\\|,<>\/?]/', $name)) {
        $errors[] = "Name contains invalid characters.";
    }
    
    // Email validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    
    // Check for disposable emails
    $disposableDomains = [
        'tempmail.com', 'guerrillamail.com', 'mailinator.com', '10minutemail.com',
        'yopmail.com', 'throwaway.com', 'fakeinbox.com', 'temp-mail.org'
    ];
    
    $emailDomain = strtolower(substr(strrchr($email, "@"), 1));
    foreach ($disposableDomains as $domain) {
        if (strpos($emailDomain, $domain) !== false) {
            $errors[] = "Disposable email addresses are not allowed.";
            break;
        }
    }
    
    // Message validation
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message is required and must be at least 10 characters long.";
    }
    
    // Check for spam patterns
    $spamWords = ['viagra', 'casino', 'lottery', 'click here', 'buy now', 'discount'];
    $content = strtolower($name . ' ' . $email . ' ' . $message);
    foreach ($spamWords as $word) {
        if (strpos($content, $word) !== false) {
            $errors[] = "Message contains suspicious content.";
            break;
        }
    }
    
    // Check for excessive links
    if (preg_match_all('/https?:\/\/[^\s]+/', $message) > 2) {
        $errors[] = "Too many links in message.";
    }
    
    // If no errors, send email
    if (empty($errors)) {
        $to = "contact@mugenauto.jp";  
        $subject = "New Contact Form Message from $name";
        $body = "You have received a new message from your website contact form.\n\n" .
                "Name: $name\n" .
                "Email: $email\n" .
                "Phone: $phone\n\n" .
                "Message:\n$message\n";
        
        $headers = "From: MUGEN AUTO NIPPON <contact@mugenauto.jp>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        if (mail($to, $subject, $body, $headers)) {
             echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    } else {
        echo "validation_error";
    }
}
?>