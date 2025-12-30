<?php
include 'connect.php';
include 'auth.php';
include 'mailer.php'; // Include PHPMailer helper

$page_title = "Contact Us - WheyStore";
include 'header.php';

$msg = "";

if (isset($_POST['send_message'])) {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $subject = mysqli_real_escape_string($link, $_POST['subject']);
    $message = mysqli_real_escape_string($link, $_POST['message']);

    // Gửi email tới support (email cá nhân)
    $contact_sent = sendContactEmail($name, $email, $subject, $message);
    
    // Gửi email xác nhận tới khách
    $confirmation_sent = sendContactConfirmationEmail($email, $name);

    if ($contact_sent && $confirmation_sent) {
        $msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>Thank you $name!</strong> Your message has been sent successfully. We will respond shortly. A confirmation email has been sent to your inbox.
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
    } elseif ($contact_sent) {
        $msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>Thank you $name!</strong> Your message has been sent successfully. We will respond shortly.
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
    } else {
        $msg = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong>Notice:</strong> Your message was processed but there was an issue sending email confirmations. We will still contact you.
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
    }
}
?>

<style>
    /* CSS Override cho trang Contact */
    
    /* 1. Wrapper chính */
    .contact-wrapper {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-top: 50px;
        margin-bottom: 80px;
    }

    /* 2. Cột Trái (Thông tin) */
    .contact-info-side {
        background-color: #004085;
        color: rgba(255, 255, 255, 0.9);
        /* Dùng !important để ép buộc khoảng cách lề */
        padding: 60px 50px !important; 
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .contact-info-side h3 {
        color: #fff;
        font-weight: 800;
        font-size: 2.2rem;
        margin-bottom: 25px;
        letter-spacing: -0.5px;
    }

    .contact-description {
        font-size: 1.05rem;
        line-height: 1.6;
        margin-bottom: 40px;
        color: rgba(255,255,255,0.75);
    }

    .contact-info-item {
        margin-bottom: 30px;
    }

    .contact-info-item h6 {
        color: #ffc107;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        margin-bottom: 8px;
        letter-spacing: 1px;
    }

    .contact-info-item p {
        font-size: 1.15rem;
        color: #fff;
        margin: 0;
        font-weight: 500;
    }

    /* 3. Cột Phải (Form) */
    .contact-form-side {
        background-color: #ffffff;
        /* Dùng !important để ép buộc khoảng cách lề */
        padding: 60px 50px !important;
    }

    .contact-form-side h3 {
        color: #333;
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 35px;
        letter-spacing: -0.5px;
        padding-left: 5px; 
    }

    /* 4. Form Controls */
    .contact-form-side form {
        width: 100%; 
        padding: 5px;
    }

    .form-control {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px 20px;
        font-size: 1rem;
        height: auto;
        margin-bottom: 5px;
    }

    .form-control:focus {
        background-color: #fff;
        border-color: #004085;
        box-shadow: 0 0 0 4px rgba(0, 64, 133, 0.1);
    }

    /* 5. Nút Gửi */
    .btn-submit {
        background-color: #004085;
        color: white;
        padding: 15px 0; /* Padding trên dưới */
        width: 100%; /* Full chiều rộng để cân đối */
        font-weight: 700;
        border-radius: 6px; /* Bo góc nhẹ giống input */
        border: none;
        font-size: 1.1rem;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 15px;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-submit:hover {
        background-color: #003366;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 64, 133, 0.2);
    }

    /* Responsive cho Mobile */
    @media (max-width: 991px) {
        .contact-info-side, .contact-form-side {
            padding: 40px 20px !important;
        }
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12"> <!-- Dùng col-xl-10 để form gọn lại ở giữa màn hình lớn -->
            
            <div class="contact-wrapper">
                <div class="row no-gutters"> 
                    
                    <!-- CỘT TRÁI (INFO) -->
                    <div class="col-lg-5 contact-info-side">
                        <h3>Get in touch</h3>
                        <p class="contact-description">
                            Have a question about our authentic supplements or need advice on your fitness journey? We'd love to hear from you.
                        </p>

                        <div class="contact-info-item">
                            <h6>ADDRESS</h6>
                            <p>1 Phan Tay Nhac Street, Hanoi</p>
                        </div>

                        <div class="contact-info-item">
                            <h6>PHONE</h6>
                            <p>+84 987 654 321</p>
                        </div>

                        <div class="contact-info-item">
                            <h6>EMAIL</h6>
                            <p>support@wheystore.vn</p>
                        </div>
                    </div>

                    <!-- CỘT PHẢI (FORM) -->
                    <div class="col-lg-7 contact-form-side">
                        <h3>Send us a message</h3>
                        <?php echo $msg; ?>
                        
                        <form method="post">
                            <div class="form-row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                            </div>

                            <div class="form-group mb-4">
                                <textarea name="message" class="form-control" rows="5" placeholder="Message" required></textarea>
                            </div>

                            <button type="submit" name="send_message" class="btn btn-submit">Send Message</button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>